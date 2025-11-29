<!-- src/views/dashboard/user_dashboard.php -->
<?php
require_once __DIR__ . '/../../helpers/auth.php';
require_login();
$user = current_user();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Dashboard</title></head>
<body>
<h2>Dashboard User</h2>
<p>Welcome, <?=htmlspecialchars($user['name'])?></p>
<ul>
    <li><a href="index.php?p=survey">Take Survey</a></li>
    <li><a href="index.php?p=match_result">Find Counselor</a></li>
    <li><a href="controllers/handle_auth.php?action=logout">Logout</a></li>
</ul>
</body>
</html>