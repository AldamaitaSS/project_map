<?php
include_once '../../utils/cors.php';
include_once '../../config/database.php';
include_once '../../models/Project.php';
include_once '../../utils/response.php';

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::methodNotAllowed();
}

$database = new Database();
$db = $database->getConnection();
$project = new Project($db);

$id_user = $_GET['id_user'] ?? null;

$stmt = $project->read($id_user);
$projects = array();

while ($row = $stmt->fetch()) {
    $projects[] = $row;
}

Response::success($projects, "Data project berhasil diambil");
?>