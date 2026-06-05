<?php

/**
 * Hitung NCF per tahun berdasarkan data project dari DB.
 *
 * $project  = row dari fields JOIN investasi JOIN production_decline JOIN opex_params
 * $produksiManual = array [ tahun_ke => produksi ] dari tabel production_manual
 */
function hitung_ncf(array $project, array $produksiManual = []): array {

    // ------------------------------------------------------------------
    // Parameter utama
    // diperbaiki: nama key disesuaikan dengan kolom DB (tahun_hitung, tax_rate, dst)
    // ------------------------------------------------------------------
    $jangkaWaktu  = max((int)($project['tahun_hitung']   ?? 10), 1);  // diperbaiki: 'jangka_waktu' → 'tahun_hitung'
    $harga        = (float)($project['harga_minyak']     ?? 0);       // diperbaiki: 'harga_minyak_usd' → 'harga_minyak'
    $capital      = (float)($project['capital']          ?? 0);
    $nonCapital   = (float)($project['non_capital']      ?? 0);
    $totalInvest  = $capital + $nonCapital;

    $taxRate      = (float)($project['tax_rate']         ?? 0);       // diperbaiki: 'pajak_penghasilan' → 'tax_rate'

    $opexAwal     = (float)($project['base_usd_m']       ?? 0);       // diperbaiki: 'opex_tahun' → 'base_usd_m'
    $opexUntil    = (int)($project['base_hingga_thn']    ?? 3);       // diperbaiki: hardcode 3 → dari kolom 'base_hingga_thn'
    $kenaikanOpex = (float)($project['eskalasi_persen']  ?? 0);       // diperbaiki: 'kenaikan_opex' → 'eskalasi_persen'

    $mulaiDecline = (int)($project['mulai_tahun_ke']     ?? 5);       // diperbaiki: sudah benar tapi fallback disesuaikan
    $decline      = (float)($project['laju_persen']      ?? 0);       // diperbaiki: 'decline_produksi' → 'laju_persen'

    // Depresiasi straight-line: Di = total investasi / jangka waktu
    $depresiasi   = $totalInvest > 0 ? $totalInvest / $jangkaWaktu : 0;

    // ------------------------------------------------------------------
    // Produksi manual: pakai array dari DB, bukan hardcode 4 tahun
    // diperbaiki: sebelumnya hardcode produksi_tahun1..4 dari $project
    // ------------------------------------------------------------------
    $maxManual = count($produksiManual) > 0 ? max(array_keys($produksiManual)) : 0;

    // ------------------------------------------------------------------
    // Loop perhitungan
    // ------------------------------------------------------------------
    $rows               = [];
    $cumProduksi        = 0;
    $cumNcf             = 0;
    $produksiSebelumnya = 0;

    $totalProduksi      = 0;
    $totalIncome        = 0;
    $totalOpex          = 0;
    $totalTax           = 0;
    $totalNcfOperasi    = 0;

    for ($tahunKe = 1; $tahunKe <= $jangkaWaktu; $tahunKe++) {

        // Produksi tahun ini
        if (isset($produksiManual[$tahunKe])) {
            // Tahun yang ada data manual-nya
            $produksi = (float)$produksiManual[$tahunKe];
        } elseif ($tahunKe >= $mulaiDecline) {
            // Sudah masuk fase decline
            $produksi = $produksiSebelumnya * (1 - ($decline / 100));
        } else {
            // Antara manual dan decline: pakai nilai tahun sebelumnya (flat)
            $produksi = $produksiSebelumnya;
        }

        $produksi = max(0, $produksi);
        $produksiSebelumnya = $produksi;

        // Opex dengan eskalasi setelah periode base
        if ($tahunKe <= $opexUntil) {
            $opex = $opexAwal;
        } else {
            $opex = $opexAwal * pow(1 + ($kenaikanOpex / 100), $tahunKe - $opexUntil);
        }

        $grossRevenue   = $produksi * $harga;
        $taxableIncome  = $grossRevenue - $opex - $depresiasi;
        $tax            = $taxableIncome > 0 ? $taxableIncome * ($taxRate / 100) : 0;

        // NCF = Gross Revenue - Opex - Tax
        $ncf = $grossRevenue - $opex - $tax;

        // diperbaiki: cum_produksi dan cum_ncf dihitung SETELAH nilai tahun ini,
        // sebelumnya ditulis ke row SEBELUM ditambah sehingga selalu tertinggal 1 tahun
        $cumProduksi += $produksi;
        $cumNcf      += $ncf;

        $rows[] = [
            'tahun_ke'      => $tahunKe,
            'produksi_mbbl' => $produksi,
            'cum_produksi'  => $cumProduksi,
            'gross_revenue' => $grossRevenue,
            'opex'          => $opex,
            'depresiasi_di' => $depresiasi,
            'taxable_income'=> $taxableIncome,
            'tax'           => $tax,
            'ncf'           => $ncf,
            'cum_ncf'       => $cumNcf,
        ];

        $totalProduksi   += $produksi;
        $totalIncome     += $grossRevenue;
        $totalOpex       += $opex;
        $totalTax        += $tax;
        $totalNcfOperasi += $ncf;
    }

    $sisaCadangan             = max(((float)($project['cadangan_mbbl'] ?? 0)) - $totalProduksi, 0);
    $totalNcfSetelahInvestasi = $totalNcfOperasi - $totalInvest;

    return [
        'rows'    => $rows,
        'summary' => [
            'total_produksi'              => $totalProduksi,
            'total_gross_revenue'         => $totalIncome,
            'total_opex'                  => $totalOpex,
            'total_tax'                   => $totalTax,
            'total_investasi'             => $totalInvest,
            'total_ncf_operasi'           => $totalNcfOperasi,
            'total_ncf_setelah_investasi' => $totalNcfSetelahInvestasi,
            'sisa_cadangan'               => $sisaCadangan,
            'depresiasi'                  => $depresiasi,
            'status_kelayakan'            => $totalNcfSetelahInvestasi >= 0
                                                ? 'Berpotensi Menguntungkan'
                                                : 'Perlu Dikaji Kembali',
        ]
    ];
}

