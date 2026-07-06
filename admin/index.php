<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'dashboard';
$conn = db();

$productCount = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$activeCount = $conn->query("SELECT COUNT(*) c FROM products WHERE status='active'")->fetch_assoc()['c'];
$orderCount = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'];
$unseenCount = $conn->query("SELECT COUNT(*) c FROM orders WHERE seen_by_admin = 0")->fetch_assoc()['c'];
$revenue = $conn->query("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status != 'cancelled'")->fetch_assoc()['s'];
$weekRevenue = $conn->query("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status != 'cancelled' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['s'];
$lowStockCount = $conn->query("SELECT COUNT(*) c FROM products WHERE stock <= 5 AND status='active'")->fetch_assoc()['c'];

$recentOrders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$lowStockProducts = $conn->query("SELECT id, name, stock FROM products WHERE stock <= 5 AND status='active' ORDER BY stock ASC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$topCategories = $conn->query("SELECT c.name, COUNT(p.id) AS pcount FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY pcount DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Dashboard — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Dashboard <?= $unseenCount ? "<span style=\"font-size:13px;color:#c0392b;font-weight:600;\">· {$unseenCount} new order" . ($unseenCount==1?'':'s') . " to review</span>" : '' ?></h1><a class="btn" href="/index.html" target="_blank">View Storefront ↗</a></div>

    <div class="cards-row">
      <div class="stat-card"><div class="num"><?= $productCount ?></div><div class="label"><?= $activeCount ?> Active Products</div></div>
      <div class="stat-card" style="<?= $unseenCount ? 'border-color:#f0c9a0;background:#fff9ef;' : '' ?>">
        <div class="num" style="<?= $unseenCount ? 'color:#c0392b;' : '' ?>"><?= $orderCount ?></div>
        <div class="label"><?= $unseenCount ? "{$unseenCount} unseen" : 'Total Orders' ?></div>
      </div>
      <div class="stat-card"><div class="num"><?= $pendingCount ?></div><div class="label">Pending Orders</div></div>
      <div class="stat-card"><div class="num">₦<?= number_format($revenue) ?></div><div class="label">Revenue (All Time)</div></div>
      <div class="stat-card"><div class="num">₦<?= number_format($weekRevenue) ?></div><div class="label">Revenue (Last 7 Days)</div></div>
      <div class="stat-card" style="<?= $lowStockCount ? 'border-color:#f0c9a0;background:#fff9ef;' : '' ?>">
        <div class="num" style="<?= $lowStockCount ? 'color:#c0392b;' : '' ?>"><?= $lowStockCount ?></div>
        <div class="label">Low / Out of Stock</div>
      </div>
    </div>

    <div class="dash-columns" style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;margin-bottom:30px;">
      <div>
        <h2 style="font-size:16px;margin-bottom:12px;">Recent Orders</h2>
        <table>
          <tr><th>Ref</th><th>Customer</th><th>Type</th><th>Total</th><th>Status</th><th>Date</th></tr>
          <?php if (!$recentOrders): ?>
          <tr><td colspan="6" class="empty-state">No orders yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentOrders as $o): ?>
          <tr>
            <td><a class="icon-link" href="/admin/orders.php?view=<?= $o['id'] ?>"><?= htmlspecialchars($o['order_ref']) ?></a></td>
            <td><?= htmlspecialchars($o['customer_name']) ?></td>
            <td><?= ucfirst($o['fulfillment_type']) ?></td>
            <td>₦<?= number_format($o['total']) ?></td>
            <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
            <td><?= date('d M, H:i', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </div>

      <div>
        <h2 style="font-size:16px;margin-bottom:12px;">Needs Attention</h2>
        <?php if ($lowStockProducts): ?>
        <div class="form-card" style="padding:18px;margin-bottom:18px;max-width:100%;">
          <p style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:12px;">Low Stock</p>
          <?php foreach ($lowStockProducts as $lp): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px;">
            <a class="icon-link" style="margin:0;" href="/admin/product-form.php?id=<?= $lp['id'] ?>"><?= htmlspecialchars($lp['name']) ?></a>
            <span class="low-stock-tag"><?= $lp['stock'] ?> left</span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="form-card" style="padding:18px;margin-bottom:18px;max-width:100%;"><p class="muted" style="font-size:13px;">All products are well stocked. 🎉</p></div>
        <?php endif; ?>

        <h2 style="font-size:16px;margin-bottom:12px;">Products by Category</h2>
        <div class="form-card" style="padding:18px;max-width:100%;">
          <?php foreach ($topCategories as $tc): ?>
          <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px;">
            <span><?= htmlspecialchars($tc['name']) ?></span>
            <span style="font-weight:700;"><?= $tc['pcount'] ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
