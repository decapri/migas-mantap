<?php
require_once __DIR__ . '/config/database.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('DELETE FROM proyek_sumur WHERE id = ?');
$stmt->execute([$id]);
header('Location: proyek.php?success=1');
exit;
?>
