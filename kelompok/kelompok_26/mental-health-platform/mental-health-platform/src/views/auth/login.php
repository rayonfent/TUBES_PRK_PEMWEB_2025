<!-- src/views/auth/login.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Login</title></head>
<body>
<h2>Login</h2>
<?php if(isset($_SESSION['error'])){ echo '<p style="color:red">'.htmlspecialchars($_SESSION['error']).'</p>'; unset($_SESSION['error']); } ?>
<form action="controllers/handle_auth.php?action=login" method="post">
    <label>Email</label><br>
    <input type="email" name="email" required><br>
    <label>Password</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>
<p>No account? <a href="index.php?p=register">Register</a></p>
</body>
</html>