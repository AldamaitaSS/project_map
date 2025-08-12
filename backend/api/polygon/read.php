<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$polygonsFile = __DIR__ . '/../../data/polygons.json';
$polygons = [];

if (file_exists($polygonsFile)) {
    $polygons = json_decode(file_get_contents($polygonsFile), true) ?? [];
}

echo json_encode([
    'success' => true,
    'data' => $polygons,
    'total' => count($polygons)
]);
?>