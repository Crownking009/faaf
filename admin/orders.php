<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'orders';
$conn = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $orderRef = $conn->query("SELECT order_ref FROM orders WHERE id=$oid")->fetch_assoc()['order_ref'] ?? "#$oid";
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $oid);
    $stmt->execute();
    log_admin_activity('order_status_changed', "Order {$orderRef} set to {$status}");
    header('Location: /admin/orders.php?view=' . $oid);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_item_qty'])) {
    $itemId = (int)$_POST['item_id'];
    $newQty = max(1, (int)$_POST['new_qty']);
    $item = $conn->query("SELECT * FROM order_items WHERE id = {$itemId}")->fetch_assoc();
    if ($item) {
        $deltaQty = $newQty - (int)$item['quantity'];
        if ($deltaQty !== 0) {
            adjust_stock($item['product_id'], $item['size'], $item['color'], -$deltaQty);
        }
        $newLineTotal = $newQty * (float)$item['unit_price'];
        $upd = $conn->prepare("UPDATE order_items SET quantity = ?, line_total = ? WHERE id = ?");
        $upd->bind_param('idi', $newQty, $newLineTotal, $itemId);
        $upd->execute();
        recalc_order_totals((int)$item['order_id']);
        log_admin_activity('order_item_updated', "Order #{$item['order_id']}: {$item['product_name']} qty set to {$newQty}");
    }
    header('Location: /admin/orders.php?view=' . ($item['order_id'] ?? 0));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $itemId = (int)$_POST['item_id'];
    $item = $conn->query("SELECT * FROM order_items WHERE id = {$itemId}")->fetch_assoc();
    if ($item) {
        adjust_stock($item['product_id'], $item['size'], $item['color'], (int)$item['quantity']);
        $conn->query("DELETE FROM order_items WHERE id = {$itemId}");
        recalc_order_totals((int)$item['order_id']);
        log_admin_activity('order_item_removed', "Order #{$item['order_id']}: removed {$item['product_name']}");
    }
    header('Location: /admin/orders.php?view=' . ($item['order_id'] ?? 0));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $oid = (int)$_POST['order_id'];
    $productId = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['quantity']);
    $product = $conn->query("SELECT name, price FROM products WHERE id = {$productId}")->fetch_assoc();
    if ($product) {
        $lineTotal = $qty * (float)$product['price'];
        $ins = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param('iisidd', $oid, $productId, $product['name'], $qty, $product['price'], $lineTotal);
        $ins->execute();
        adjust_stock($productId, null, null, -$qty);
        recalc_order_totals($oid);
        log_admin_activity('order_item_added', "Order #{$oid}: added {$product['name']} x{$qty}");
    }
    header('Location: /admin/orders.php?view=' . $oid);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fees'])) {
    $oid = (int)$_POST['order_id'];
    $deliveryFee = (float)$_POST['delivery_fee'];
    $discountAmount = (float)$_POST['discount_amount'];
    $notes = trim($_POST['notes'] ?? '');
    $stmt = $conn->prepare("UPDATE orders SET delivery_fee = ?, discount_amount = ?, notes = ? WHERE id = ?");
    $stmt->bind_param('ddsi', $deliveryFee, $discountAmount, $notes, $oid);
    $stmt->execute();
    recalc_order_totals($oid);
    log_admin_activity('order_fees_updated', "Order #{$oid}: fees/notes updated");
    header('Location: /admin/orders.php?view=' . $oid);
    exit;
}

