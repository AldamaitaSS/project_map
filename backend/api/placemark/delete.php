<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    echo json_encode(['success' => false, 'message' => 'Method harus DELETE']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

// Load existing placemarks
$placemarksFile = __DIR__ . '/../../data/placemarks.json';
$placemarks = [];
if (file_exists($placemarksFile)) {
    $placemarks = json_decode(file_get_contents($placemarksFile), true) ?? [];
}

// Find and remove placemark
$found = false;
foreach ($placemarks as $key => $placemark) {
    if ($placemark['id'] === $id) {
        unset($placemarks[$key]);
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Placemark tidak ditemukan']);
    exit;
}

// Reindex array
$placemarks = array_values($placemarks);

// Save updated placemarks
if (file_put_contents($placemarksFile, json_encode($placemarks, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Placemark berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus placemark'
    ]);
}
?>