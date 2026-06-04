<?php
require_once __DIR__ . '/../config/database.php';

$id = (int)($_POST['id'] ?? 0);

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | UPDATE FIELDS
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE fields SET
            nama = ?,
            lokasi_lapangan = ?,
            cadangan_mbbl = ?,
            harga_minyak = ?,
            tax_rate = ?,
            tahun_perhitungan = ?,
            status_proyek = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $_POST['nama_proyek'],
        $_POST['lokasi_lapangan'],
        $_POST['cadangan_mbbl'],
        $_POST['harga_minyak'],
        $_POST['tax_rate'],
        $_POST['tahun_perhitungan'],
        $_POST['status_proyek'],
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPDATE INVESTASI
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE investasi SET
            capital = ?,
            non_capital = ?
        WHERE field_id = ?
    ");

    $stmt->execute([
        $_POST['capital'],
        $_POST['non_capital'],
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPDATE DECLINE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE production_decline SET
            mulai_tahun = ?,
            laju_persen = ?
        WHERE field_id = ?
    ");

    $stmt->execute([
        $_POST['mulai_decline'],
        $_POST['decline_rate'],
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPDATE OPEX
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE opex_params SET
            base_usd_m = ?,
            berlaku_sampai_tahun = ?,
            eskalasi_persen = ?
        WHERE field_id = ?
    ");

    $stmt->execute([
        $_POST['opex_base'],
        $_POST['opex_until'],
        $_POST['opex_eskalasi'],
        $id
    ]);

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATA PRODUKSI
    |--------------------------------------------------------------------------
    */

    $pdo->prepare("
        DELETE FROM production_data
        WHERE field_id = ?
    ")->execute([$id]);

    $stmt = $pdo->prepare("
        INSERT INTO production_data (
            field_id,
            tahun_ke,
            produksi_mbbl
        )
        VALUES (?,?,?)
    ");

    $produksi = [
        $_POST['produksi1'] ?? null,
        $_POST['produksi2'] ?? null,
        $_POST['produksi3'] ?? null,
        $_POST['produksi4'] ?? null
    ];

    foreach ($produksi as $i => $value) {

        if ($value === null || $value === '') {
            continue;
        }

        $stmt->execute([
            $id,
            $i + 1,
            $value
        ]);
    }

    $pdo->commit();

    header('Location: ../detail-proyek.php?id=' . $id . '&updated=1');
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());
}