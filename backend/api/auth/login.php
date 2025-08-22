<?php
// backend/api/auth/login.php - IMPROVED VERSION

// Include CORS first
include_once '../../util/cors.php';
include_once '../../config/database.php';
include_once '../../model/user.php';
include_once '../../util/response.php';

// Only allow POST requests
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::methodNotAllowed();
}

try {
    // Initialize database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        Response::error("Database connection failed", 500);
    }
    
    // Initialize user model
    $user = new user($db);

    // Get JSON input
    $input = file_get_contents("php://input");
    if (empty($input)) {
        Response::error("No input data received");
    }
    
    $data = json_decode($input, true);
    
    if (!$data) {
        Response::error("Invalid JSON data: " . json_last_error_msg());
    }

    // Validate required fields
    if (empty($data['username']) || empty($data['password'])) {
        Response::error("Username dan password harus diisi");
    }

    // Additional validation
    if (strlen($data['username']) < 3) {
        Response::error("Username minimal 3 karakter");
    }

    // Debug: Log input username
    error_log("Login attempt for username: " . $data['username']);

    // Attempt login
    if ($user->login(trim($data['username']), $data['password'])) {
        // Debug: Log successful login with actual user ID
        error_log("Login successful - User ID: " . $user->id_user . ", Username: " . $user->username);
        
        // Login successful - return user data without password
        $user_data = [
            "id_user" => $user->id_user,
            "nama" => $user->nama,
            "username" => $user->username,
            "email" => $user->email
        ];
        
        // Debug: Log data yang akan dikirim
        error_log("Sending user data: " . json_encode($user_data));
        
        Response::success($user_data, "Login berhasil");
    } else {
        // Debug: Log failed login
        error_log("Login failed for username: " . $data['username']);
        
        // Login failed
        Response::error("Username atau password salah", 401);
    }
    
} catch(PDOException $e) {
    error_log("Database error in login: " . $e->getMessage());
    Response::error("Database error occurred", 500);
} catch(Exception $e) {
    error_log("General error in login: " . $e->getMessage());
    Response::error("Server error: " . $e->getMessage(), 500);
}
?>