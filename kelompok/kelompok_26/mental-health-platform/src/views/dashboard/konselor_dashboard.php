<!-- src/views/dashboard/konselor_dashboard.php -->
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['konselor'])) { header('Location: index.php?p=login'); exit; }
$k = $_SESSION['konselor'];
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Konselor Dashboard</title></head>
<body>
<h2>Konselor Dashboard</h2>
<p>Hi <?=htmlspecialchars($k['name'])?></p>
<ul>
    <li><a href="controllers/handle_auth.php?action=logout">Logout</a></li>
</ul>
</body>
</html>