<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'account';
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($new !== $confirm) {
        $error = 'New password and confirmation do not match.';
    } else {
        $result = admin_change_password((int)$_SESSION['admin_id'], $current, $new);
        if ($result['ok']) {
            $msg = 'Password updated successfully.';
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>My Account — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>My Account</h1></div>
    <?php if ($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-card">
      <p class="muted" style="margin-bottom:18px;">Signed in as <b><?= htmlspecialchars($_SESSION['admin_username']) ?></b></p>
      <form method="post">
        <label>Current Password</label>
        <input type="password" name="current_password" required>
        <label>New Password</label>
        <input type="password" name="new_password" required minlength="6">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required minlength="6">
        <button class="btn" type="submit" style="margin-top:8px;">Update Password</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
