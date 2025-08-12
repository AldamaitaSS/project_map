<?php
class polygon {
    private $conn;
    private $table_name = "polygon";
    
    public $id_polygon;
    public $nama_polygon;
    public $deskripsi;
    public $coordinate;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nama_polygon, deskripsi, coordinate) 
                  VALUES (:nama_polygon, :deskripsi, :coordinate) 
                  RETURNING id_polygon";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nama_polygon", $this->nama_polygon);
        $stmt->bindParam(":deskripsi", $this->deskripsi);
        $stmt->bindParam(":coordinate", $this->coordinate);
        
        if($stmt->execute()) {
            $row = $stmt->fetch();
            $this->id_polygon = $row['id_polygon'];
            return true;
        }
        return false;
    }
    
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id_polygon DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nama_polygon = :nama_polygon,
                      deskripsi = :deskripsi,
                      coordinate = :coordinate
                  WHERE id_polygon = :id_polygon";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nama_polygon", $this->nama_polygon);
        $stmt->bindParam(":deskripsi", $this->deskripsi);
        $stmt->bindParam(":coordinate", $this->coordinate);
        $stmt->bindParam(":id_polygon", $this->id_polygon);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_polygon = :id_polygon";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_polygon", $this->id_polygon);
        return $stmt->execute();
    }
}
?>