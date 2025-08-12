<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
echo json_encode([
    'status' => 'Server berjalan!',
    'waktu' => date('Y-m-d H:i:s'),
    'path' => __FILE__
]);
?>