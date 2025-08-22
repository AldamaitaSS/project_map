<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get raw input
$rawInput = file_get_contents('php://input');
error_log("Raw input: " . $rawInput); // Debug log

$input = json_decode($rawInput, true);

// Debug log untuk melihat data yang diterima
error_log("Parsed input: " . print_r($input, true));

// Validasi input
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Validasi field yang diperlukan
if (!isset($input['nama_project']) || empty(trim($input['nama_project']))) {
    echo json_encode(['success' => false, 'message' => 'Project name is required']);
    exit();
}

// Validasi id_user - pastikan ada dan valid
if (!isset($input['id_user']) || empty($input['id_user'])) {
    error_log("Missing id_user in input: " . print_r($input, true));
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

// Sanitize input
$nama_project = trim($input['nama_project']);
$id_user = intval($input['id_user']); // Convert to integer

// Validasi panjang nama project
if (strlen($nama_project) < 3) {
    echo json_encode(['success' => false, 'message' => 'Project name must be at least 3 characters long']);
    exit();
}

if (strlen($nama_project) > 100) {
    echo json_encode(['success' => false, 'message' => 'Project name is too long (max 100 characters)']);
    exit();
}

try {
    // Database connection
    include_once '../../config/database.php';
    
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Database connection failed');
    }
    
    // Check if user exists
    $checkUserStmt = $db->prepare("SELECT id_user FROM m_user WHERE id_user = ?");
    $checkUserStmt->execute([$id_user]);
    
    if ($checkUserStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Check if project name already exists for this user
    $checkProjectStmt = $db->prepare("SELECT id_project FROM project WHERE nama_project = ? AND id_user = ?");
    $checkProjectStmt->execute([$nama_project, $id_user]);
    
    if ($checkProjectStmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Project name already exists']);
        exit();
    }
    
    // Insert new project
    $insertStmt = $db->prepare("
        INSERT INTO project (nama_project, id_user, created_at) 
        VALUES (?, ?, NOW()) 
        RETURNING id_project, nama_project, id_user, created_at
    ");
    
    $insertStmt->execute([$nama_project, $id_user]);
    $result = $insertStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Project berhasil dibuat',
            'data' => [
                'id_project' => (int)$result['id_project'],
                'nama_project' => $result['nama_project'],
                'id_user' => (int)$result['id_user'],
                'created_at' => $result['created_at']
            ]
        ]);
    } else {
        throw new Exception('Failed to create project');
    }
    
} catch (Exception $e) {
    error_log("Create project error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>