<?php
// src/models/Konselor.php
require_once __DIR__ . '/../config/database.php';

class Konselor {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function create($name, $email, $password, $bio=null, $profile_picture=null, $experience_years=0, $comm='B', $app='B') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO konselor (name,email,password,bio,profile_picture,experience_years) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("sssssi",$name,$email,$hash,$bio,$profile_picture,$experience_years);
        if (!$stmt->execute()) return false;
        $kid = $this->conn->insert_id;
        $stmt2 = $this->conn->prepare("INSERT INTO konselor_profile (konselor_id, communication_style, approach_style) VALUES (?,?,?)");
        $stmt2->bind_param("iss",$kid,$comm,$app);
        $stmt2->execute();
        return $kid;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM konselor WHERE email = ?");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM konselor WHERE konselor_id = ?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function setOnlineStatus($konselor_id, $status) {
        $s = $status ? 1 : 0;
        $stmt = $this->conn->prepare("UPDATE konselor SET online_status = ? WHERE konselor_id = ?");
        $stmt->bind_param("ii",$s,$konselor_id);
        return $stmt->execute();
    }

    // get all konselor with profile & score calculation performed elsewhere
    public function allWithProfile() {
        $sql = "SELECT k.*, kp.communication_style, kp.approach_style
                FROM konselor k
                LEFT JOIN konselor_profile kp ON kp.konselor_id = k.konselor_id";
        $res = $this->conn->query($sql);
        $out = [];
        while ($r = $res->fetch_assoc()) $out[] = $r;
        return $out;
    }
}