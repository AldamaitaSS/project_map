<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method harus POST']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_placemark   = intval($input['id_placemark'] ?? 0);
    $nama_placemark = trim($input['nama_placemark'] ?? '');

    if ($id_placemark <= 0 || $nama_placemark === '') {
        echo json_encode(['success' => false, 'message' => 'ID atau nama tidak valid']);
        exit;
    }

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../model/Placemark.php';

    $db = (new Database())->getConnection();
    $placemark = new Placemark($db);
    $placemark->id_placemark   = $id_placemark;
    $placemark->nama_placemark = $nama_placemark;

    if ($placemark->update()) {
        echo json_encode(['success' => true, 'message' => 'Nama placemark berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Placemark tidak ditemukan atau gagal update']);
    }
} catch (Throwable $e) {
    // biar selalu JSON, walaupun ada error fatal
    echo json_encode(['success' => false, 'message' => 'Server error: '.$e->getMessage()]);
}
