<?php
/**
 * perhitungan.php — Engine NCF Proyek Sumur Migas
 *
 * Mendukung:
 *  - Produksi: beberapa tahun awal manual (tabel produksi_tahunan),
 *    sisanya dihitung otomatis dengan decline rate multi-fase
 *    (tabel penurunan_produksi).
 *  - Investasi: capital + non-capital, bisa multi-item, bisa di tahun berbeda.
 *    Depresiasi straight-line hanya dari total capital (investasi awal tahun ke-0).
 *  - OPEX: multi-segmen. Tiap segmen bisa punya titik mulai kenaikan sendiri.
 *  - Tax: dari field tax_rate di proyek_sumur.
 *
 * Fungsi publik utama: hitung_ncf($project, $pdo)
 *
 * @param array $project  Baris dari tabel proyek_sumur
 * @param PDO   $pdo      Koneksi database
 * @return array [
 *   'rows'    => array of per-tahun data,
 *   'summary' => aggregate totals,
 * ]
 */

/**
 * Ambil semua data pendukung proyek dari DB, lalu hitung NCF per tahun.
 */
function hitung_ncf(array $project, PDO $pdo): array
{
    $pid           = (int) $project['id'];
    $jangka_waktu  = (int) $project['jangka_waktu'];
    $harga_minyak  = (float) $project['harga_minyak'];
    $tax_rate      = (float) $project['tax_rate'] / 100;   // 0.51 dst

    // ── 1. Produksi manual ────────────────────────────────────────────────
    // Keyed by tahun_ke → volume
    $produksi_manual = [];
    $stmt = $pdo->prepare('
        SELECT tahun_ke, volume_produksi
        FROM produksi_tahunan
        WHERE proyek_id = ? AND is_manual = 1
        ORDER BY tahun_ke ASC
    ');
    $stmt->execute([$pid]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $produksi_manual[(int)$r['tahun_ke']] = (float)$r['volume_produksi'];
    }

    // ── 2. Penurunan produksi — ambil semua, urutkan mulai_tahun_ke ASC ──
    // Pada saat menghitung produksi tahun-T, kita pakai rule dengan
    // mulai_tahun_ke TERBESAR yang masih <= T.
    $decline_rules = [];
    $stmt2 = $pdo->prepare('
        SELECT mulai_tahun_ke, persen_penurunan
        FROM penurunan_produksi
        WHERE proyek_id = ?
        ORDER BY mulai_tahun_ke ASC
    ');
    $stmt2->execute([$pid]);
    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $decline_rules[(int)$r['mulai_tahun_ke']] = (float)$r['persen_penurunan'];
    }

    // ── 3. Investasi ──────────────────────────────────────────────────────
    $stmt3 = $pdo->prepare('
        SELECT tipe, jumlah, tahun_ke
        FROM investasi
        WHERE proyek_id = ?
    ');
    $stmt3->execute([$pid]);
    $investasi_rows = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // Pisahkan: modal awal (tahun_ke=0) vs reinvestasi (tahun_ke>0)
    $total_capital_awal     = 0.0;
    $total_noncapital_awal  = 0.0;
    $reinvestasi_per_tahun  = [];   // [tahun_ke => jumlah total reinvestasi]

    foreach ($investasi_rows as $inv) {
        $tahun = (int) $inv['tahun_ke'];
        $jumlah = (float) $inv['jumlah'];

        if ($tahun === 0) {
            if ($inv['tipe'] === 'capital') {
                $total_capital_awal += $jumlah;
            } else {
                $total_noncapital_awal += $jumlah;
            }
        } else {
            // Reinvestasi di tengah proyek — masuk ke NCF tahun tersebut
            $reinvestasi_per_tahun[$tahun] = ($reinvestasi_per_tahun[$tahun] ?? 0) + $jumlah;
        }
    }

    $total_investasi_awal = $total_capital_awal + $total_noncapital_awal;
    // Straight-line: hanya berdasarkan CAPITAL (noncapital tidak disusutkan)
    $depresiasi_per_tahun = $jangka_waktu > 0
        ? $total_capital_awal / $jangka_waktu
        : 0.0;

    // ── 4. OPEX config ────────────────────────────────────────────────────
    // Ambil semua segmen, urut berdasarkan kolom 'urutan'
    $stmt4 = $pdo->prepare('
        SELECT opex_base, berlaku_sampai_tahun, kenaikan_mulai_tahun, persen_kenaikan
        FROM opex_config
        WHERE proyek_id = ?
        ORDER BY urutan ASC
    ');
    $stmt4->execute([$pid]);
    $opex_segments = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. Hitung per tahun ───────────────────────────────────────────────
    $rows = [];
    $total_produksi = 0.0;
    $total_income   = 0.0;
    $total_opex_sum = 0.0;
    $total_dep_sum  = 0.0;
    $total_tax_sum  = 0.0;
    $total_ncf_sum  = 0.0;

    // Untuk produksi auto-decline: kita perlu tahu produksi tahun terakhir
    // yang ada data manualnya — atau yang sudah dihitung sebelumnya.
    $produksi_prev = null;  // produksi tahun sebelumnya (untuk chaining decline)

    for ($t = 1; $t <= $jangka_waktu; $t++) {

        // --- Produksi ---
        if (isset($produksi_manual[$t])) {
            // Pakai angka manual dari DB
            $produksi = $produksi_manual[$t];
        } else {
            // Hitung decline dari tahun sebelumnya
            $decline_pct = _get_decline_rate($t, $decline_rules);
            if ($decline_pct !== null && $produksi_prev !== null) {
                $produksi = $produksi_prev * (1 - $decline_pct / 100);
            } else {
                // Tidak ada decline rule dan tidak ada data manual → 0
                $produksi = 0.0;
            }
        }
        $produksi_prev = $produksi;

        // --- Income ---
        // Income = produksi (Mbbl) × 1000 (bbl/Mbbl) × harga ($/bbl) / 1_000_000 (→ $M)
        // Atau: jika volume sudah dalam satuan konsisten, pakai langsung:
        //   produksi Mbbl × harga = hasil dalam $M (karena M bbl × $/bbl = $M bbl)
        //   tapi soal pakai: volume (Mbbl) × harga ($/bbl) → satuan $M? 
        //   Cek: 175 Mbbl × $32/bbl = 5600 M$ sudah benar ($M)
        $income = $produksi * $harga_minyak;  // $M

        // --- OPEX ---
        $opex = _hitung_opex($t, $opex_segments);

        // --- Depresiasi ---
        $depresiasi = $depresiasi_per_tahun;

        // --- Taxable Income ---
        // TI = Income - Opex - Depresiasi
        $taxable_income = $income - $opex - $depresiasi;

        // --- Tax ---
        // Tax hanya dikenakan jika TI positif (tidak ada negative tax)
        $tax = $taxable_income > 0 ? $taxable_income * $tax_rate : 0.0;

        // --- NCF tahunan ---
        // NCF = Income - Opex - Tax - Reinvestasi_tahun_ini
        $reinvestasi_t = $reinvestasi_per_tahun[$t] ?? 0.0;
        $ncf = $income - $opex - $tax - $reinvestasi_t;

        // Akumulasi
        $total_produksi += $produksi;
        $total_income   += $income;
        $total_opex_sum += $opex;
        $total_dep_sum  += $depresiasi;
        $total_tax_sum  += $tax;
        $total_ncf_sum  += $ncf;

        $rows[] = [
            'tahun'          => $t,
            'tahun_kalender' => (int)$project['tahun_awal'] + $t,
            'produksi'       => round($produksi, 4),
            'income'         => round($income, 4),
            'opex'           => round($opex, 4),
            'depresiasi'     => round($depresiasi, 4),
            'taxable_income' => round($taxable_income, 4),
            'tax'            => round($tax, 4),
            'ncf'            => round($ncf, 4),
            'reinvestasi'    => $reinvestasi_t,
            'is_manual_prod' => isset($produksi_manual[$t]),
        ];
    }

    $total_ncf_setelah_investasi = $total_ncf_sum - $total_investasi_awal;

    $summary = [
        'total_produksi'             => round($total_produksi, 4),
        'total_income'               => round($total_income, 4),
        'total_opex'                 => round($total_opex_sum, 4),
        'total_depresiasi'           => round($total_dep_sum, 4),
        'total_tax'                  => round($total_tax_sum, 4),
        'total_ncf_operasi'          => round($total_ncf_sum, 4),
        'total_investasi'            => round($total_investasi_awal, 4),
        'total_capital'              => round($total_capital_awal, 4),
        'total_noncapital'           => round($total_noncapital_awal, 4),
        'total_ncf_setelah_investasi'=> round($total_ncf_setelah_investasi, 4),
        'status_kelayakan'           => $total_ncf_setelah_investasi >= 0
                                            ? 'Layak (NCF Positif)'
                                            : 'Tidak Layak (NCF Negatif)',
    ];

    return [
        'rows'    => $rows,
        'summary' => $summary,
    ];
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPER: Cari decline rate yang berlaku pada tahun ke-$t
// Logika: ambil rule dengan mulai_tahun_ke TERBESAR yang masih <= $t
// Return: float persen, atau null jika tidak ada rule yang berlaku
// ─────────────────────────────────────────────────────────────────────────────
function _get_decline_rate(int $t, array $rules): ?float
{
    $applicable = null;
    foreach ($rules as $mulai => $pct) {
        if ($mulai <= $t) {
            $applicable = $pct;  // rules sudah urut ASC, jadi keep overwrite
        }
    }
    return $applicable;
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPER: Hitung OPEX untuk tahun ke-$t dari segmen-segmen config
//
// Algoritma:
//  Iterasi segmen (urutan ASC).
//  Ambil segmen pertama di mana $t <= berlaku_sampai_tahun (atau berlaku_sampai=NULL).
//  Dalam segmen tersebut:
//    - Jika $t < kenaikan_mulai_tahun (atau tidak ada kenaikan) → pakai opex_base
//    - Jika $t >= kenaikan_mulai_tahun → opex_base × (1 + pct/100)^(t - mulai_kenaikan)
// ─────────────────────────────────────────────────────────────────────────────
function _hitung_opex(int $t, array $segments): float
{
    foreach ($segments as $seg) {
        $sampai  = $seg['berlaku_sampai_tahun'] !== null
                   ? (int)$seg['berlaku_sampai_tahun']
                   : PHP_INT_MAX;

        if ($t > $sampai) {
            continue;  // tahun ini di luar jangkauan segmen ini
        }

        // Segmen ini berlaku
        $base    = (float) $seg['opex_base'];
        $mulai_k = $seg['kenaikan_mulai_tahun'] !== null
                   ? (int)$seg['kenaikan_mulai_tahun']
                   : null;
        $pct_k   = $seg['persen_kenaikan'] !== null
                   ? (float)$seg['persen_kenaikan']
                   : 0.0;

        if ($mulai_k !== null && $t >= $mulai_k && $pct_k > 0) {
            // Jumlah tahun kenaikan = t - mulai_k (tahun mulai_k sendiri = kenaikan ke-0 = base)
            // Tahun mulai_k: opex = base × (1+r)^0 = base
            // Tahun mulai_k + 1: opex = base × (1+r)^1
            $n = $t - $mulai_k;
            return $base * pow(1 + $pct_k / 100, $n);
        }

        return $base;
    }

    // Fallback: jika tidak ada segmen yang cocok → opex = 0
    return 0.0;
}
