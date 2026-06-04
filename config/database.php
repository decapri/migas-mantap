<?php
$host = 'localhost';
$dbname = 'ncf_oilfield';
$username = 'root';
$password = '';
$port = 3307;

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    echo "Koneksi berhasil";
} catch (PDOException $e) {
    die('Koneksi database gagal: ' . $e->getMessage());
}
?>