<?php
class project {
    private $conn;
    private $table_name = "project";
    
    public $id_project;
    public $id_user;
    public $id_placemark;
    public $id_polygon;
    public $nama_project;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_user, nama_project) 
                  VALUES (:id_user, :nama_project) 
                  RETURNING id_project";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_user", $this->id_user);
        $stmt->bindParam(":nama_project", $this->nama_project);
        
        if($stmt->execute()) {
            $row = $stmt->fetch();
            $this->id_project = $row['id_project'];
            return true;
        }
        return false;
    }
    
    public function read($id_user = null) {
        $query = "SELECT p.*, u.username, u.nama as nama_user
                FROM " . $this->table_name . " p
                LEFT JOIN m_user u ON p.id_user = u.id_user";
        
        if($id_user) {
            $query .= " WHERE p.id_user = :id_user";
        }
        
        $query .= " ORDER BY p.id_project DESC";
        
        $stmt = $this->conn->prepare($query);
        
        if($id_user) {
            $stmt->bindParam(":id_user", $id_user);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nama_project = :nama_project,
                      id_placemark = :id_placemark,
                      id_polygon = :id_polygon
                  WHERE id_project = :id_project";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nama_project", $this->nama_project);
        $stmt->bindParam(":id_placemark", $this->id_placemark);
        $stmt->bindParam(":id_polygon", $this->id_polygon);
        $stmt->bindParam(":id_project", $this->id_project);
        
        return $stmt->execute();
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_project = :id_project";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_project", $this->id_project);
        return $stmt->execute();
    }
}
?>