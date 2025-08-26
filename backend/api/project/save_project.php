<?php

error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}


$host = "localhost";
$db_name = "db_map";
$username = "postgres";
$password = "root";
$port = "5433";

function sendResponse($status, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);
    $response = array('status' => $status, 'message' => $message);
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit();
}


if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse('error', 'Method not allowed', null, 405);
}

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = file_get_contents("php://input");
    $data = json_decode($input);

    if (!$data) {
        sendResponse('error', 'Invalid JSON data', null, 400);
    }

    // ✅ Validasi id_project wajib ada
    if (!isset($data->id_project) || empty($data->id_project)) {
        sendResponse('error', 'ID Project is required', null, 400);
    }

    $id_project = $data->id_project;

    // ✅ Ambil id_user dari database
    $stmt = $pdo->prepare("SELECT id_user FROM project WHERE id_project = ?");
    $stmt->execute([$id_project]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        sendResponse('error', 'Project not found', null, 404);
    }

    $pdo->beginTransaction();

    $nama_project = isset($data->name) ? $data->name : "Project " . date('Y-m-d H:i:s');
    $deskripsi = isset($data->description) ? $data->description : "Auto generated project";

    // Simpan placemarks
    $placemark_ids = [];
    if (!empty($data->placemarks) && is_array($data->placemarks)) {
        foreach($data->placemarks as $placemark) {
            $stmt = $pdo->prepare("INSERT INTO placemark (id_project, nama_placemark, deskripsi, latitude, longitude) 
                                VALUES (?, ?, ?, ?, ?) RETURNING id_placemark");
            $stmt->execute([
                $id_project,
                "Marker " . date('Y-m-d H:i:s'),
                "Auto generated placemark",
                $placemark->lat,
                $placemark->lng
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $placemark_ids[] = $result['id_placemark'];
        }
    }

    // Simpan polygon
    $polygon_ids = [];
    if (!empty($data->polygon) && is_array($data->polygon)) {
        $stmt = $pdo->prepare("INSERT INTO polygon (id_project, nama_polygon, deskripsi, coordinate) 
                            VALUES (?, ?, ?, ?) RETURNING id_polygon");
        $stmt->execute([
            $id_project,
            "Polygon " . date('Y-m-d H:i:s'),
            "Auto generated polygon",
            json_encode($data->polygon)
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $polygon_ids[] = $result['id_polygon'];
    }

    // Update project (bukan insert baru)
    $first_placemark_id = !empty($placemark_ids) ? $placemark_ids[0] : null;

    $stmt = $pdo->prepare("UPDATE project 
                       SET nama_project = ?, deskripsi = ?, updated_at = NOW()
                       WHERE id_project = ? RETURNING id_project");
    $stmt->execute([$nama_project, $deskripsi, $id_project]);

    $pdo->commit();

    sendResponse('success', 'Project berhasil disimpan', array(
        "id_project" => $id_project,
        "placemarks_count" => count($placemark_ids),
        "polygons_count" => count($polygon_ids),
        "placemark_ids" => $placemark_ids,
        "polygon_ids" => $polygon_ids
    ));

} catch(PDOException $e) {
    if (isset($pdo)) $pdo->rollback();
    error_log("Database error in save_project: " . $e->getMessage());
    sendResponse('error', 'Database error: ' . $e->getMessage(), null, 500);
} catch(Exception $e) {
    if (isset($pdo)) $pdo->rollback();
    error_log("General error in save_project: " . $e->getMessage());
    sendResponse('error', 'Error: ' . $e->getMessage(), null, 500);
}
