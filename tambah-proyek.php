<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tambah Proyek';
$activePage = 'proyek';
include __DIR__ . '/includes/header.php';
?>

<form action="proses/simpan-proyek.php" method="POST" class="project-form space-y-6 w-full max-w-none">
    <?php include __DIR__ . '/includes/form-proyek.php'; ?>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>