<?php
function hitung_ncf(array $project): array {
    $jangkaWaktu = max((int)($project['jangka_waktu'] ?? 10), 1);
    $harga = (float)($project['harga_minyak_usd'] ?? 0);
    $capital = (float)($project['capital'] ?? 0);
    $nonCapital = (float)($project['non_capital'] ?? 0);
    $totalInvestasi = $capital + $nonCapital;

    $taxRate = (float)($project['pajak_penghasilan'] ?? 0);
    if ($taxRate <= 0) $taxRate = (float)($project['persentase_pajak'] ?? 0);

    $opexAwal = (float)($project['opex_tahun'] ?? 0);
    $kenaikanOpex = (float)($project['kenaikan_opex'] ?? 0);
    $decline = (float)($project['decline_produksi'] ?? 0);

    // Jika nilai depresiasi tidak diisi, hitung otomatis dengan metode garis lurus.
    // Mengikuti contoh Excel: Depresiasi = total investasi / jangka waktu.
    $depresiasi = (float)($project['nilai_depresiasi'] ?? 0);
    if ($depresiasi <= 0) $depresiasi = $totalInvestasi / $jangkaWaktu;

    $produksiAwal = [
        1 => (float)($project['produksi_tahun1'] ?? 0),
        2 => (float)($project['produksi_tahun2'] ?? 0),
        3 => (float)($project['produksi_tahun3'] ?? 0),
        4 => (float)($project['produksi_tahun4'] ?? 0),
    ];

    $rows = [];
    $totalProduksi = 0;
    $totalIncome = 0;
    $totalOpex = 0;
    $totalTax = 0;
    $totalNcfOperasi = 0;
    $produksiSebelumnya = 0;
    $opexSebelumnya = $opexAwal;
    $cumProduksi = 0;
    $cumNcf = 0;
    $mulaiDecline = (int)($project['mulai_tahun_ke'] ?? 5);

    for ($tahunKe = 1; $tahunKe <= $jangkaWaktu; $tahunKe++) {
        if ($tahunKe <= 4) {
    $produksi = $produksiAwal[$tahunKe] ?? 0;
}
elseif ($tahunKe >= $mulaiDecline) {
    $produksi = $produksiSebelumnya * (1 - ($decline / 100));
}
else {
    $produksi = $produksiSebelumnya;
}
        $produksiSebelumnya = $produksi;

        $opexUntil = 3;

if ($tahunKe <= $opexUntil) {
    $opex = $opexAwal;
} else {
    $opex = $opexAwal * pow(
        1 + ($kenaikanOpex / 100),
        $tahunKe - $opexUntil
    );
}
        $opexSebelumnya = $opex;

$grossRevenue = $produksi * $harga;

$taxableIncome = $grossRevenue - $opex - $depresiasi;

$tax = $taxableIncome > 0
    ? $taxableIncome * ($taxRate / 100)
    : 0;

/*
 * NCF sesuai kalkulator HTML
 * NCF = Gross Revenue - Opex - Tax
 */
$ncf = $grossRevenue - $opex - $tax;

       $rows[] = [
    'tahun_ke' => $tahunKe,
    'tahun' => ((int)$project['tahun_awal'] + $tahunKe - 1),

    'produksi_mbbl' => $produksi,
    'cum_produksi' => $cumProduksi,

    'gross_revenue' => $grossRevenue,
    'opex' => $opex,
    'depresiasi_di' => $depresiasi,

    'taxable_income' => $taxableIncome,
    'tax' => $tax,

    'ncf' => $ncf,
    'cum_ncf' => $cumNcf
];

        $totalProduksi += $produksi;
        $totalIncome += $grossRevenue;
        $totalOpex += $opex;
        $totalTax += $tax;
        $totalNcfOperasi += $ncf;
    }

    $totalNcfSetelahInvestasi = $totalNcfOperasi - $totalInvestasi;
    $sisaCadangan = max(((float)($project['cadangan_mbbl'] ?? 0)) - $totalProduksi, 0);

    return [
        'rows' => $rows,
        'summary' => [
            'total_produksi' => $totalProduksi,
            'total_gross_revenue' => $totalIncome,
            'total_opex' => $totalOpex,
            'total_tax' => $totalTax,
            'total_investasi' => $totalInvestasi,
            'total_ncf_operasi' => $totalNcfOperasi,
            'total_ncf_setelah_investasi' => $totalNcfSetelahInvestasi,
            'sisa_cadangan' => $sisaCadangan,
            'depresiasi' => $depresiasi,
            'status_kelayakan' => $totalNcfSetelahInvestasi >= 0 ? 'Berpotensi Menguntungkan' : 'Perlu Dikaji Kembali',
        ]
    ];
}
?>
