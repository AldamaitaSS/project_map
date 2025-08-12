<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$placemarksFile = __DIR__ . '/../../data/placemarks.json';
$placemarks = [];

if (file_exists($placemarksFile)) {
    $placemarks = json_decode(file_get_contents($placemarksFile), true) ?? [];
}

echo json_encode([
    'success' => true,
    'data' => $placemarks,
    'total' => count($placemarks)
]);
?>