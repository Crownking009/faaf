<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$conn = db();

$oid = (int)($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param('i', $oid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
if (!$order) { die('Order not found.'); }

$itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->bind_param('i', $oid);
$itemStmt->execute();
$items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$storeName = get_setting('store_name', 'FAAF Collections & Souvenirs');
$storeAddress = get_setting('store_address', '');
$whatsapp = get_setting('whatsapp_number', '');
$currency = get_setting('currency_symbol', '₦');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice <?= htmlspecialchars($order['order_ref']) ?></title>
<style>
  body{font-family:'Segoe UI',Arial,sans-serif;color:#262217;max-width:700px;margin:40px auto;padding:0 20px;}
  .invoice-head{display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #d6a400;padding-bottom:20px;margin-bottom:24px;}
  .invoice-head h1{font-size:22px;margin:0 0 4px;}
  .invoice-head p{margin:2px 0;font-size:13px;color:#555;}
  .invoice-meta{text-align:right;}
  .invoice-meta b{font-size:16px;}
  table{width:100%;border-collapse:collapse;margin:20px 0;}
  th,td{padding:9px 10px;text-align:left;border-bottom:1px solid #e7ddc4;font-size:13.5px;}
  th{background:#f7f1e3;font-size:11px;text-transform:uppercase;letter-spacing:.04em;}
  .totals{width:280px;margin-left:auto;}
  .totals div{display:flex;justify-content:space-between;padding:5px 0;font-size:13.5px;}
  .totals .grand{font-weight:800;font-size:16px;border-top:2px solid #15130f;padding-top:8px;margin-top:6px;}
  .footer-note{margin-top:40px;font-size:12px;color:#888;text-align:center;}
  .print-btn{margin:20px 0;padding:10px 20px;background:#15130f;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:13px;}
  @media print{.print-btn{display:none;}}
</style>
</head>
<body>
  <button class="print-btn" onclick="window.print()">🖨 Print Invoice</button>

  <div class="invoice-head">
    <div>
      <h1><?= htmlspecialchars($storeName) ?></h1>
      <p><?= htmlspecialchars($storeAddress) ?></p>
      <?php if ($whatsapp): ?><p>WhatsApp: <?= htmlspecialchars($whatsapp) ?></p><?php endif; ?>
    </div>
    <div class="invoice-meta">
      <p><b>Invoice</b></p>
      <p><?= htmlspecialchars($order['order_ref']) ?></p>
      <p><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
    </div>
  </div>

  <p><b>Bill To:</b><br>
     <?= htmlspecialchars($order['customer_name']) ?><br>
     <?= htmlspecialchars($order['customer_phone']) ?><br>
     <?= $order['fulfillment_type'] === 'delivery' ? htmlspecialchars($order['delivery_address']) : 'Store Pickup — ' . htmlspecialchars($storeAddress) ?>
  </p>

  <table>
    <tr><th>Item</th><th>Size/Color</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
    <?php foreach ($items as $it): ?>
    <tr>
      <td><?= htmlspecialchars($it['product_name']) ?></td>
      <td><?= htmlspecialchars(trim(($it['size'] ?? '') . ' ' . ($it['color'] ?? ''))) ?: '—' ?></td>
      <td><?= $it['quantity'] ?></td>
      <td><?= $currency ?><?= number_format($it['unit_price']) ?></td>
      <td><?= $currency ?><?= number_format($it['line_total']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <div class="totals">
    <div><span>Subtotal</span><span><?= $currency ?><?= number_format($order['subtotal']) ?></span></div>
    <div><span>Delivery Fee</span><span><?= $currency ?><?= number_format($order['delivery_fee']) ?></span></div>
    <?php if ($order['discount_amount'] > 0): ?>
    <div><span>Coupon (<?= htmlspecialchars($order['coupon_code']) ?>)</span><span>−<?= $currency ?><?= number_format($order['discount_amount']) ?></span></div>
    <?php endif; ?>
    <div class="grand"><span>Total</span><span><?= $currency ?><?= number_format($order['total']) ?></span></div>
  </div>

  <?php if ($order['notes']): ?>
  <p><b>Notes:</b> <?= htmlspecialchars($order['notes']) ?></p>
  <?php endif; ?>

  <p class="footer-note">Thank you for shopping with <?= htmlspecialchars($storeName) ?>.</p>
</body>
</html>
