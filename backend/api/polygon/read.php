<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// koneksi DB
include_once(__DIR__ . '/../../config/database.php'); 

try {
    $db = new Database();
    $conn = $db->getConnection();

    // ambil id_project dari query string
    $id_project = isset($_GET['id_project']) ? intval($_GET['id_project']) : 0;

    if ($id_project > 0) {
        // Query untuk specific project
        $query = "SELECT id_polygon, id_project, nama_polygon, deskripsi, coordinate, created_at 
                  FROM polygon 
                  WHERE id_project = :id_project
                  ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id_project", $id_project, PDO::PARAM_INT);
    } else {
        // Query untuk semua polygon
        $query = "SELECT id_polygon, id_project, nama_polygon, deskripsi, coordinate, created_at 
                  FROM polygon 
                  ORDER BY created_at DESC";
        
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();

    $polygons = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $polygons,
        "total" => count($polygons)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
