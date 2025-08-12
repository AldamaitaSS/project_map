<?php
include_once '../../utils/cors.php';
include_once '../../config/database.php';
include_once '../../models/User.php';
include_once '../../utils/response.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->nama) && !empty($data->username) && !empty($data->email) && !empty($data->password)) {
    $user->nama = $data->nama;
    $user->username = $data->username;
    $user->email = $data->email;
    $user->password = $data->password;
    
    if($user->create()) {
        Response::success(null, "User berhasil dibuat");
    } else {
        Response::error("Gagal membuat user");
    }
} else {
    Response::error("Data tidak lengkap");
}
?>