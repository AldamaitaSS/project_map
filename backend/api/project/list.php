<?php
// Include CORS first
include_once '../../util/cors.php';

// Set proper headers
header('Content-Type: application/json; charset=UTF-8');

// Include other files
include_once '../../config/database.php';
include_once '../../model/project.php';
include_once '../../util/response.php';

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::methodNotAllowed();
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        Response::error("Database connection failed", 500);
    }
    
    $project = new Project($db);
    
    // Get user_id from query parameter (optional)
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    
    $stmt = $project->read($user_id);
    $projects_arr = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $project_item = array(
            "id" => $row['id_project'],
            "id_project" => $row['id_project'],
            "name" => $row['nama_project'],
            "nama_project" => $row['nama_project'],
            "owner" => $row['nama_user'] ?? $row['username'] ?? 'Unknown',
            "owner_id" => $row['id_user'],
            "created_at" => $row['created_at'] ?? date('Y-m-d H:i:s'),
            "updated_at" => $row['updated_at'] ?? $row['created_at'] ?? date('Y-m-d H:i:s'),
            "lastModified" => $row['updated_at'] ?? $row['created_at'] ?? date('Y-m-d H:i:s'),
            "created" => $row['created_at'] ?? date('Y-m-d H:i:s')
        );
        
        array_push($projects_arr, $project_item);
    }
    
    Response::success($projects_arr, "Projects retrieved successfully");
    
} catch(Exception $e) {
    error_log("Error in project list: " . $e->getMessage());
    Response::error("Server error occurred: " . $e->getMessage(), 500);
}
?>