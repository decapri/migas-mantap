<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Tambah Proyek';
$activePage = 'proyek';
include __DIR__ . '/includes/header.php';
?>

<form action="proses/simpan-proyek.php" method="POST" class="project-form space-y-6 w-full max-w-none">
    <div class="w-full rounded-2xl p-5 shadow-sm border relative overflow-hidden" style="background: linear-gradient(135deg, #2F2A24, #7A5A1E); border-color: rgba(255,210,122,.22);">
        <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full" style="background: rgba(255,255,255,.10);"></div>
        <div class="relative">
            <h2 class="text-2xl font-bold text-white">Tambah Proyek Sumur</h2>
            <p class="text-white/70">Isi data proyek, parameter perhitungan, produksi, dan keuangan.</p>
        </div>
    </div>

    <?php include __DIR__ . '/includes/form-proyek.php'; ?>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>