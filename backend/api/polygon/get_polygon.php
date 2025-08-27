<?php
// Fix path - dari api/polygon/ ke config/, model/, util/
include_once '../../config/database.php';
include_once '../../model/polygon.php';
include_once '../../util/cors.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    $polygon = new Polygon($db);

    // Get id_project from query parameter
    $id_project = isset($_GET['id_project']) ? intval($_GET['id_project']) : null;

    if ($id_project) {
        $polygon->id_project = $id_project;
        $stmt = $polygon->read();
        
        $polygons = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $coordinates = json_decode($row['coordinate'], true);
            
            $polygons[] = array(
                'id' => $row['id_polygon'],
                'id_polygon' => $row['id_polygon'],
                'id_project' => $row['id_project'],
                'nama_polygon' => $row['nama_polygon'],
                'name' => $row['nama_polygon'], // alias untuk frontend
                'deskripsi' => $row['deskripsi'],
                'coordinates' => $coordinates,
                'coordinate' => $row['coordinate'],
                'created_at' => $row['created_at']
            );
        }
        
        echo json_encode([
            'success' => true,
            'data' => $polygons,
            'message' => 'Polygons loaded for project ' . $id_project
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Project ID is required'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>