<?php
include_once '../../utils/cors.php';
include_once '../../config/database.php';
include_once '../../models/Placemark.php';
include_once '../../utils/response.php';

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::methodNotAllowed();
}

$database = new Database();
$db = $database->getConnection();
$placemark = new Placemark($db);

$stmt = $placemark->read();
$placemarks = array();

while ($row = $stmt->fetch()) {
    $placemarks[] = $row;
}

Response::success($placemarks, "Data placemark berhasil diambil");
?>