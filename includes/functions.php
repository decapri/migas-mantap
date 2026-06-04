<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function format_number($value, $decimals = 2) {
    return number_format((float)$value, $decimals, ',', '.');
}

function format_usd_m($value) {
    return '$ ' . number_format((float)$value, 2, '.', ',') . ' M';
}

function format_rupiah($value) {
    return 'Rp ' . number_format((float)$value, 0, ',', '.');
}

function status_badge($status) {
    $class = 'bg-slate-100 text-slate-700';
    if ($status === 'Berjalan') $class = 'bg-green-100 text-green-700';
    if ($status === 'Direncanakan') $class = 'bg-blue-100 text-blue-700';
    if ($status === 'Selesai') $class = 'bg-orange-100 text-orange-700';
    return '<span class="px-3 py-1 rounded-full text-xs font-semibold '.$class.'">'.e($status).'</span>';
}

function get_usd_to_idr_rate() {
    // Konsep API kurs: mencoba membaca kurs USD-IDR dari API publik.
    // Jika gagal karena internet/server tidak mengizinkan, sistem memakai nilai fallback.
    $fallback = 15700;
    $cacheFile = __DIR__ . '/../assets/js/kurs-cache.json';
    $cacheMaxAge = 3600; // 1 jam

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheMaxAge)) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        if (!empty($cached['rate'])) return (float)$cached['rate'];
    }

    $url = 'https://open.er-api.com/v6/latest/USD';
    $json = @file_get_contents($url);
    if ($json !== false) {
        $data = json_decode($json, true);
        if (isset($data['rates']['IDR'])) {
            $rate = (float)$data['rates']['IDR'];
            @file_put_contents($cacheFile, json_encode(['rate' => $rate, 'updated_at' => date('c')]));
            return $rate;
        }
    }
    return $fallback;
}
function ambil_hasil_ncf($fieldId)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT *
        FROM ncf_results
        WHERE field_id = ?
        ORDER BY tahun_ke
    ");

    $stmt->execute([$fieldId]);
    $rows = [];

    while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // kembalikan kolom dengan nama yang sama seperti di DB agar kode view tetap kompatibel
        $rows[] = [
            'tahun' => $r['tahun_ke'],
            'produksi_mbbl' => isset($r['produksi_mbbl']) ? (float)$r['produksi_mbbl'] : 0,
            'gross_revenue' => isset($r['gross_revenue']) ? (float)$r['gross_revenue'] : 0,
            'opex' => isset($r['opex']) ? (float)$r['opex'] : 0,
            'depresiasi_di' => isset($r['depresiasi_di']) ? (float)$r['depresiasi_di'] : 0,
            'taxable_income' => isset($r['taxable_income']) ? (float)$r['taxable_income'] : 0,
            'tax' => isset($r['tax']) ? (float)$r['tax'] : 0,
            'ncf' => isset($r['ncf']) ? (float)$r['ncf'] : 0
        ];
    }

    $sum = $pdo->prepare("
        SELECT *
        FROM v_ncf_summary
        WHERE field_id = ?
    ");

    $sum->execute([$fieldId]);

    $summaryDb = $sum->fetch(PDO::FETCH_ASSOC) ?: [];

    $inv = $pdo->prepare("
        SELECT total_investasi
        FROM investasi
        WHERE field_id = ?
    ");

    $inv->execute([$fieldId]);

    $investasi = $inv->fetchColumn();
    $investasi = $investasi !== false ? (float)$investasi : 0;

    $totalNcfKumulatif = isset($summaryDb['total_ncf_kumulatif']) ? (float)$summaryDb['total_ncf_kumulatif'] : 0;

    // sediakan kunci yang digunakan di view (compatibility)
    $totalGrossRevenue = isset($summaryDb['total_gross_revenue']) ? (float)$summaryDb['total_gross_revenue'] : 0;

    return [
        'rows' => $rows,
        'summary' => [
            'total_produksi' => isset($summaryDb['total_produksi_mbbl']) ? (float)$summaryDb['total_produksi_mbbl'] : 0,
            'total_gross_revenue' => $totalGrossRevenue,
            'total_income' => $totalGrossRevenue, // alias lama
            'total_tax' => isset($summaryDb['total_tax']) ? (float)$summaryDb['total_tax'] : 0,
            'total_ncf_setelah_investasi' => $totalNcfKumulatif - $investasi,
            'total_investasi' => $investasi,
            'status_kelayakan' =>
                ($totalNcfKumulatif - $investasi) >= 0
                ? 'LAYAK'
                : 'TIDAK LAYAK'
        ]
    ];
}
?>
