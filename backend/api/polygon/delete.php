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

// Load existing polygons
$polygonsFile = __DIR__ . '/../../data/polygons.json';
$polygons = [];
if (file_exists($polygonsFile)) {
    $polygons = json_decode(file_get_contents($polygonsFile), true) ?? [];
}

// Find and remove polygon
$found = false;
foreach ($polygons as $key => $polygon) {
    if ($polygon['id'] === $id) {
        unset($polygons[$key]);
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Polygon tidak ditemukan']);
    exit;
}

// Reindex array
$polygons = array_values($polygons);

// Save updated polygons
if (file_put_contents($polygonsFile, json_encode($polygons, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Polygon berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus polygon'
    ]);
}
?>