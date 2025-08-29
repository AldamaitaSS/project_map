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
    $id_polygon   = intval($input['id_polygon'] ?? 0);
    $nama_polygon = trim($input['nama_polygon'] ?? '');

    if ($id_polygon <= 0 || $nama_polygon === '') {
        echo json_encode(['success' => false, 'message' => 'ID atau nama tidak valid']);
        exit;
    }

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../model/polygon.php';

    $db = (new Database())->getConnection();
    $polygon = new polygon($db);
    $polygon->id_polygon   = $id_polygon;
    $polygon->nama_polygon = $nama_polygon;

    if ($polygon->update()) {
        echo json_encode(['success' => true, 'message' => 'Nama polygon berhasil diupdate']);
    } else {
        echo json_encode(['success' => false, 'message' => 'polygon tidak ditemukan atau gagal update']);
    }
} catch (Throwable $e) {
    // biar selalu JSON, walaupun ada error fatal
    echo json_encode(['success' => false, 'message' => 'Server error: '.$e->getMessage()]);
}
