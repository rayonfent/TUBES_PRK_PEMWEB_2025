<!-- src/views/auth/register.php -->
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Register</title></head>
<body>
<h2>Register</h2>
<?php if(isset($_SESSION['error'])){ echo '<p style="color:red">'.htmlspecialchars($_SESSION['error']).'</p>'; unset($_SESSION['error']); } ?>
<form action="controllers/handle_auth.php?action=register" method="post" enctype="multipart/form-data">
    <label>Name</label><br>
    <input type="text" name="name" required><br>
    <label>Email</label><br>
    <input type="email" name="email" required><br>
    <label>Password</label><br>
    <input type="password" name="password" required><br>
    <label>Confirm Password</label><br>
    <input type="password" name="password2" required><br>
    <label>Profile Picture (optional)</label><br>
    <input type="file" name="profile_picture" accept="image/*"><br><br>
    <button type="submit">Register</button>
</form>
<p>Already have account? <a href="index.php?p=login">Login</a></p>
</body>
</html>