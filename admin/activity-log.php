<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'activity';
$conn = db();

$logs = $conn->query("SELECT * FROM admin_activity_log ORDER BY created_at DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Activity Log — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Activity Log</h1></div>
    <p class="muted" style="margin-bottom:18px;font-size:13px;">Shows the last 200 actions taken by admin users — logins, product/order/coupon changes, and settings updates.</p>

    <table>
      <tr><th>When</th><th>Admin</th><th>Action</th><th>Details</th></tr>
      <?php if (!$logs): ?><tr><td colspan="4" class="empty-state">No activity recorded yet.</td></tr><?php endif; ?>
      <?php foreach ($logs as $log): ?>
      <tr>
        <td><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></td>
        <td><?= htmlspecialchars($log['admin_username'] ?? '—') ?></td>
        <td><?= htmlspecialchars(str_replace('_', ' ', $log['action'])) ?></td>
        <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body>
</html>
