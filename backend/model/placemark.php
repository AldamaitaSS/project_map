<?php
// filepath: d:\laragon\www\project_map\backend\model\placemark.php
class Placemark {
    // Database connection and table name
    private $conn;
    private $table_name = "placemark";
    
    // Object properties
    public $id_placemark;
    public $id_project;
    public $nama_placemark;
    public $deskripsi;
    public $latitude;
    public $longitude;
    public $alamat;
    public $rt;
    public $rw;
    public $kelurahan;
    public $kecamatan;
    public $kota;
    public $provinsi;
    public $created_at;
    
    // Constructor with DB
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Read placemarks by id_project (PostgreSQL style)
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_project = $1 ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id_project]);
        
        return $stmt;
    }
    
    // Create placemark (PostgreSQL style) - FIXED
    public function create() {
        if (empty($this->nama_placemark)) {
            $this->nama_placemark = 'Placemark ' . date('Y-m-d H:i:s');
        }

        $query = "INSERT INTO " . $this->table_name . " 
          (id_project, nama_placemark, deskripsi, latitude, longitude, alamat, rt, rw, kelurahan, kecamatan, kota, provinsi) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
          RETURNING id_placemark";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->id_project = htmlspecialchars(strip_tags($this->id_project));
        $this->nama_placemark = htmlspecialchars(strip_tags($this->nama_placemark));
        $this->deskripsi = htmlspecialchars(strip_tags($this->deskripsi));
        $this->latitude = floatval($this->latitude);
        $this->longitude = floatval($this->longitude);
        $this->alamat = htmlspecialchars(strip_tags($this->alamat ?? ''));
        $this->rt = htmlspecialchars(strip_tags($this->rt ?? ''));
        $this->rw = htmlspecialchars(strip_tags($this->rw ?? ''));
        $this->kelurahan = htmlspecialchars(strip_tags($this->kelurahan ?? ''));
        $this->kecamatan = htmlspecialchars(strip_tags($this->kecamatan ?? ''));
        $this->kota = htmlspecialchars(strip_tags($this->kota ?? ''));
        $this->provinsi = htmlspecialchars(strip_tags($this->provinsi ?? ''));
        
        $result = $stmt->execute([
            $this->id_project,
            $this->nama_placemark,
            $this->deskripsi,
            $this->latitude,
            $this->longitude,
            $this->alamat,
            $this->rt,
            $this->rw,
            $this->kelurahan,
            $this->kecamatan,
            $this->kota,
            $this->provinsi
        ]);
        
        if ($result) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id_placemark = $row['id_placemark'];
            return true;
        }
        return false;
    }
    
    // Update placemark
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                SET nama_placemark = ?
                WHERE id_placemark = ?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([
            $this->nama_placemark,
            $this->id_placemark
        ]);
    }
    
    // Verify ownership (PostgreSQL style)
    public function verifyOwnership() {
        $query = "SELECT p.id_project, pr.id_user 
                  FROM " . $this->table_name . " p
                  JOIN project pr ON p.id_project = pr.id_project 
                  WHERE p.id_placemark = $1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id_placemark]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Delete placemark (PostgreSQL style)
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_placemark = $1";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id_placemark]);
    }
}
?>