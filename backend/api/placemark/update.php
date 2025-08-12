<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(['success' => false, 'message' => 'Method harus PUT']);
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

// Find and update placemark
$found = false;
foreach ($placemarks as &$placemark) {
    if ($placemark['id'] === $id) {
        $placemark['lat'] = floatval($input['lat'] ?? $placemark['lat']);
        $placemark['lng'] = floatval($input['lng'] ?? $placemark['lng']);
        $placemark['name'] = $input['name'] ?? $placemark['name'];
        $placemark['description'] = $input['description'] ?? $placemark['description'];
        $placemark['updated_at'] = date('Y-m-d H:i:s');
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Placemark tidak ditemukan']);
    exit;
}

// Save updated placemarks
if (file_put_contents($placemarksFile, json_encode($placemarks, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Placemark berhasil diupdate'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan perubahan'
    ]);
}
?>