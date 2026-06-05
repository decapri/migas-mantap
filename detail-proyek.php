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
    i.total_investasi,
    pd.laju_persen,
    pd.mulai_tahun_ke,
    op.base_usd_m,
    op.base_hingga_thn,
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
$lastRow = !empty($rows) ? end($rows) : [];
$summary = [
    'total_produksi'  => array_sum(array_column($rows, 'produksi_mbbl')),
    'total_income'    => array_sum(array_column($rows, 'gross_revenue')),
    'total_opex'      => array_sum(array_column($rows, 'opex')),
    'total_tax'       => array_sum(array_column($rows, 'tax')),
    'total_ncf'       => $lastRow['cum_ncf'] ?? 0,
    'total_investasi' => (float)($project['total_investasi'] ?? 0)
                         ?: (($project['capital'] ?? 0) + ($project['non_capital'] ?? 0)),
];
$kurs = get_usd_to_idr_rate();
$pageTitle = 'Detail Proyek';
$activePage = 'proyek';
include __DIR__ . '/includes/header.php';
?>
<div class="space-y-5 lg:space-y-6 min-w-0">

    <!-- HERO HEADER -->
    <div class="app-card rounded-[26px] sm:rounded-[28px] p-5 sm:p-6 relative overflow-hidden min-w-0">
        <div class="absolute -right-16 -top-16 w-44 sm:w-48 h-44 sm:h-48 rounded-full opacity-40" style="background: var(--color-primary-pale);"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 min-w-0">
            <div class="min-w-0">
                <div class="mb-2"><?= status_badge($project['status_proyek'] ?? 'Tidak tersedia') ?></div>
                <h2 class="text-xl sm:text-2xl xl:text-3xl font-bold tracking-tight leading-tight" style="color: var(--color-heading);"><?= e($project['nama']) ?></h2>
                <p class="mt-1 text-sm" style="color: var(--color-muted);">Cadangan: <?= format_number($project['cadangan_mbbl']) ?> Mbbl</p>
            </div>
            <div class="flex gap-3 shrink-0">
                <a href="edit-proyek.php?id=<?= e($project['id']) ?>" class="app-btn-primary px-5 py-2.5 rounded-xl font-semibold text-sm transition">Edit Proyek</a>
                <a href="proyek.php" class="px-5 py-2.5 rounded-xl font-semibold text-sm transition" style="background: var(--color-surface); border: 1px solid var(--color-divider); color: var(--color-heading);">← Kembali</a>
            </div>
        </div>
    </div>

    <!-- PARAMETER PROYEK -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 min-w-0">
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="text-sm font-semibold" style="color: var(--color-muted);">Capital</div>
            <div class="text-lg xl:text-xl font-bold mt-1" style="color: var(--color-heading);"><?= format_usd_m($project['capital']) ?></div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="text-sm font-semibold" style="color: var(--color-muted);">Non-Capital</div>
            <div class="text-lg xl:text-xl font-bold mt-1" style="color: var(--color-heading);"><?= format_usd_m($project['non_capital']) ?></div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="text-sm font-semibold" style="color: var(--color-muted);">Harga Minyak</div>
            <div class="text-lg xl:text-xl font-bold mt-1" style="color: var(--color-heading);">$<?= format_number($project['harga_minyak']) ?>/bbl</div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="text-sm font-semibold" style="color: var(--color-muted);">Tax Rate</div>
            <div class="text-lg xl:text-xl font-bold mt-1" style="color: var(--color-heading);"><?= format_number($project['tax_rate']) ?>%</div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="text-sm font-semibold" style="color: var(--color-muted);">Decline Rate</div>
            <div class="text-lg xl:text-xl font-bold mt-1" style="color: var(--color-heading);"><?= format_number($project['laju_persen']) ?>%</div>
        </div>
        <?php
            $tahunHitung = (int)($project['tahun_hitung'] ?? 10);
            $totalInvest = ($project['capital'] ?? 0) + ($project['non_capital'] ?? 0);
            $di = $tahunHitung > 0 ? $totalInvest / $tahunHitung : 0;
        ?>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="text-sm font-semibold" style="color: var(--color-muted);">Depresiasi (DI)</div>
            <div class="text-lg xl:text-xl font-bold mt-1" style="color: #D88912;"><?= format_usd_m($di) ?></div>
            <div class="text-xs mt-1" style="color: var(--color-muted);">per tahun · <?= $tahunHitung ?> thn</div>
        </div>
    </div>

    <!-- SUMMARY NCF -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 min-w-0">
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="flex items-start justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <div class="text-sm font-semibold" style="color: var(--color-muted);">Total Produksi</div>
                    <div class="text-lg xl:text-xl font-bold mt-1 leading-tight" style="color: var(--color-heading);"><?= format_number($summary['total_produksi']) ?> Mbbl</div>
                </div>
                <div class="icon-box shrink-0">
                    <svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 20V6.5A2.5 2.5 0 0 1 10.5 4h3A2.5 2.5 0 0 1 16 6.5V20"/><path d="M6 20h12"/><path d="M10 8h4"/><path d="M10 12h4"/></svg>
                </div>
            </div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="flex items-start justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <div class="text-sm font-semibold" style="color: var(--color-muted);">Total Revenue</div>
                    <div class="text-lg xl:text-xl font-bold mt-1 leading-tight" style="color: var(--color-heading);"><?= format_usd_m($summary['total_income']) ?></div>
                </div>
                <div class="icon-box shrink-0" style="background: #FFF8E6; color: #D88912;">
                    <svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
            </div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="flex items-start justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <div class="text-sm font-semibold" style="color: var(--color-muted);">Total OPEX</div>
                    <div class="text-lg xl:text-xl font-bold mt-1 leading-tight" style="color: var(--color-heading);"><?= format_usd_m($summary['total_opex']) ?></div>
                </div>
                <div class="icon-box shrink-0">
                    <svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94z"/></svg>
                </div>
            </div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="flex items-start justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <div class="text-sm font-semibold" style="color: var(--color-muted);">Total Tax</div>
                    <div class="text-lg xl:text-xl font-bold mt-1 leading-tight text-[#E46A61]"><?= format_usd_m($summary['total_tax']) ?></div>
                </div>
                <div class="icon-box shrink-0" style="background: #FEF2F2; color: #E46A61;">
                    <svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12"/></svg>
                </div>
            </div>
        </div>
        <div class="app-card app-card-hover p-5 rounded-[22px] sm:rounded-[24px] min-w-0">
            <div class="flex items-start justify-between gap-2 min-w-0">
                <div class="min-w-0">
                    <div class="text-sm font-semibold" style="color: var(--color-muted);">Total Investasi</div>
                    <div class="text-lg xl:text-xl font-bold mt-1 leading-tight" style="color: #D88912;"><?= format_usd_m($summary['total_investasi']) ?></div>
                </div>
                <div class="icon-box shrink-0" style="background: #FFF8E6; color: #D88912;">
                    <svg class="ui-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                </div>
            </div>
        </div>
        <div class="p-5 rounded-[22px] sm:rounded-[24px] text-white relative overflow-hidden min-w-0" style="background: linear-gradient(135deg, #2F2A24, #7A5A1E); box-shadow: var(--shadow-card);">
            <div class="absolute -right-8 -top-8 w-24 h-24 rounded-full bg-white/10"></div>
            <div class="relative min-w-0">
                <div class="text-xs font-semibold uppercase tracking-wide text-white/65">NCF Kumulatif</div>
                <div class="mt-1 text-lg xl:text-xl font-bold leading-tight" style="color: #FFD27A;"><?= format_usd_m($summary['total_ncf']) ?></div>
                <div class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-bold" style="background: rgba(255,210,122,0.16); color: #FFD27A; border: 1px solid rgba(255,210,122,0.24);">
                    Setelah Investasi
                </div>
            </div>
        </div>
    </div>

    <!-- TABEL PERHITUNGAN -->
    <div class="app-card rounded-[24px] sm:rounded-[26px] overflow-hidden min-w-0">
        <div class="p-5 sm:p-6 border-b" style="border-color: var(--color-divider);">
            <h3 class="font-bold text-lg" style="color: var(--color-heading);">Tabel Perhitungan Tahunan</h3>
            <p class="text-sm mt-1" style="color: var(--color-muted);">Rumus: Income − OPEX − Depresiasi = Taxable Income &nbsp;|&nbsp; NCF = Taxable Income − Tax</p>
        </div>
        <div class="overflow-x-auto">
            <table class="calculation-table w-full text-[13px] sm:text-sm">
                <thead style="background: #FFF8EB; color: #7A5A1E;">
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
                    <tr class="border-t" style="background: #FFFDF8; border-color: var(--color-divider);">
                        <td class="p-3 font-bold" style="color: var(--color-heading);">0</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right">-</td>
                        <td class="p-3 text-right font-bold text-[#E46A61]">-<?= format_usd_m($summary['total_investasi']) ?></td>
                        <td class="p-3 text-right font-bold text-[#E46A61]">-<?= format_usd_m($summary['total_investasi']) ?></td>
                    </tr>
                    <?php foreach ($rows as $r): ?>
                    <tr class="border-t hover:bg-[#FFF9EE] transition" style="border-color: var(--color-divider);">
                        <td class="p-3 font-semibold" style="color: var(--color-heading);"><?= $r['tahun_ke'] ?></td>
                        <td class="p-3 text-right"><?= format_number($r['produksi_mbbl']) ?></td>
                        <td class="p-3 text-right"><?= format_number($r['cum_produksi']) ?></td>
                        <td class="p-3 text-right"><?= format_usd_m($r['gross_revenue']) ?></td>
                        <td class="p-3 text-right"><?= format_usd_m($r['opex']) ?></td>
                        <td class="p-3 text-right"><?= format_usd_m($r['depresiasi_di']) ?></td>
                        <td class="p-3 text-right <?= $r['taxable_income'] < 0 ? 'text-[#E46A61]' : '' ?>"><?= format_usd_m($r['taxable_income']) ?></td>
                        <td class="p-3 text-right text-[#E46A61]"><?= format_usd_m($r['tax']) ?></td>
                        <td class="p-3 text-right font-bold <?= $r['ncf'] < 0 ? 'text-[#E46A61]' : 'text-[#3F8F4D]' ?>"><?= format_usd_m($r['ncf']) ?></td>
                        <td class="p-3 text-right font-semibold <?= $r['cum_ncf'] < 0 ? 'text-[#E46A61]' : 'text-[#3F8F4D]' ?>"><?= format_usd_m($r['cum_ncf']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="background: linear-gradient(135deg, #2F2A24, #7A5A1E); color: #FFFDF8;">
                        <td class="p-4 text-lg sm:text-xl font-extrabold tracking-tight" colspan="9" style="color: #FFFDF8;">Total NCF Setelah Investasi</td>
                        <td class="p-4 text-right text-xl sm:text-2xl font-extrabold" style="color: #FFD27A;"><?= format_usd_m($summary['total_ncf']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php include __DIR__ . '/includes/footer.php'; ?>