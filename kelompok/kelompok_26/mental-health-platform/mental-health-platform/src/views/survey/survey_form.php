<!-- src/views/survey/survey_form.php -->
<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Survey</title></head>
<body>
<h2>Quick Survey</h2>
<p>Hi <?=htmlspecialchars($user['name'])?></p>
<form action="controllers/handle_survey.php" method="post">
    <h3>1) Saat mendapat nasihat, kamu lebih suka konselor yang:</h3>
    <label><input type="radio" name="communication_pref" value="S" required> Jujur & langsung (S)</label><br>
    <label><input type="radio" name="communication_pref" value="G"> Lembut & empatik (G)</label><br>

    <h3>2) Saat konsultasi, kamu ingin prosesnya:</h3>
    <label><input type="radio" name="approach_pref" value="O" required> Langsung ke inti (O)</label><br>
    <label><input type="radio" name="approach_pref" value="D"> Perlahan & bertahap (D)</label><br>

    <h3>3) Masalah utama (pilih minimal 1):</h3>
    <?php
    // $issues variable from controller
    if (!empty($issues)) {
        foreach ($issues as $iss) {
            echo '<label><input type="checkbox" name="issues[]" value="'.htmlspecialchars($iss['issue_id']).'"> '.htmlspecialchars($iss['name']).'</label><br>';
        }
    } else {
        echo "<p>No issues configured. Admin must add issues in DB.</p>";
    }
    ?>
    <br>
    <button type="submit">Submit Survey</button>
</form>
</body>
</html>