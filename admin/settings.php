<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'settings';
$conn = db();
$msg = '';
$pwMsg = '';
$pwError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $fields = ['store_name','store_address','store_lat','store_lng','whatsapp_number','delivery_rate_per_km','delivery_base_fee','free_delivery_threshold','currency_symbol','admin_email'];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $val = trim($_POST[$f]);
            $stmt = $conn->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?");
            $stmt->bind_param('ss', $val, $f);
            $stmt->execute();
        }
    }
    log_admin_activity('settings_updated', 'Store settings changed');
    $msg = 'Settings updated.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($new !== $confirm) {
        $pwError = 'New password and confirmation do not match.';
    } else {
        $result = admin_change_password((int)$_SESSION['admin_id'], $current, $new);
        if ($result['ok']) {
            log_admin_activity('password_changed', 'Admin changed their own password');
            $pwMsg = 'Password updated successfully.';
        } else {
            $pwError = $result['error'];
        }
    }
}

$res = $conn->query("SELECT setting_key, setting_value FROM settings");
$s = [];
while ($row = $res->fetch_assoc()) $s[$row['setting_key']] = $row['setting_value'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Settings — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Store Settings</h1></div>
    <?php if ($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="form-card">
      <form method="post" class="form-grid">
        <input type="hidden" name="save_settings" value="1">
        <div class="full"><label>Store Name</label><input type="text" name="store_name" value="<?= htmlspecialchars($s['store_name']) ?>"></div>
        <div class="full"><label>Store Address</label><input type="text" name="store_address" value="<?= htmlspecialchars($s['store_address']) ?>"></div>
        <div><label>Store Latitude</label><input type="text" name="store_lat" value="<?= htmlspecialchars($s['store_lat']) ?>"></div>
        <div><label>Store Longitude</label><input type="text" name="store_lng" value="<?= htmlspecialchars($s['store_lng']) ?>"></div>
        <div><label>WhatsApp Number (intl format, no +)</label><input type="text" name="whatsapp_number" value="<?= htmlspecialchars($s['whatsapp_number']) ?>"></div>
        <div><label>Admin Notification Email (optional)</label><input type="email" name="admin_email" value="<?= htmlspecialchars($s['admin_email'] ?? '') ?>" placeholder="you@example.com"></div>
        <div><label>Currency Symbol</label><input type="text" name="currency_symbol" value="<?= htmlspecialchars($s['currency_symbol']) ?>"></div>
        <div><label>Delivery Rate per KM (₦)</label><input type="number" step="0.01" name="delivery_rate_per_km" value="<?= htmlspecialchars($s['delivery_rate_per_km']) ?>"></div>
        <div><label>Delivery Base Fee (₦)</label><input type="number" step="0.01" name="delivery_base_fee" value="<?= htmlspecialchars($s['delivery_base_fee']) ?>"></div>
        <div class="full"><label>Free Delivery Threshold (₦, 0 = disabled)</label><input type="number" step="0.01" name="free_delivery_threshold" value="<?= htmlspecialchars($s['free_delivery_threshold']) ?>"></div>
        <p class="muted full" style="margin:-8px 0 14px;">Tip: get your exact store latitude/longitude free from <a href="https://www.latlong.net" target="_blank">latlong.net</a> by searching your address.</p>
        <div class="full"><button class="btn" type="submit">Save Settings</button></div>
      </form>
    </div>

    <h2 style="font-size:16px;margin:34px 0 12px;">Security</h2>
    <div class="form-card">
      <p class="muted" style="margin-bottom:16px;">Signed in as <b><?= htmlspecialchars($_SESSION['admin_username']) ?></b></p>
      <?php if ($pwMsg): ?><div class="alert-success"><?= htmlspecialchars($pwMsg) ?></div><?php endif; ?>
      <?php if ($pwError): ?><div class="alert-error"><?= htmlspecialchars($pwError) ?></div><?php endif; ?>
      <form method="post">
        <input type="hidden" name="change_password" value="1">
        <label>Current Password</label>
        <input type="password" name="current_password" required>
        <label>New Password</label>
        <input type="password" name="new_password" required minlength="6">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required minlength="6">
        <button class="btn" type="submit" style="margin-top:8px;">Update Password</button>
      </form>
      <p class="muted" style="margin-top:16px;font-size:12.5px;">Need to add another staff login? Go to <a href="/admin/users.php">Admin Users</a>.</p>
    </div>
  </div>
</div>
</body>
</html>
