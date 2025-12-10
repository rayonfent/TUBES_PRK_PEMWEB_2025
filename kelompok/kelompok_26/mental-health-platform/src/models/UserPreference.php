<?php
// src/models/UserPreference.php

class UserPreference {
    protected $db;
    
    // =================================================================
    // Konstanta untuk nilai ENUM di tabel 'user_preferences'
    // Disesuaikan dengan hasil survey (match_result.php)
    // =================================================================

    // communication_pref (S: Straightforward/Tegas, G: Gentle/Lembut, B: Balanced)
    const COMM_STRAIGHTFORWARD = 'S';
    const COMM_GENTLE = 'G';
    const COMM_BALANCED      = 'B';
    
    // approach_pref (O: Logical/Rasional, D: Emotional/Suportif, B: Balanced - berdasarkan Q4)
    // 'O' = Logical Thinker (Q4 = 1)
    // 'D' = Emotional Feeler (Q4 = 2)
    const APPROACH_LOGICAL   = 'O';
    const APPROACH_EMOTIONAL = 'D';
    const APPROACH_BALANCED  = 'B';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Mengambil preferensi pengguna berdasarkan ID user.
     * @param int $userId ID pengguna.
     * @return array|null Data preferensi atau null.
     */
    public function getPreferenceByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM user_preferences WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows === 1 ? $result->fetch_assoc() : null;
    }
    
    /**
     * Menyimpan atau memperbarui preferensi pengguna berdasarkan hasil survei (Q1-Q4).
     * Logika mapping diambil dari src/views/matching/match_result.php.
     * * @param int $userId
     * @param int $q1 Jawaban Q1 (1=Tegas, 2=Lembut)
     * @param int $q2 Jawaban Q2 (1=Blak-blakan, 2=Halus)
     * @param int $q3 Jawaban Q3 (1=Tegas/Arahkan, 2=Didengarkan/Perlahan)
     * @param int $q4 Jawaban Q4 (1=Logis/Rasional, 2=Hangat/Suportif)
     * @return array Status operasi.
     */
    public function savePreferenceFromSurvey($userId, $q1, $q2, $q3, $q4) {
        
        // 1. Derivasi Communication Preference (berdasarkan Q1, Q2, Q3)
        $direct_score = 0;
        if ($q1 == 1) $direct_score++;
        if ($q2 == 1) $direct_score++;
        if ($q3 == 1) $direct_score++;
        
        $gentle_score = 3 - $direct_score; // Total 3 pertanyaan
            
        if ($direct_score >= 2) {
            $comm_pref = self::COMM_STRAIGHTFORWARD; // 'S'
        } elseif ($gentle_score >= 2) {
            $comm_pref = self::COMM_GENTLE; // 'G'
        } else {
            $comm_pref = self::COMM_BALANCED; // 'B' (misalnya, skor 1-2 atau 2-1)
        }
        
        // 2. Derivasi Approach Preference (berdasarkan Q4)
        if ($q4 == 1) {
            $approach_pref = self::APPROACH_LOGICAL; // 'O'
        } elseif ($q4 == 2) {
            $approach_pref = self::APPROACH_EMOTIONAL; // 'D'
        } else {
            $approach_pref = self::APPROACH_BALANCED; // 'B' (fallback)
        }
        
        $existing = $this->getPreferenceByUserId($userId);
        
        if ($existing) {
            // UPDATE: Perbarui preferensi yang sudah ada
            $stmt = $this->db->prepare("UPDATE user_preferences SET communication_pref = ?, approach_pref = ?, created_at = NOW() WHERE user_id = ?");
            $stmt->bind_param("ssi", $comm_pref, $approach_pref, $userId);
            $msg = 'Preferensi berhasil diperbarui';
        } else {
            // INSERT: Simpan preferensi baru
            $stmt = $this->db->prepare("INSERT INTO user_preferences (user_id, communication_pref, approach_pref) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userId, $comm_pref, $approach_pref);
            $msg = 'Preferensi berhasil disimpan';
        }

        if ($stmt->execute()) {
            return ['success' => true, 'message' => $msg, 'prefs' => ['comm' => $comm_pref, 'approach' => $approach_pref]];
        } else {
            return ['success' => false, 'message' => 'Gagal menyimpan preferensi: ' . $stmt->error];
        }
    }
}