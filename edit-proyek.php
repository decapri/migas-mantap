<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);

// diperbaiki: sebelumnya hanya SELECT dari fields saja,
// padahal form butuh data investasi, decline, opex, dan produksi manual
$stmt = $pdo->prepare("
     SELECT
        f.*,
        i.capital,
        i.non_capital,
        i.total_investasi,
        pd.mulai_tahun_ke,
        pd.laju_persen,
        op.base_usd_m,
        op.base_hingga_thn,
        op.eskalasi_persen
    FROM fields f
    LEFT JOIN investasi i           ON i.field_id  = f.id
    LEFT JOIN production_decline pd ON pd.field_id = f.id
    LEFT JOIN opex_params op        ON op.field_id = f.id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) die('Data proyek tidak ditemukan.');

// ditambahkan: ambil data produksi manual [ tahun_ke => produksi ]
$stmt = $pdo->prepare("
    SELECT tahun_ke, produksi
    FROM production_manual
    WHERE field_id = ?
    ORDER BY tahun_ke
");
$stmt->execute([$id]);
$produksiManual = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [ 1 => 175, 2 => 201, dst ]

$pageTitle = 'Edit Proyek';
$activePage = 'proyek';
include __DIR__ . '/includes/header.php';
?>
<form action="proses/update-proyek.php" method="POST" class="space-y-6 max-w-6xl">
    <input type="hidden" name="id" value="<?= e($project['id']) ?>">
    <?php include __DIR__ . '/includes/form-proyek.php'; ?>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>