<?php
include_once '../../utils/cors.php';
include_once '../../config/database.php';
include_once '../../models/Project.php';
include_once '../../models/Placemark.php';
include_once '../../models/Polygon.php';
include_once '../../utils/response.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed();
}

$database = new Database();
$db = $database->getConnection();

$project = new Project($db);
$placemark = new Placemark($db);
$polygon = new Polygon($db);

$data = json_decode(file_get_contents("php://input"));

try {
    $db->beginTransaction();
    
    // Simpan project
    $project->id_user = $data->id_user ?? 1; // Default user ID 1 jika tidak ada
    $project->nama_project = $data->nama_project ?? "Project " . date('Y-m-d H:i:s');
    
    if(!$project->create()) {
        throw new Exception("Gagal menyimpan project");
    }
    
    $placemark_id = null;
    $polygon_id = null;
    
    // Simpan placemarks jika ada
    if(!empty($data->placemarks)) {
        $placemarks_data = array();
        foreach($data->placemarks as $p) {
            $placemarks_data[] = array(
                'nama_placemark' => 'Marker ' . count($placemarks_data) + 1,
                'deskripsi' => 'Auto generated marker',
                'latitude' => $p->lat,
                'longitude' => $p->lng
            );
        }
        
        if(!$placemark->createMultiple($placemarks_data)) {
            throw new Exception("Gagal menyimpan placemarks");
        }
    }
    
    // Simpan polygon jika ada
    if(!empty($data->polygon)) {
        $polygon->nama_polygon = "Polygon " . date('Y-m-d H:i:s');
        $polygon->deskripsi = "Auto generated polygon";
        $polygon->coordinate = json_encode($data->polygon);
        
        if(!$polygon->create()) {
            throw new Exception("Gagal menyimpan polygon");
        }
        
        $polygon_id = $polygon->id_polygon;
    }
    
    // Update project dengan ID placemark dan polygon
    $project->id_placemark = $placemark_id;
    $project->id_polygon = $polygon_id;
    $project->update();
    
    $db->commit();
    Response::success(array("id_project" => $project->id_project), "Project berhasil disimpan");
    
} catch(Exception $e) {
    $db->rollback();
    Response::error("Error: " . $e->getMessage());
}
?>