if (isset($_GET['view'])) {
    $oid = (int)$_GET['view'];
    $conn->query("UPDATE orders SET seen_by_admin = 1 WHERE id = $oid");
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->bind_param('i', $oid);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    if (!$order) { header('Location: /admin/orders.php'); exit; }
    $itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id=?");
    $itemStmt->bind_param('i', $oid);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $allProducts = $conn->query("SELECT id, name, price FROM products WHERE status='active' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Order <?= htmlspecialchars($order['order_ref']) ?> — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Order <?= htmlspecialchars($order['order_ref']) ?></h1>
      <div style="display:flex;gap:10px;">
        <a class="btn btn-outline" href="/admin/invoice.php?id=<?= $order['id'] ?>" target="_blank">🖨 Print Invoice</a>
        <a class="btn btn-outline" href="/admin/orders.php">← All Orders</a>
      </div>
    </div>

    <div class="form-card" style="max-width:100%;margin-bottom:20px;">
      <div class="form-grid">
        <div><label>Customer</label><p><?= htmlspecialchars($order['customer_name']) ?> · <?= htmlspecialchars($order['customer_phone']) ?></p></div>
        <div><label>Fulfillment</label><p><?= ucfirst($order['fulfillment_type']) ?><?= $order['fulfillment_type']=='delivery' ? ' — '.htmlspecialchars($order['delivery_address']).' ('.$order['delivery_distance_km'].' km)' : '' ?></p></div>
        <div><label>Subtotal</label><p>₦<?= number_format($order['subtotal']) ?></p></div>
        <div><label>Total</label><p style="font-weight:800;">₦<?= number_format($order['total']) ?></p></div>
        <div><label>Placed</label><p><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p></div>
      </div>

      <table style="margin-top:10px;">
        <tr><th>Item</th><th>Size/Color</th><th>Qty</th><th>Unit</th><th>Total</th><th></th></tr>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><?= htmlspecialchars($it['product_name']) ?></td>
          <td><?= htmlspecialchars(trim(($it['size']??'').' '.($it['color']??''))) ?: '—' ?></td>
          <td>
            <form method="post" style="display:flex;gap:6px;align-items:center;">
              <input type="hidden" name="update_item_qty" value="1">
              <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
              <input type="number" name="new_qty" value="<?= $it['quantity'] ?>" min="1" style="width:60px;margin:0;padding:6px 8px;">
              <button class="icon-link" style="border:none;background:none;cursor:pointer;padding:0;" type="submit">Update</button>
            </form>
          </td>
          <td>₦<?= number_format($it['unit_price']) ?></td>
          <td>₦<?= number_format($it['line_total']) ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Remove this item and restock it?');">
              <input type="hidden" name="remove_item" value="1">
              <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
              <button class="icon-link" style="border:none;background:none;cursor:pointer;padding:0;color:#c0392b;" type="submit">Remove</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>

      <form method="post" style="margin-top:14px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="add_item" value="1">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <select name="product_id" required style="width:auto;margin:0;">
          <option value="">+ Add a product to this order…</option>
          <?php foreach ($allProducts as $p): ?>
          <option value="<?= $p['id'] ?>">&nbsp;<?= htmlspecialchars($p['name']) ?> — ₦<?= number_format($p['price']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="number" name="quantity" value="1" min="1" style="width:70px;margin:0;">
        <button class="btn btn-sm" type="submit">Add Item</button>
      </form>

      <h3 style="font-size:14px;margin:26px 0 12px;">Fees &amp; Notes</h3>
      <form method="post" class="form-grid">
        <input type="hidden" name="update_fees" value="1">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <div><label>Delivery Fee (₦)</label><input type="number" step="0.01" name="delivery_fee" value="<?= $order['delivery_fee'] ?>"></div>
        <div><label>Discount Amount (₦)<?= $order['coupon_code'] ? ' — ' . htmlspecialchars($order['coupon_code']) : '' ?></label><input type="number" step="0.01" name="discount_amount" value="<?= $order['discount_amount'] ?>"></div>
        <div class="full"><label>Notes</label><textarea name="notes" rows="2"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea></div>
        <div class="full"><button class="btn btn-sm" type="submit">Save Fees &amp; Notes</button></div>
      </form>

      <h3 style="font-size:14px;margin:26px 0 12px;">Status</h3>
      <form method="post" style="display:flex;gap:10px;align-items:center;">
        <input type="hidden" name="update_status" value="1">
        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
        <select name="status" style="width:auto;margin:0;">
          <?php foreach (['pending','confirmed','fulfilled','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= $order['status']==$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Update</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
    <?php
    exit;
}

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Orders — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Orders</h1><a class="btn btn-outline" href="/admin/export-orders.php">⬇ Export CSV</a></div>
    <div class="filters-inline">
      <input type="text" id="orderSearch" placeholder="Search by name, ref or phone…" onkeyup="filterOrders()">
      <select id="orderStatusFilter" onchange="filterOrders()">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="confirmed">Confirmed</option>
        <option value="fulfilled">Fulfilled</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <table id="ordersTable">
      <tr><th>Ref</th><th>Customer</th><th>Type</th><th>Total</th><th>Status</th><th>Date</th><th></th></tr>
      <?php if (!$orders): ?><tr><td colspan="7" class="empty-state">No orders yet.</td></tr><?php endif; ?>
      <?php foreach ($orders as $o): ?>
      <tr data-status="<?= $o['status'] ?>" data-search="<?= strtolower(htmlspecialchars($o['customer_name'] . ' ' . $o['order_ref'] . ' ' . $o['customer_phone'])) ?>">
        <td><?= htmlspecialchars($o['order_ref']) ?> <?= !$o['seen_by_admin'] ? '<span class="badge badge-pending" style="margin-left:4px;">NEW</span>' : '' ?></td>
        <td><?= htmlspecialchars($o['customer_name']) ?></td>
        <td><?= ucfirst($o['fulfillment_type']) ?></td>
        <td>₦<?= number_format($o['total']) ?></td>
        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
        <td><?= date('d M, H:i', strtotime($o['created_at'])) ?></td>
        <td><a class="icon-link" href="/admin/orders.php?view=<?= $o['id'] ?>">View</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <p id="noOrderResults" class="empty-state" style="display:none;">No orders match your search.</p>
  </div>
</div>
<script>
function filterOrders() {
  const q = document.getElementById('orderSearch').value.toLowerCase();
  const status = document.getElementById('orderStatusFilter').value;
  const rows = document.querySelectorAll('#ordersTable tr[data-search]');
  let visible = 0;
  rows.forEach(row => {
    const matchesSearch = row.dataset.search.includes(q);
    const matchesStatus = !status || row.dataset.status === status;
    const show = matchesSearch && matchesStatus;
    row.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('noOrderResults').style.display = (visible === 0 && rows.length > 0) ? 'block' : 'none';
}
</script>
</body>
</html>
