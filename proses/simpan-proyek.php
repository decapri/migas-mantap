<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/perhitungan.php'; // ditambahkan: untuk memanggil simpan_ncf_results()

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
            tahun_hitung,       -- diperbaiki: form kirim 'tahun_perhitungan', kolom DB = 'tahun_hitung'
            status_proyek       -- diperbaiki: kolom ini ditambahkan ke tabel fields (lihat ncf_oilfield.sql)
        )
        VALUES (?,?,?,?,?,?)
    ");

    $stmt->execute([
        $_POST['nama_proyek'],
        $_POST['cadangan_mbbl'],
        $_POST['harga_minyak'],
        $_POST['tax_rate'],
        $_POST['tahun_perhitungan'], // nama input form tetap sama, hanya kolom DB-nya yang diluruskan
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
            mulai_tahun_ke,     -- diperbaiki: sebelumnya 'mulai_tahun', kolom DB = 'mulai_tahun_ke'
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
            base_hingga_thn,    -- diperbaiki: sebelumnya 'berlaku_sampai_tahun', kolom DB = 'base_hingga_thn'
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
        INSERT INTO production_manual (  -- diperbaiki: sebelumnya 'production_data', tabel DB = 'production_manual'
            field_id,
            tahun_ke,
            produksi             -- diperbaiki: sebelumnya 'produksi_mbbl', kolom DB = 'produksi'
        )
        VALUES (?,?,?)
    ");

    $produksiInputs = array_filter($_POST, function ($key) {
        return preg_match('/^produksi(\d+)$/', $key);
    }, ARRAY_FILTER_USE_KEY);

    // Sort by year index so tahun_ke remains ordered
    uksort($produksiInputs, function ($a, $b) {
        return intval(substr($a, 8)) <=> intval(substr($b, 8));
    });

    foreach ($produksiInputs as $key => $value) {
        if ($value === null || $value === '') {
            continue;
        }

        $tahunKe = intval(substr($key, 8));
        if ($tahunKe <= 0) {
            continue;
        }

        $stmt->execute([
            $fieldId,
            $tahunKe,
            $value
        ]);
    }

    // ditambahkan: hitung dan simpan NCF ke tabel ncf_results
    simpan_ncf_results($pdo, (int)$fieldId);

    $pdo->commit();

    header('Location: ../proyek.php?success=1');
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());
}