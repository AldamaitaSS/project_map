<?php
include_once '../../config/database.php';
include_once '../../model/placemark.php';
include_once '../../util/cors.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $placemark = new Placemark($db);

    // Get POST data
    $data = json_decode(file_get_contents("php://input"));

    if (!$data || !isset($data->id_project) || !isset($data->latitude) || !isset($data->longitude)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: id_project, latitude, longitude']);
        exit;
    }

    $placemark->id_project = $data->id_project;
    $placemark->nama_placemark = !empty($data->nama_placemark) 
    ? $data->nama_placemark 
    : 'Placemark ' . date('Y-m-d H:i:s');
    $placemark->deskripsi = $data->deskripsi ?? 'Auto generated placemark';
    $placemark->latitude = $data->latitude;
    $placemark->longitude = $data->longitude;
    
    // NEW: Set address fields
    $placemark->alamat = $data->alamat ?? '';
    $placemark->rt = $data->rt ?? '';
    $placemark->rw = $data->rw ?? '';
    $placemark->kelurahan = $data->kelurahan ?? '';
    $placemark->kecamatan = $data->kecamatan ?? '';
    $placemark->kota = $data->kota ?? '';
    $placemark->provinsi = $data->provinsi ?? '';
    
    // Create placemark
    if ($placemark->create()) {
        echo json_encode([
            'success' => true,
            'message' => 'Placemark created successfully',
            'data' => [
                'id_placemark' => $placemark->id_placemark,
                'id_project' => $placemark->id_project,
                'nama_placemark' => $placemark->nama_placemark,
                'latitude' => $placemark->latitude,
                'longitude' => $placemark->longitude,
                'alamat' => $placemark->alamat,
                'rt' => $placemark->rt,
                'rw' => $placemark->rw,
                'kelurahan' => $placemark->kelurahan,
                'kecamatan' => $placemark->kecamatan,
                'kota' => $placemark->kota,
                'provinsi' => $placemark->provinsi
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create placemark']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>