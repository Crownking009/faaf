<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'customers';
$conn = db();

$q = trim($_GET['q'] ?? '');
$where = '';
$params = [];
$types = '';
if ($q !== '') {
    $where = 'WHERE customer_name LIKE ? OR customer_phone LIKE ?';
    $like = "%$q%";
    $params = [$like, $like];
    $types = 'ss';
}

$sql = "SELECT customer_name, customer_phone,
               COUNT(*) AS order_count,
               SUM(total) AS total_spent,
               MAX(created_at) AS last_order_at
        FROM orders
        $where
        GROUP BY customer_phone, customer_name
        ORDER BY total_spent DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$currency = get_setting('currency_symbol', '₦');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Customers — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Customers <span style="font-size:14px;color:var(--muted);font-weight:500;">(<?= count($customers) ?>)</span></h1></div>

    <form method="get" class="filters-inline">
      <input type="text" name="q" placeholder="Search by name or phone…" value="<?= htmlspecialchars($q) ?>">
      <button class="btn btn-outline btn-sm" type="submit">Search</button>
      <?php if ($q): ?><a class="btn btn-outline btn-sm" href="/admin/customers.php">Clear</a><?php endif; ?>
    </form>

    <table>
      <tr><th>Name</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Last Order</th><th></th></tr>
      <?php if (!$customers): ?><tr><td colspan="6" class="empty-state">No customers yet — orders will appear here once placed.</td></tr><?php endif; ?>
      <?php foreach ($customers as $c): ?>
      <tr>
        <td><?= htmlspecialchars($c['customer_name']) ?></td>
        <td><?= htmlspecialchars($c['customer_phone']) ?></td>
        <td><?= $c['order_count'] ?></td>
        <td><?= $currency ?><?= number_format($c['total_spent']) ?></td>
        <td><?= date('d M Y', strtotime($c['last_order_at'])) ?></td>
        <td><a class="icon-link" href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $c['customer_phone']) ?>" target="_blank">WhatsApp</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body>
</html>
