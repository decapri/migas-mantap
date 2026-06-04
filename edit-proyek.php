<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
    SELECT *
    FROM fields
    WHERE id = ?
");
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) die('Data proyek tidak ditemukan.');
$pageTitle = 'Edit Proyek';
$activePage = 'proyek';
include __DIR__ . '/includes/header.php';
?>
<form action="proses/update-proyek.php" method="POST" class="space-y-6 max-w-6xl">
    <input type="hidden" name="id" value="<?= e($project['id']) ?>">
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <h2 class="text-2xl font-bold">Edit Proyek Sumur</h2>
        <p class="text-slate-500">Ubah data proyek dan parameter perhitungan.</p>
    </div>
    <?php include __DIR__ . '/includes/form-proyek.php'; ?>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
