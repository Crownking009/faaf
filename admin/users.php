<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'users';
$conn = db();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    if ($username === '' || strlen($password) < 6) {
        $error = 'Please provide a username and a password of at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        $stmt->bind_param('ss', $username, $hash);
        if (@$stmt->execute()) {
            log_admin_activity('admin_user_added', "Added admin user: {$username}");
            $msg = 'Admin user created.';
        } else {
            $error = 'That username is already taken.';
        }
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $totalAdmins = $conn->query("SELECT COUNT(*) c FROM admin_users")->fetch_assoc()['c'];
    if ($id === (int)$_SESSION['admin_id']) {
        $error = "You can't delete your own account while logged in.";
    } elseif ($totalAdmins <= 1) {
        $error = 'You must have at least one admin account.';
    } else {
        $target = $conn->query("SELECT username FROM admin_users WHERE id = $id")->fetch_assoc();
        $conn->query("DELETE FROM admin_users WHERE id = $id");
        log_admin_activity('admin_user_deleted', "Removed admin user: " . ($target['username'] ?? "#$id"));
        header('Location: /admin/users.php?deleted=1');
        exit;
    }
}

$admins = $conn->query("SELECT id, username, created_at FROM admin_users ORDER BY created_at ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Admin Users — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Admin Users</h1></div>
    <?php if ($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert-success">Admin user removed.</div><?php endif; ?>

    <div class="form-card" style="margin-bottom:26px;">
      <form method="post" class="form-grid">
        <input type="hidden" name="action" value="add">
        <div><label>Username</label><input type="text" name="username" required></div>
        <div><label>Password</label><input type="password" name="password" required minlength="6"></div>
        <div class="full"><button class="btn" type="submit">+ Add Admin User</button></div>
      </form>
    </div>

    <table>
      <tr><th>Username</th><th>Created</th><th></th></tr>
      <?php foreach ($admins as $a): ?>
      <tr>
        <td><?= htmlspecialchars($a['username']) ?> <?= $a['id'] === (int)$_SESSION['admin_id'] ? '<span class="badge badge-active">You</span>' : '' ?></td>
        <td><?= date('d M Y', strtotime($a['created_at'])) ?></td>
        <td>
          <?php if ($a['id'] !== (int)$_SESSION['admin_id']): ?>
          <a class="icon-link" style="color:#c0392b" href="/admin/users.php?delete=<?= $a['id'] ?>" onclick="return confirm('Remove this admin user?');">Remove</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <p class="muted" style="margin-top:16px;font-size:12.5px;">Every admin user has full access to the whole admin panel — there are no restricted roles yet. Only add people you trust with full control.</p>
  </div>
</div>
</body>
</html>
