<?php
class user {
    private $conn;
    private $table_name = "m_user";
    
    public $id_user;
    public $nama;
    public $username;
    public $email;
    public $password;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nama, username, email, password) 
                  VALUES (:nama, :username, :email, :password)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nama", $this->nama);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", password_hash($this->password, PASSWORD_DEFAULT));
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function login($username, $password) {
        $query = "SELECT id_user, nama, username, email, password 
                  FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if(password_verify($password, $row['password'])) {
                $this->id_user = $row['id_user'];
                $this->nama = $row['nama'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                return true;
            }
        }
        return false;
    }
    
    public function read() {
        $query = "SELECT id_user, nama, username, email FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>