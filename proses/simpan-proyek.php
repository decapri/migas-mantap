<?php

require_once __DIR__ . '/../config/database.php';

try {

    $pdo->beginTransaction();

    // seluruh proses insert

    $pdo->commit();

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());
}
?>
<?php

require_once __DIR__ . '/../config/database.php';

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | 1. Simpan data field
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO fields (
            nama,
            cadangan_mbbl,
            harga_minyak,
            tax_rate,
            tahun_perhitungan,
            status_proyek
        )
        VALUES (?,?,?,?,?,?)
    ");

    $stmt->execute([
        $_POST['nama_proyek'],
        $_POST['cadangan_mbbl'],
        $_POST['harga_minyak'],
        $_POST['tax_rate'],
        $_POST['tahun_perhitungan'],
        $_POST['status_proyek']
    ]);

    $fieldId = $pdo->lastInsertId();

    /*
    |--------------------------------------------------------------------------
    | 2. Simpan investasi
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO investasi (
            field_id,
            capital,
            non_capital
        )
        VALUES (?,?,?)
    ");

    $stmt->execute([
        $fieldId,
        $_POST['capital'],
        $_POST['non_capital']
    ]);

    /*
    |--------------------------------------------------------------------------
    | 3. Simpan decline rate
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO production_decline (
            field_id,
            mulai_tahun,
            laju_persen
        )
        VALUES (?,?,?)
    ");

    $stmt->execute([
        $fieldId,
        $_POST['mulai_decline'],
        $_POST['decline_rate']
    ]);

    /*
    |--------------------------------------------------------------------------
    | 4. Simpan parameter OPEX
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO opex_params (
            field_id,
            base_usd_m,
            berlaku_sampai_tahun,
            eskalasi_persen
        )
        VALUES (?,?,?,?)
    ");

    $stmt->execute([
        $fieldId,
        $_POST['opex_base'],
        $_POST['opex_until'],
        $_POST['opex_eskalasi']
    ]);

    /*
    |--------------------------------------------------------------------------
    | 5. Simpan data produksi awal
    |--------------------------------------------------------------------------
    */

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
            $fieldId,
            $i + 1,
            $value
        ]);
    }

    $pdo->commit();

    header('Location: ../proyek.php?success=1');
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());
}