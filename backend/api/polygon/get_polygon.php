<?php
include_once '../../utils/cors.php';
include_once '../../config/database.php';
include_once '../../models/Polygon.php';
include_once '../../utils/response.php';

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::methodNotAllowed();
}

$database = new Database();
$db = $database->getConnection();
$polygon = new Polygon($db);

$stmt = $polygon->read();
$polygons = array();

while ($row = $stmt->fetch()) {
    $row['coordinate'] = json_decode($row['coordinate']);
    $polygons[] = $row;
}

Response::success($polygons, "Data polygon berhasil diambil");
?>