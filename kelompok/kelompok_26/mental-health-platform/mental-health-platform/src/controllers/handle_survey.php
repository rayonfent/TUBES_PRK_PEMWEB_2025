<?php
// src/controllers/handle_survey.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/SurveyController.php';

$ctrl = new SurveyController($conn);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ctrl->handleSubmit();
} else {
    $ctrl->renderSurveyForm();
}