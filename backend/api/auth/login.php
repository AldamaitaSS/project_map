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

if(!empty($data->username) && !empty($data->password)) {
    if($user->login($data->username, $data->password)) {
        $user_data = array(
            "id_user" => $user->id_user,
            "nama" => $user->nama,
            "username" => $user->username,
            "email" => $user->email
        );
        Response::success($user_data, "Login berhasil");
    } else {
        Response::error("Username atau password salah", 401);
    }
} else {
    Response::error("Data tidak lengkap");
}
?>