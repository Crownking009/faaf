<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'coupons';
$conn = db();
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['discount_type'];
    $value = (float)$_POST['discount_value'];
    $minOrder = (float)($_POST['min_order_amount'] ?: 0);
    $usageLimit = $_POST['usage_limit'] !== '' ? (int)$_POST['usage_limit'] : null;
    $expiresAt = $_POST['expires_at'] !== '' ? $_POST['expires_at'] : null;

    if ($code === '' || $value <= 0) {
        $error = 'Please provide a code and a discount value greater than 0.';
    } else {
        $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, usage_limit, expires_at) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssddis', $code, $type, $value, $minOrder, $usageLimit, $expiresAt);
        // Note: usage_limit can be NULL; mysqli bind_param with 'i' + null value still binds NULL correctly.
        if (@$stmt->execute()) {
            log_admin_activity('coupon_created', "Created coupon: {$code}");
            $msg = 'Coupon created.';
        } else {
            $error = 'That coupon code may already exist. Try a different code.';
        }
    }
}
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $conn->query("UPDATE coupons SET active = 1 - active WHERE id = $id");
    header('Location: /admin/coupons.php?toggled=1');
    exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $code = $conn->query("SELECT code FROM coupons WHERE id=$id")->fetch_assoc()['code'] ?? "#$id";
    $conn->query("DELETE FROM coupons WHERE id = $id");
    log_admin_activity('coupon_deleted', "Deleted coupon: {$code}");
    header('Location: /admin/coupons.php?deleted=1');
    exit;
}

$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$currency = get_setting('currency_symbol', '₦');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Coupons — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Coupons</h1></div>
    <?php if ($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['toggled'])): ?><div class="alert-success">Coupon status updated.</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert-success">Coupon deleted.</div><?php endif; ?>

    <div class="form-card" style="margin-bottom:26px;max-width:100%;">
      <form method="post" class="form-grid">
        <input type="hidden" name="action" value="add">
        <div><label>Coupon Code</label><input type="text" name="code" required placeholder="e.g. WELCOME10" style="text-transform:uppercase;"></div>
        <div><label>Discount Type</label>
          <select name="discount_type">
            <option value="percent">Percentage (%)</option>
            <option value="fixed">Fixed Amount (<?= $currency ?>)</option>
          </select>
        </div>
        <div><label>Discount Value</label><input type="number" step="0.01" name="discount_value" required placeholder="e.g. 10"></div>
        <div><label>Minimum Order Amount (optional)</label><input type="number" step="0.01" name="min_order_amount" placeholder="0"></div>
        <div><label>Usage Limit (optional)</label><input type="number" name="usage_limit" placeholder="Unlimited"></div>
        <div><label>Expiry Date (optional)</label><input type="date" name="expires_at"></div>
        <div class="full"><button class="btn" type="submit">+ Create Coupon</button></div>
      </form>
    </div>

    <table>
      <tr><th>Code</th><th>Discount</th><th>Min Order</th><th>Usage</th><th>Expires</th><th>Status</th><th></th></tr>
      <?php if (!$coupons): ?><tr><td colspan="7" class="empty-state">No coupons yet.</td></tr><?php endif; ?>
      <?php foreach ($coupons as $c): ?>
      <tr>
        <td><b><?= htmlspecialchars($c['code']) ?></b></td>
        <td><?= $c['discount_type'] === 'percent' ? $c['discount_value'] . '%' : $currency . number_format($c['discount_value']) ?></td>
        <td><?= $c['min_order_amount'] > 0 ? $currency . number_format($c['min_order_amount']) : '—' ?></td>
        <td><?= $c['times_used'] ?><?= $c['usage_limit'] !== null ? ' / ' . $c['usage_limit'] : '' ?></td>
        <td><?= $c['expires_at'] ? date('d M Y', strtotime($c['expires_at'])) : 'Never' ?></td>
        <td><span class="badge badge-<?= $c['active'] ? 'active' : 'draft' ?>"><?= $c['active'] ? 'Active' : 'Disabled' ?></span></td>
        <td>
          <a class="icon-link" href="/admin/coupons.php?toggle=<?= $c['id'] ?>"><?= $c['active'] ? 'Disable' : 'Enable' ?></a>
          <a class="icon-link" style="color:#c0392b" href="/admin/coupons.php?delete=<?= $c['id'] ?>" onclick="return confirm('Delete this coupon?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body>
</html>
