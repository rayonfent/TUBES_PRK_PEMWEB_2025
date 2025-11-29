<?php
// src/controllers/SurveyController.php
require_once __DIR__ . '/../models/UserPreference.php';
require_once __DIR__ . '/../models/Issue.php';
require_once __DIR__ . '/../helpers/auth.php';

class SurveyController {
    private $prefModel;
    private $issueModel;
    public function __construct($conn) {
        $this->prefModel = new UserPreference($conn);
        $this->issueModel = new Issue($conn);
    }

    public function renderSurveyForm() {
        require_login();
        $issues = $this->issueModel->all();
        include __DIR__ . '/../views/survey/survey_form.php';
    }

    public function handleSubmit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        require_login();
        $user = current_user();
        $comm = $_POST['communication_pref'] ?? 'B';
        $approach = $_POST['approach_pref'] ?? 'B';
        $issues = $_POST['issues'] ?? [];
        $this->prefModel->save($user['user_id'], $comm, $approach, $issues);
        header('Location: ../index.php?p=match_result'); exit;
    }
}