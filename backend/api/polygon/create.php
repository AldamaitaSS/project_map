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
if (!isset($input['coordinates']) || !is_array($input['coordinates']) || count($input['coordinates']) < 3) {
    echo json_encode([
        'success' => false,
        'message' => 'Minimal 3 koordinat diperlukan untuk polygon'
    ]);
    exit;
}

// Load existing polygons
$polygonsFile = __DIR__ . '/../../data/polygons.json';
$polygons = [];
if (file_exists($polygonsFile)) {
    $polygons = json_decode(file_get_contents($polygonsFile), true) ?? [];
}

// Add new polygon
$newPolygon = [
    'id' => count($polygons) + 1,
    'name' => $input['name'] ?? 'Polygon ' . (count($polygons) + 1),
    'description' => $input['description'] ?? '',
    'coordinates' => $input['coordinates'],
    'color' => $input['color'] ?? '#FF0000',
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$polygons[] = $newPolygon;

// Save polygons
$dataDir = dirname($polygonsFile);
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

if (file_put_contents($polygonsFile, json_encode($polygons, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Polygon berhasil dibuat',
        'data' => $newPolygon
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan polygon'
    ]);
}
?>