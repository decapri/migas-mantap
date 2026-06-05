<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/perhitungan.php';

$id = (int)($_POST['id'] ?? 0);

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | UPDATE FIELDS — hanya update field yang dikirim & tidak kosong
    |--------------------------------------------------------------------------
    */
    $fieldsMap = [
        'nama'          => $_POST['nama_proyek']      ?? null,
        'cadangan_mbbl' => $_POST['cadangan_mbbl']    ?? null,
        'harga_minyak'  => $_POST['harga_minyak']     ?? null,
        'tax_rate'      => $_POST['tax_rate']         ?? null,
        'tahun_hitung'  => $_POST['tahun_perhitungan'] ?? null,
        'status_proyek' => $_POST['status_proyek']    ?? null,
    ];

    $setClauses = [];
    $setValues  = [];
    foreach ($fieldsMap as $col => $val) {
        if ($val !== null && $val !== '') {
            $setClauses[] = "$col = ?";
            $setValues[]  = $val;
        }
    }
    if ($setClauses) {
        $setValues[] = $id;
        $pdo->prepare("UPDATE fields SET " . implode(', ', $setClauses) . " WHERE id = ?")
            ->execute($setValues);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE INVESTASI — hanya update yang terisi
    |--------------------------------------------------------------------------
    */
    // Hitung total_investasi: pakai input manual jika ada, fallback ke capital+non_capital
    $cap    = $_POST['capital']         ?? null;
    $nc     = $_POST['non_capital']     ?? null;
    $manual = $_POST['total_investasi'] ?? null;

    $totalInvestasi = null;
    if ($manual !== null && $manual !== '') {
        $totalInvestasi = $manual;
    } elseif ($cap !== null && $nc !== null && $cap !== '' && $nc !== '') {
        $totalInvestasi = (float)$cap + (float)$nc;
    } elseif ($cap !== null && $cap !== '') {
        // Ambil non_capital dari DB untuk dihitung ulang
        $existing = $pdo->prepare("SELECT non_capital FROM investasi WHERE field_id = ?");
        $existing->execute([$id]);
        $row = $existing->fetch();
        $totalInvestasi = (float)$cap + (float)($row['non_capital'] ?? 0);
    } elseif ($nc !== null && $nc !== '') {
        // Ambil capital dari DB untuk dihitung ulang
        $existing = $pdo->prepare("SELECT capital FROM investasi WHERE field_id = ?");
        $existing->execute([$id]);
        $row = $existing->fetch();
        $totalInvestasi = (float)($row['capital'] ?? 0) + (float)$nc;
    }

    $investMap = [
        'capital'         => $cap,
        'non_capital'     => $nc,
        'total_investasi' => $totalInvestasi !== null ? (string)$totalInvestasi : null,
    ];

    $setClauses = [];
    $setValues  = [];
    foreach ($investMap as $col => $val) {
        if ($val !== null && $val !== '') {
            $setClauses[] = "$col = ?";
            $setValues[]  = $val;
        }
    }
    if ($setClauses) {
        $setValues[] = $id;
        $pdo->prepare("UPDATE investasi SET " . implode(', ', $setClauses) . " WHERE field_id = ?")
            ->execute($setValues);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DECLINE — hanya update yang terisi
    |--------------------------------------------------------------------------
    */
    $declineMap = [
        'mulai_tahun_ke' => $_POST['mulai_decline'] ?? null,
        'laju_persen'    => $_POST['decline_rate']  ?? null,
    ];

    $setClauses = [];
    $setValues  = [];
    foreach ($declineMap as $col => $val) {
        if ($val !== null && $val !== '') {
            $setClauses[] = "$col = ?";
            $setValues[]  = $val;
        }
    }
    if ($setClauses) {
        $setValues[] = $id;
        $pdo->prepare("UPDATE production_decline SET " . implode(', ', $setClauses) . " WHERE field_id = ?")
            ->execute($setValues);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE OPEX — hanya update yang terisi
    |--------------------------------------------------------------------------
    */
    $opexMap = [
        'base_usd_m'      => $_POST['opex_base']     ?? null,
        'base_hingga_thn' => $_POST['opex_until']    ?? null,
        'eskalasi_persen' => $_POST['opex_eskalasi'] ?? null,
    ];

    $setClauses = [];
    $setValues  = [];
    foreach ($opexMap as $col => $val) {
        if ($val !== null && $val !== '') {
            $setClauses[] = "$col = ?";
            $setValues[]  = $val;
        }
    }
    if ($setClauses) {
        $setValues[] = $id;
        $pdo->prepare("UPDATE opex_params SET " . implode(', ', $setClauses) . " WHERE field_id = ?")
            ->execute($setValues);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE DATA PRODUKSI — hanya update jika ada input produksi yang diisi
    |--------------------------------------------------------------------------
    */
    $produksi = [
        1 => $_POST['produksi1'] ?? null,
        2 => $_POST['produksi2'] ?? null,
        3 => $_POST['produksi3'] ?? null,
        4 => $_POST['produksi4'] ?? null,
    ];

    $adaInputProduksi = false;
    foreach ($produksi as $val) {
        if ($val !== null && $val !== '') {
            $adaInputProduksi = true;
            break;
        }
    }

    if ($adaInputProduksi) {
        $pdo->prepare("DELETE FROM production_manual WHERE field_id = ?")
            ->execute([$id]);

        $stmtProd = $pdo->prepare("
            INSERT INTO production_manual (field_id, tahun_ke, produksi)
            VALUES (?, ?, ?)
        ");

        foreach ($produksi as $tahun => $value) {
            if ($value !== null && $value !== '') {
                $stmtProd->execute([$id, $tahun, $value]);
            }
        }
    }

    // Hitung ulang dan simpan NCF ke tabel ncf_results
    simpan_ncf_results($pdo, $id);

    $pdo->commit();

    header('Location: ../detail-proyek.php?id=' . $id . '&updated=1');
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());
}