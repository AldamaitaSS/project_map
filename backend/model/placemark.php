<?php
class placemark {
    private $conn;
    private $table_name = "placemark";
    
    public $id_placemark;
    public $nama_placemark;
    public $deskripsi;
    public $latitude;
    public $longitude;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nama_placemark, deskripsi, latitude, longitude) 
                  VALUES (:nama_placemark, :deskripsi, :latitude, :longitude) 
                  RETURNING id_placemark";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nama_placemark", $this->nama_placemark);
        $stmt->bindParam(":deskripsi", $this->deskripsi);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);
        
        if($stmt->execute()) {
            $row = $stmt->fetch();
            $this->id_placemark = $row['id_placemark'];
            return true;
        }
        return false;
    }
    
    public function createMultiple($placemarks) {
        $this->conn->beginTransaction();
        
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (nama_placemark, deskripsi, latitude, longitude) 
                      VALUES (:nama_placemark, :deskripsi, :latitude, :longitude)";
            
            $stmt = $this->conn->prepare($query);
            
            foreach($placemarks as $placemark) {
                $stmt->bindParam(":nama_placemark", $placemark['nama_placemark']);
                $stmt->bindParam(":deskripsi", $placemark['deskripsi']);
                $stmt->bindParam(":latitude", $placemark['latitude']);
                $stmt->bindParam(":longitude", $placemark['longitude']);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
    
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id_placemark DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_placemark = :id_placemark";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_placemark", $this->id_placemark);
        return $stmt->execute();
    }
}
?>