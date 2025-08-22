<?php
// backend/api/auth/register.php - FIXED VERSION FOR POSTGRESQL

// Include CORS first
include_once '../../util/cors.php';

// Then include other files
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
    $user = new User($db);

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
    $required_fields = ['nama', 'username', 'email', 'password'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            Response::error("Field '$field' is required");
        }
    }

    // Additional validation
    if (strlen($data['nama']) < 2) {
        Response::error("Nama minimal 2 karakter");
    }

    if (strlen($data['username']) < 3) {
        Response::error("Username minimal 3 karakter");
    }

    if (strlen($data['password']) < 6) {
        Response::error("Password minimal 6 karakter");
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        Response::error("Format email tidak valid");
    }

    // Check if username or email already exists
    if ($user->userExists($data['username'], $data['email'])) {
        Response::error("Username atau email sudah terdaftar");
    }

    // Set user data
    $user->nama = trim($data['nama']);
    $user->username = trim($data['username']);
    $user->email = trim($data['email']);
    $user->password = $data['password']; // Will be hashed in create() method
    
    // Create user
    if ($user->create()) {
        // Return success without sensitive data
        $response_data = [
            'id_user' => $user->id_user,
            'nama' => $user->nama,
            'username' => $user->username,
            'email' => $user->email
        ];
        
        Response::success($response_data, "User berhasil dibuat");
    } else {
        Response::error("Gagal membuat user. Silakan coba lagi.");
    }
    
} catch(PDOException $e) {
    error_log("Database error in register: " . $e->getMessage());
    Response::error("Database error occurred", 500);
} catch(Exception $e) {
    error_log("General error in register: " . $e->getMessage());
    Response::error("Server error: " . $e->getMessage(), 500);
}
?>