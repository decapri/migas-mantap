<?php
require_once 'config/database.php';

echo "Database yang sedang dipakai: ";
$db = $pdo->query("SELECT DATABASE()")->fetchColumn();
echo $db . "<br>";

$stmt = $pdo->query("SELECT COUNT(*) FROM fields");
$total = $stmt->fetchColumn();

echo "Jumlah data fields: " . $total;
?>