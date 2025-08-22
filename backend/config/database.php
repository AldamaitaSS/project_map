<?php
// config/database.php - FIXED VERSION
class Database {
    protected $host = "localhost";
    protected $db_name = "db_map";
    protected $username = "postgres";
    protected $password = "root";
    protected $port = "5433";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // FIXED: Gunakan PostgreSQL connection string, bukan MySQL
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;

            $this->conn = new PDO($dsn, $this->username, $this->password);

            // Hapus ini kalau MySQL
            // $this->conn->exec("set names utf8");

            // Ganti dengan ini untuk PostgreSQL
            $this->conn->exec("SET client_encoding TO 'UTF8'");

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $this->conn;
            
        } catch(PDOException $exception) {
            // untuk debugging sementara
            die(json_encode([
                "status" => "error",
                "message" => "Database connection error: " . $exception->getMessage()
            ]));
        }
    }
    
    // Method untuk test connection
    public function testConnection() {
        $conn = $this->getConnection();
        if ($conn) {
            return true;
        }
        return false;
    }
}