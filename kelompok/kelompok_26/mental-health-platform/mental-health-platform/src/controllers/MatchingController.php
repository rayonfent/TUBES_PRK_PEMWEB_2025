<?php
// src/controllers/MatchingController.php
require_once __DIR__ . '/../models/Konselor.php';
require_once __DIR__ . '/../models/UserPreference.php';
require_once __DIR__ . '/../models/ChatSession.php';
require_once __DIR__ . '/../models/MatchingHistory.php';
require_once __DIR__ . '/../helpers/auth.php';

class MatchingController {
    private $kModel;
    private $prefModel;
    private $chatModel;
    private $matchModel;
    public function __construct($conn) {
        $this->kModel = new Konselor($conn);
        $this->prefModel = new UserPreference($conn);
        $this->chatModel = new ChatSession($conn);
        $this->matchModel = new MatchingHistory($conn);
        $this->conn = $conn;
    }

    public function showResult() {
        require_login();
        $user = current_user();
        $data = $this->prefModel->getByUser($user['user_id']);
        $comm = $data['pref']['communication_pref'] ?? 'B';
        $app = $data['pref']['approach_pref'] ?? 'B';
        $issues = $data['issues'] ?? [];

        // fetch all konselor with profile
        $konselors = $this->kModel->allWithProfile();

        // compute score
        $candidates = [];
        foreach ($konselors as $k) {
            $score = 0.0;
            if ($comm === $k['communication_style']) $score += 1.0;
            elseif ($comm === 'B' || $k['communication_style'] === 'B') $score += 0.5;
            if ($app === $k['approach_style']) $score += 1.0;
            elseif ($app === 'B' || $k['approach_style'] === 'B') $score += 0.5;
            // specialization check
            $specMatch = 0;
            foreach ($issues as $iss) {
                $stmt = $this->conn->prepare("SELECT 1 FROM konselor_specialization WHERE konselor_id = ? AND issue_id = ?");
                $stmt->bind_param("ii",$k['konselor_id'],$iss);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) { $specMatch = 1; break; }
            }
            $score += $specMatch;
            $final = $score + ($k['rating'] / 100.0);
            $candidates[] = ['konselor'=>$k,'score'=>$final];
        }
        usort($candidates, function($a,$b){ return $b['score'] <=> $a['score']; });

        // store top match if exists
        if (!empty($candidates)) {
            $top = $candidates[0];
            $this->matchModel->log($user['user_id'], $top['konselor']['konselor_id'], $top['score']);
        }

        include __DIR__ . '/../views/matching/match_result.php';
    }

    public function startChatWith($konselor_id) {
        require_login();
        $user = current_user();
        // create session
        $sid = $this->chatModel->createSession($user['user_id'], $konselor_id, 1);
        header('Location: ../index.php?p=chat_room&session_id=' . $sid);
        exit;
    }
}