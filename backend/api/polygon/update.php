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

// Load existing polygons
$polygonsFile = __DIR__ . '/../../data/polygons.json';
$polygons = [];
if (file_exists($polygonsFile)) {
    $polygons = json_decode(file_get_contents($polygonsFile), true) ?? [];
}

// Find and update polygon
$found = false;
foreach ($polygons as &$polygon) {
    if ($polygon['id'] === $id) {
        $polygon['name'] = $input['name'] ?? $polygon['name'];
        $polygon['description'] = $input['description'] ?? $polygon['description'];
        $polygon['coordinates'] = $input['coordinates'] ?? $polygon['coordinates'];
        $polygon['color'] = $input['color'] ?? $polygon['color'];
        $polygon['updated_at'] = date('Y-m-d H:i:s');
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Polygon tidak ditemukan']);
    exit;
}

// Save updated polygons
if (file_put_contents($polygonsFile, json_encode($polygons, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Polygon berhasil diupdate'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan perubahan'
    ]);
}
?>