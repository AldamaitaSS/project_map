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
$id_project = intval($input['id_project'] ?? 0);

// Cek juga dari query parameter untuk fleksibilitas
if ($id_project <= 0) {
    $id_project = intval($_GET['id_project'] ?? 0);
}

if ($id_project <= 0) {
    echo json_encode(['success' => false, 'message' => 'id_project tidak valid']);
    exit;
}

// Load existing projects
$projectsFile = __DIR__ . '/../../data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true) ?? [];
}

// Find and remove project
$found = false;
$deletedProject = null;
foreach ($projects as $key => $project) {
    if ($project['id_project'] === $id_project) {
        $deletedProject = $project;
        unset($projects[$key]);
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode([
        'success' => false, 
        'message' => 'Project dengan id_project ' . $id_project . ' tid_projectak ditemukan'
    ]);
    exit;
}

// Reindex array
$projects = array_values($projects);

// Save updated projects
if (file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Project "' . $deletedProject['name'] . '" berhasil dihapus',
        'deleted_project' => [
            'id_project' => $deletedProject['id_project'],
            'name' => $deletedProject['name'],
            'created_at' => $deletedProject['created_at']
        ],
        'remaining_projects' => count($projects)
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menghapus project dari database'
    ]);
}
?>