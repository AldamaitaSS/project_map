<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH'])) {
    echo json_encode(['success' => false, 'message' => 'Method harus PUT atau PATCH']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID project tidak valid']);
    exit;
}

// Load existing projects
$projectsFile = __DIR__ . '/../../data/projects.json';
$projects = [];
if (file_exists($projectsFile)) {
    $projects = json_decode(file_get_contents($projectsFile), true) ?? [];
}

// Find and update project
$found = false;
$updatedProject = null;
foreach ($projects as &$project) {
    if ($project['id'] === $id) {
        // Update fields yang diberikan, keep yang lama jika tidak ada
        $project['name'] = $input['name'] ?? $project['name'];
        $project['description'] = $input['description'] ?? $project['description'];
        
        // Update placemarks jika diberikan
        if (isset($input['placemarks'])) {
            $project['placemarks'] = $input['placemarks'];
        }
        
        // Update polygon jika diberikan
        if (isset($input['polygon'])) {
            $project['polygon'] = $input['polygon'];
        }
        
        // Update polygons array jika diberikan (untuk multiple polygons)
        if (isset($input['polygons'])) {
            $project['polygons'] = $input['polygons'];
        }
        
        // Update timestamp
        $project['updated_at'] = date('Y-m-d H:i:s');
        
        $updatedProject = $project;
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode([
        'success' => false, 
        'message' => 'Project dengan ID ' . $id . ' tidak ditemukan'
    ]);
    exit;
}

// Save updated projects
if (file_put_contents($projectsFile, json_encode($projects, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Project "' . $updatedProject['name'] . '" berhasil diupdate',
        'data' => $updatedProject,
        'summary' => [
            'placemarks_count' => count($updatedProject['placemarks'] ?? []),
            'has_polygon' => isset($updatedProject['polygon']) && !empty($updatedProject['polygon']),
            'polygons_count' => count($updatedProject['polygons'] ?? []),
            'last_updated' => $updatedProject['updated_at']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal menyimpan perubahan project'
    ]);
}
?>