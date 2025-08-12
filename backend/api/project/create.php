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
if (!isset($input['name']) || empty($input['name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Nama project harus diisi'
    ]);
    exit;
}

// Load existing projects
$projectsFile = __DIR__ . '/../../data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true) ?? [];
}

// Add new project
$newProject = [
    'id' => count($projects) + 1,
    'name' => $input['name'],
    'description' => $input['description'] ?? '',
    'placemarks' => $input['placemarks'] ?? [],
    'polygons' => $input['polygons'] ?? [],
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

$projects[] = $newProject;

// Save projects
$dataDir = dirname($projectsFile);
if (!file_exists($dataDir)) {
    mkdir($dataDir, 0755, true);
}

if (file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Project berhasil dibuat',
        'data' => $newProject
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan project'
    ]);
}
?>