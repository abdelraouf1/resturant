<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (attempt_admin_login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Ember & Oak</title>
<link rel="stylesheet" href="../public/assets/css/style.css">
<link rel="stylesheet" href="assets/admin.css">
</head>
<body style="background:#1c1815; min-height:100vh; display:flex; align-items:center; justify-content:center;">

<div class="form-card" style="width:380px;">
  <h2 style="margin-bottom:24px; text-align:center;">Admin Login</h2>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" required autofocus>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
  </form>
</div>

</body>
</html>