/**
 * Simpan hasil kalkulasi NCF ke tabel ncf_results.
 * Dipanggil dari simpan-proyek.php dan update-proyek.php setelah data tersimpan.
 *
 * $pdo      = koneksi PDO
 * $fieldId  = id lapangan yang baru disimpan/diupdate
 */
function simpan_ncf_results(PDO $pdo, int $fieldId): void {

    // Ambil semua parameter yang dibutuhkan
    $stmt = $pdo->prepare("
        SELECT
            f.*,
            i.capital,
            i.non_capital,
            pd.mulai_tahun_ke,
            pd.laju_persen,
            op.base_usd_m,
            op.base_hingga_thn,
            op.eskalasi_persen
        FROM fields f
        LEFT JOIN investasi i          ON i.field_id  = f.id
        LEFT JOIN production_decline pd ON pd.field_id = f.id
        LEFT JOIN opex_params op        ON op.field_id = f.id
        WHERE f.id = ?
    ");
    $stmt->execute([$fieldId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) return;

    // Ambil data produksi manual [ tahun_ke => produksi ]
    $stmt = $pdo->prepare("
        SELECT tahun_ke, produksi
        FROM production_manual
        WHERE field_id = ?
        ORDER BY tahun_ke
    ");
    $stmt->execute([$fieldId]);
    $produksiManual = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [ tahun_ke => produksi ]

    // Hitung
    $hasil = hitung_ncf($project, $produksiManual);

    // Hapus hasil lama lalu insert yang baru
    $pdo->prepare("DELETE FROM ncf_results WHERE field_id = ?")->execute([$fieldId]);

    $insert = $pdo->prepare("
        INSERT INTO ncf_results (
            field_id,
            tahun_ke,
            produksi_mbbl,
            cum_produksi,
            gross_revenue,
            opex,
            depresiasi_di,
            taxable_income,
            tax,
            ncf,
            cum_ncf
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");

    foreach ($hasil['rows'] as $r) {
        $insert->execute([
            $fieldId,
            $r['tahun_ke'],
            $r['produksi_mbbl'],
            $r['cum_produksi'],
            $r['gross_revenue'],
            $r['opex'],
            $r['depresiasi_di'],
            $r['taxable_income'],
            $r['tax'],
            $r['ncf'],
            $r['cum_ncf'],
        ]);
    }
}