<?php
// src/models/Issue.php
require_once __DIR__ . '/../config/database.php';

class Issue {
    private $conn;
    public function __construct($conn=null) { $this->conn = $conn ?? $GLOBALS['conn']; }

    public function all() {
        $res = $this->conn->query("SELECT * FROM issues ORDER BY name ASC");
        $out = [];
        while ($r = $res->fetch_assoc()) $out[] = $r;
        return $out;
    }
}