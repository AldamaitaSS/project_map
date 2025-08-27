<?php
// Fix path - dari api/placemark/ ke config/, model/, util/
include_once '../../config/database.php';
include_once '../../model/placemark.php';
include_once '../../util/cors.php';

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();
    $placemark = new Placemark($db);

    // Get id_project from query parameter
    $id_project = isset($_GET['id_project']) ? intval($_GET['id_project']) : null;

    if ($id_project) {
        $placemark->id_project = $id_project;
        $stmt = $placemark->read();
        
        $placemarks = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $placemarks[] = array(
                'id' => $row['id_placemark'],
                'id_placemark' => $row['id_placemark'],
                'id_project' => $row['id_project'],
                'lat' => floatval($row['latitude']),
                'lng' => floatval($row['longitude']),
                'latitude' => floatval($row['latitude']),
                'longitude' => floatval($row['longitude']),
                'nama_placemark' => $row['nama_placemark'],
                'deskripsi' => $row['deskripsi'],
                // NEW: Address fields
                'alamat' => $row['alamat'] ?? '',
                'rt' => $row['rt'] ?? '',
                'rw' => $row['rw'] ?? '',
                'kelurahan' => $row['kelurahan'] ?? '',
                'kecamatan' => $row['kecamatan'] ?? '',
                'kota' => $row['kota'] ?? '',
                'provinsi' => $row['provinsi'] ?? '',
                'created_at' => $row['created_at']
            );
        }
        
        echo json_encode([
            'success' => true,
            'data' => $placemarks,
            'message' => 'Placemarks loaded for project ' . $id_project
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