<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/perhitungan.php';

$pageTitle = 'Proyek Sumur';
$activePage = 'proyek';

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$where = [];
$params = [];

if ($search !== '') {
    $where[] = 'f.nama LIKE ?';
    $params[] = "%$search%";
}

if ($status !== '') {
    $where[] = 'f.status_proyek = ?';
    $params[] = $status;
}

$sql = "
SELECT
    f.*,
    COALESCE(v.total_ncf_kumulatif,0) AS total_ncf
FROM fields f
LEFT JOIN v_ncf_summary v
    ON v.field_id = f.id
";

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY f.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$projects = $stmt->fetchAll();
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <?php if (!empty($_GET['success'])): ?>
        <div class="rounded-xl px-4 py-3 text-sm font-semibold" style="background:#EAF8ED;color:#3F8F4D;border:1px solid #B7E5C0">Data proyek berhasil diproses.</div>
    <?php endif; ?>
    <div class="app-card rounded-[26px] p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold" style="color:var(--color-heading)">Daftar Proyek Sumur</h2>
            <p style="color:var(--color-muted)">Kelola proyek sumur migas yang sudah dibangun maupun yang masih dalam perencanaan.</p>
        </div>
        <a href="tambah-proyek.php" class="app-btn-primary px-5 py-3 rounded-xl font-semibold text-center transition shrink-0">+ Tambah Proyek</a>
    </div>

    <form method="GET" id="filter-form" class="app-card rounded-[22px] p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="text" name="search" value="<?= e($search) ?>"
            placeholder="Cari nama proyek…"
            class="px-4 py-3 rounded-xl app-input"
            oninput="clearTimeout(window._st);window._st=setTimeout(()=>document.getElementById('filter-form').submit(),400)">
        <select name="status" class="px-4 py-3 rounded-xl app-input"
            onchange="document.getElementById('filter-form').submit()">
            <option value="">Semua Status</option>
            <?php foreach (['Direncanakan','Berjalan','Selesai'] as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="app-card rounded-[22px] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="calculation-table w-full text-sm">
                <thead style="background:#FFF8EB;color:#7A5A1E">
                    <tr>
                        <th class="p-3 text-left">Nama Lapangan</th>
                        <th class="p-3 text-right">Cadangan</th>
                        <th class="p-3 text-right">Harga Minyak</th>
                        <th class="p-3 text-center">Tahun Analisis</th>
                        <th class="p-3 text-center">Tax Rate</th>
                        <th class="p-3 text-center">Status</th>
                        <th class="p-3 text-right">Total NCF</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$projects): ?>
                    <tr><td colspan="8" class="p-6 text-center" style="color:var(--color-muted)">Belum ada data proyek.</td></tr>
                <?php endif; ?>
                <?php foreach ($projects as $p): ?>
                    <tr class="border-t hover:bg-[#FFF9EE] transition" style="border-color:var(--color-divider)">
                        <td class="p-3 font-semibold" style="color:var(--color-heading)"><?= e($p['nama']) ?></td>
                        <td class="p-3 text-right"><?= format_number($p['cadangan_mbbl']) ?> Mbbl</td>
                        <td class="p-3 text-right"><?= format_usd_m($p['harga_minyak']) ?>/bbl</td>
                        <td class="p-3 text-center"><?= e($p['tahun_hitung']) ?></td>
                        <td class="p-3 text-center"><?= e($p['tax_rate']) ?>%</td>
                        <td class="p-3 text-center"><?= status_badge($p['status_proyek'] ?? '-') ?></td>
                        <td class="p-3 text-right font-semibold text-[#3F8F4D]"><?= format_usd_m($p['total_ncf']) ?></td>
                        <td class="p-3">
                            <div class="flex justify-center gap-2">
                                <a href="detail-proyek.php?id=<?= e($p['id']) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background:#FFF8E6;color:#D88912">Detail</a>
                                <a href="edit-proyek.php?id=<?= e($p['id']) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background:#FFF8E6;color:#D88912">Edit</a>
                                <a onclick="return confirm('Hapus proyek ini?')" href="hapus-proyek.php?id=<?= e($p['id']) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background:#FEF2F2;color:#E46A61">Hapus</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>