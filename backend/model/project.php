<?php
class Project {
    private $conn;
    private $table_name = "project";
    
    public $id_project;
    public $id_user;
    public $nama_project;
    public $deskripsi;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // CREATE
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_user, nama_project, deskripsi, created_at, updated_at) 
                  VALUES (:id_user, :nama_project, :deskripsi, NOW(), NOW()) 
                  RETURNING id_project";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id_user", $this->id_user);
        $stmt->bindParam(":nama_project", $this->nama_project);
        $stmt->bindParam(":deskripsi", $this->deskripsi);

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            $this->id_project = $row['id_project'];
            return true;
        }
        return false;
    }

    // READ
    public function read($id_user = null) {
        $query = "SELECT p.*, u.username, u.nama as nama_user
                  FROM " . $this->table_name . " p
                  LEFT JOIN m_user u ON p.id_user = u.id_user";

        if ($id_user) {
            $query .= " WHERE p.id_user = :id_user";
        }

        $query .= " ORDER BY p.updated_at DESC";

        $stmt = $this->conn->prepare($query);

        if ($id_user) {
            $stmt->bindParam(":id_user", $id_user);
        }

        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        // Ambil detail project + user
        $query = "SELECT p.*, u.username, u.nama as nama_user
                FROM " . $this->table_name . " p
                LEFT JOIN m_user u ON p.id_user = u.id_user
                WHERE p.id_project = :id_project
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_project", $this->id_project);
        $stmt->execute();

        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            return null; // ga ketemu
        }

        // Ambil placemarks
        $queryPlacemark = "SELECT * FROM placemark WHERE id_project = :id_project";
        $stmtPlacemark = $this->conn->prepare($queryPlacemark);
        $stmtPlacemark->bindParam(":id_project", $this->id_project);
        $stmtPlacemark->execute();
        $placemarks = $stmtPlacemark->fetchAll(PDO::FETCH_ASSOC);

        // Ambil polygon (misal 1 project hanya punya 1 polygon)
        $queryPolygon = "SELECT * FROM polygon WHERE id_project = :id_project LIMIT 1";
        $stmtPolygon = $this->conn->prepare($queryPolygon);
        $stmtPolygon->bindParam(":id_project", $this->id_project);
        $stmtPolygon->execute();
        $polygon = $stmtPolygon->fetch(PDO::FETCH_ASSOC);

        // Gabungkan
        $project['placemarks'] = $placemarks;
        $project['polygon'] = $polygon;

        return $project;
    }

    // UPDATE
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nama_project = :nama_project,
                      deskripsi = :deskripsi,
                      updated_at = NOW()
                  WHERE id_project = :id_project";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nama_project", $this->nama_project);
        $stmt->bindParam(":deskripsi", $this->deskripsi);
        $stmt->bindParam(":id_project", $this->id_project);

        return $stmt->execute();
    }

    // DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id_project = :id_project";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_project", $this->id_project);

        return $stmt->execute();
    }
}
?>
