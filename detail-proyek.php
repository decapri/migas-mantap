<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/perhitungan.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
SELECT
    f.*,
    i.capital,
    i.non_capital,
    pd.laju_persen,
    op.base_usd_m,
    op.eskalasi_persen
FROM fields f
LEFT JOIN investasi i
    ON i.field_id = f.id
LEFT JOIN production_decline pd
    ON pd.field_id = f.id
LEFT JOIN opex_params op
    ON op.field_id = f.id
WHERE f.id = ?
");

$stmt->execute([$id]);
$project = $stmt->fetch();

if (!$project) {
    die('Data lapangan tidak ditemukan');
}
$stmt = $pdo->prepare("
SELECT *
FROM ncf_results
WHERE field_id = ?
ORDER BY tahun_ke
");

$stmt->execute([$id]);

$rows = $stmt->fetchAll();
$summary = [
    'total_produksi' => array_sum(array_column($rows,'produksi_mbbl')),
    'total_income' => array_sum(array_column($rows,'gross_revenue')),
    'total_opex' => array_sum(array_column($rows,'opex')),
    'total_tax' => array_sum(array_column($rows,'tax')),
    'total_ncf' => end($rows)['cum_ncf'] ?? 0,
    'total_investasi' =>
        ($project['capital'] ?? 0)
        +
        ($project['non_capital'] ?? 0)
];
$kurs = get_usd_to_idr_rate();
$pageTitle = 'Detail Proyek';
$activePage = 'proyek';
include __DIR__ . '/includes/header.php';
?>
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold"><?= e($project['nama']) ?></h2>
                <p class="text-slate-500 mt-1">Cadangan: <?= format_number($project['cadangan_mbbl']) ?> Mbbl</p>
            </div>
            <div class="flex gap-3">
                <a href="edit-proyek.php?id=<?= e($project['id']) ?>" class="bg-amber-500 text-white px-5 py-3 rounded-xl font-semibold">Edit Proyek</a>
                <a href="proyek.php" class="bg-white border border-slate-200 px-5 py-3 rounded-xl font-semibold">Kembali</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Status</div>
            <div class="mt-2"><?= status_badge($project['status_proyek'] ?? 'Tidak tersedia') ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Capital</div>
            <div class="text-xl font-bold mt-2"><?= format_usd_m($project['capital']) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Non Capital</div>
            <div class="text-xl font-bold mt-2"><?= format_usd_m($project['non_capital']) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Harga Minyak</div>
            <div class="text-xl font-bold mt-2">$<?= format_number($project['harga_minyak']) ?>/bbl</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Tax Rate</div>
            <div class="text-xl font-bold mt-2"><?= format_number($project['tax_rate']) ?>%</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Decline Rate</div>
            <div class="text-xl font-bold mt-2"><?= format_number($project['laju_persen']) ?>%</div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200">
            <h3 class="font-bold text-lg">Tabel Perhitungan Tahunan</h3>
            <p class="text-sm text-slate-500">Rumus mengikuti contoh spreadsheet: Income - OPEX - Depresiasi = Taxable Income, lalu NCF = Taxable Income - Tax.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
<thead class="bg-slate-900 text-white sticky top-0 z-10">
<tr>
    <th class="p-3 text-left">Tahun</th>
    <th class="p-3 text-right">Produksi (Mbbl)</th>
    <th class="p-3 text-right">Cum Produksi</th>
    <th class="p-3 text-right">Gross Revenue</th>
    <th class="p-3 text-right">OPEX</th>
    <th class="p-3 text-right">Depresiasi</th>
    <th class="p-3 text-right">Taxable Income</th>
    <th class="p-3 text-right">Tax</th>
    <th class="p-3 text-right">NCF</th>
    <th class="p-3 text-right">Cum NCF</th>
</tr>
</thead>
                <tbody>
                    <tr class="bg-slate-50 border-t border-slate-100 hover:bg-slate-100">
                        <td class="p-3 font-semibold">0</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right font-bold text-red-600">-<?= format_usd_m($summary['total_investasi']) ?></td>
                        <td class="p-3 text-right font-bold text-red-600">-<?= format_usd_m($summary['total_investasi']) ?></td>
                    </tr>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="p-3 font-semibold"><?= $r['tahun_ke'] ?></td>
                        <td class="p-3 text-right"><?= format_number($r['produksi_mbbl']) ?></td>
                        <td class="p-3 text-right"><?= format_number($r['cum_produksi']) ?></td>
                        <td class="p-3 text-right"><?= format_usd_m($r['gross_revenue']) ?></td>
                        <td class="p-3 text-right"><?= format_usd_m($r['opex']) ?></td>
                        <td class="p-3 text-right"><?= format_usd_m($r['depresiasi_di']) ?></td>
                        <td class="p-3 text-right <?= $r['taxable_income'] < 0 ? 'text-red-600' : '' ?>"><?= format_usd_m($r['taxable_income']) ?></td>
                        <td class="p-3 text-right text-red-600"><?= format_usd_m($r['tax']) ?></td>
                        <td class="p-3 text-right <?= $r['ncf'] < 0 ? 'text-red-600' : 'text-green-600' ?>"><?= format_usd_m($r['ncf']) ?></td>
                        <td class="p-3 text-right font-semibold <?= $r['cum_ncf'] < 0 ? 'text-red-600' : 'text-green-600' ?>"><?= format_usd_m($r['cum_ncf']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="bg-slate-900 text-white font-bold">
                        <td colspan="10" class="p-4 text-right">Total NCF Setelah Investasi: <?= format_usd_m($summary['total_ncf']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Total Produksi</div>
            <div class="text-xl font-bold mt-2"><?= format_number($summary['total_produksi']) ?> Mbbl</div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Total Revenue</div>
            <div class="text-xl font-bold mt-2"><?= format_usd_m($summary['total_income']) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Total OPEX</div>
            <div class="text-xl font-bold mt-2"><?= format_usd_m($summary['total_opex']) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Total Tax</div>
            <div class="text-xl font-bold mt-2 text-red-600"><?= format_usd_m($summary['total_tax']) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">Total Investasi</div>
            <div class="text-xl font-bold mt-2 text-amber-600"><?= format_usd_m($summary['total_investasi']) ?></div>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-sm text-slate-500">NCF Kumulatif</div>
            <div class="text-xl font-bold mt-2 text-green-600"><?= format_usd_m($summary['total_ncf']) ?></div>
        </div>
    </div>

        </div>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
