<?php
// src/models/UserPreference.php
require_once __DIR__ . '/../config/database.php';

class UserPreference {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function save($user_id, $communication_pref, $approach_pref, $issues = []) {
        // upsert preference
        $stmt = $this->conn->prepare("SELECT pref_id FROM user_preferences WHERE user_id = ?");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $stmt2 = $this->conn->prepare("UPDATE user_preferences SET communication_pref = ?, approach_pref = ? WHERE user_id = ?");
            $stmt2->bind_param("ssi",$communication_pref,$approach_pref,$user_id);
            $stmt2->execute();
        } else {
            $stmt2 = $this->conn->prepare("INSERT INTO user_preferences (user_id, communication_pref, approach_pref) VALUES (?,?,?)");
            $stmt2->bind_param("iss",$user_id,$communication_pref,$approach_pref);
            $stmt2->execute();
        }
        // manage issues
        $stmt3 = $this->conn->prepare("DELETE FROM user_issue WHERE user_id = ?");
        $stmt3->bind_param("i",$user_id);
        $stmt3->execute();
        if (!empty($issues)) {
            $stmt4 = $this->conn->prepare("INSERT INTO user_issue (user_id, issue_id) VALUES (?,?)");
            foreach ($issues as $iss) {
                $stmt4->bind_param("ii",$user_id,$iss);
                $stmt4->execute();
            }
        }
        return true;
    }

    public function getByUser($user_id) {
        $stmt = $this->conn->prepare("SELECT communication_pref, approach_pref, created_at FROM user_preferences WHERE user_id = ?");
        $stmt->bind_param("i",$user_id);
        $stmt->execute();
        $pref = $stmt->get_result()->fetch_assoc();
        $stmt2 = $this->conn->prepare("SELECT issue_id FROM user_issue WHERE user_id = ?");
        $stmt2->bind_param("i",$user_id);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $issues = [];
        while ($r = $res->fetch_assoc()) $issues[] = $r['issue_id'];
        return ['pref'=>$pref,'issues'=>$issues];
    }
}