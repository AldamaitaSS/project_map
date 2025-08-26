<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include_once __DIR__ . '/../../config/database.php';
include_once __DIR__ . '/../../model/project.php';
include_once __DIR__ . '/../../util/cors.php';

$database = new Database();
$db = $database->getConnection();

$project = new Project($db);

$project->id_project = isset($_GET['id_project']) ? $_GET['id_project'] : null;

if (!$project->id_project) {
    echo json_encode([
        "success" => false,
        "message" => "Parameter id_project diperlukan"
    ]);
    exit;
}

$result = $project->readOne();

if ($result) {
    echo json_encode([
        "success" => true,
        "data" => $result
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Project tidak ditemukan"
    ]);
}
?>
