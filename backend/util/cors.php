<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>

<?php
class Response {
    public static function success($data = null, $message = "Success") {
        if (ob_get_length()) ob_clean();
        
        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    public static function error($message = "Error", $code = 400) {
        if (ob_get_length()) ob_clean();
        
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
    
    public static function methodNotAllowed() {
        self::error('Method not allowed', 405);
    }
}
?>