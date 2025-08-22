<?php
// model/user.php - Complete User Model

class user {
    private $conn;
    private $table_name = "m_user";

    // User properties
    public $id_user;
    public $nama;
    public $username;
    public $email;
    public $password;
    

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        try {
            // Hash password
            $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
            
            // PostgreSQL query dengan RETURNING clause
            $query = "INSERT INTO " . $this->table_name . " 
                     (nama, username, email, password, created_at) 
                     VALUES (:nama, :username, :email, :password, NOW()) 
                     RETURNING id_user";
            
            $stmt = $this->conn->prepare($query);
            
            // Clean data untuk mencegah XSS
            $this->nama = htmlspecialchars(strip_tags($this->nama));
            $this->username = htmlspecialchars(strip_tags($this->username));
            $this->email = htmlspecialchars(strip_tags($this->email));
            
            // Bind values
            $stmt->bindParam(":nama", $this->nama);
            $stmt->bindParam(":username", $this->username);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $hashed_password);
            
            if($stmt->execute()) {
                // Get the returned ID dari PostgreSQL
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result && isset($result['id_user'])) {
                    $this->id_user = $result['id_user'];
                    return true;
                }
            }
            
            return false;
            
        } catch(PDOException $e) {
            error_log("User create error: " . $e->getMessage());
            return false;
        }
    }

    // Login user
    public function login($username, $password) {
        try {
            // Query untuk mencari user berdasarkan username atau email
            $query = "SELECT id_user, nama, username, email, password 
                     FROM " . $this->table_name . " 
                     WHERE username = :username OR email = :username 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $username = htmlspecialchars(strip_tags($username));
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password dengan hash yang tersimpan
                if(password_verify($password, $row['password'])) {
                    // Set properties untuk response
                    $this->id_user = $row['id_user'];
                    $this->nama = $row['nama'];
                    $this->username = $row['username'];
                    $this->email = $row['email'];
                    return true;
                }
            }
            
            return false;
            
        } catch(PDOException $e) {
            error_log("User login error: " . $e->getMessage());
            return false;
        }
    }

    // Check if username or email already exists
    public function userExists($username, $email) {
        try {
            $query = "SELECT id_user FROM " . $this->table_name . " 
                     WHERE username = :username OR email = :email 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $username = htmlspecialchars(strip_tags($username));
            $email = htmlspecialchars(strip_tags($email));
            
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
            
        } catch(PDOException $e) {
            error_log("User exists check error: " . $e->getMessage());
            return false;
        }
    }

    // Get user by ID
    public function getById($id) {
        try {
            $query = "SELECT id_user, nama, username, email, created_at 
                     FROM " . $this->table_name . " 
                     WHERE id_user = :id 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->id_user = $row['id_user'];
                $this->nama = $row['nama'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->created_at = $row['created_at'];
                return true;
            }
            
            return false;
            
        } catch(PDOException $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return false;
        }
    }

    // Update user (optional method)
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . " 
                     SET nama = :nama, 
                         email = :email, 
                         updated_at = NOW()
                     WHERE id_user = :id";
            
            $stmt = $this->conn->prepare($query);
            
            // Clean data
            $this->nama = htmlspecialchars(strip_tags($this->nama));
            $this->email = htmlspecialchars(strip_tags($this->email));
            
            // Bind values
            $stmt->bindParam(":nama", $this->nama);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":id", $this->id_user);
            
            return $stmt->execute();
            
        } catch(PDOException $e) {
            error_log("User update error: " . $e->getMessage());
            return false;
        }
    }
}
?>