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

$input = json_decode(file_get_contents('php://input'), true);

// Validasi input
$required = ['lat', 'lng', 'name'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Field $field harus diisi"
        ]);
        exit;
    }
}

// Load existing placemarks
$placemarksFile = __DIR__ . '/../../data/placemarks.json';
$placemarks = [];
if (file_exists($placemarksFile)) {
    $placemarks = json_decode(file_get_contents($placemarksFile), true) ?? [];
}

// Add new placemark
$newPlacemark = [
    'id' => count($placemarks) + 1,
    'lat' => floatval($input['lat']),
    'lng' => floatval($input['lng']),
    'name' => $input['name'],
    'description' => $input['description'] ?? '',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$placemarks[] = $newPlacemark;

// Save placemarks
$dataDir = dirname($placemarksFile);
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

if (file_put_contents($placemarksFile, json_encode($placemarks, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Placemark berhasil dibuat',
        'data' => $newPlacemark
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan placemark'
    ]);
}
?>