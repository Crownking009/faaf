<?php
/**
 * POST /api/orders.php
 * Body (JSON): {
 *   customer_name, customer_phone, fulfillment_type: 'pickup'|'delivery',
 *   delivery_address, delivery_lat, delivery_lng, delivery_distance_km, delivery_fee,
 *   coupon_code, discount_amount,
 *   items: [{ product_id, name, size, color, quantity, unit_price }],
 *   notes
 * }
 */
require_once __DIR__ . '/../config/db.php';
$conn = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['items']) || !is_array($input['items'])) {
    json_response(['error' => 'Your cart looks empty. Please add items before checking out.'], 400);
}

if (!check_rate_limit('create_order', 10, 3600)) {
    json_response(['error' => 'Too many orders placed from this connection recently. Please wait a bit and try again, or reach us directly on WhatsApp.'], 429);
}

$name = trim($input['customer_name'] ?? '');
$phone = trim($input['customer_phone'] ?? '');
$fulfillment = $input['fulfillment_type'] ?? 'pickup';

if ($name === '' || $phone === '') {
    json_response(['error' => 'Please provide your name and phone number.'], 400);
}
if (!in_array($fulfillment, ['pickup', 'delivery'])) {
    json_response(['error' => 'Invalid fulfillment type.'], 400);
}
if ($fulfillment === 'delivery' && empty($input['delivery_address'])) {
    json_response(['error' => 'Please provide a delivery address.'], 400);
}

$subtotal = 0;
foreach ($input['items'] as $item) {
    $subtotal += ((float)$item['unit_price']) * ((int)$item['quantity']);
}
$deliveryFee = $fulfillment === 'delivery' ? (float)($input['delivery_fee'] ?? 0) : 0;
$couponCode = !empty($input['coupon_code']) ? strtoupper(trim($input['coupon_code'])) : null;
$discountAmount = $couponCode ? max(0, (float)($input['discount_amount'] ?? 0)) : 0;
$total = max(0, $subtotal + $deliveryFee - $discountAmount);

$orderRef = 'FAAF-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO orders
        (order_ref, customer_name, customer_phone, fulfillment_type, delivery_address, delivery_lat, delivery_lng, delivery_distance_km, delivery_fee, coupon_code, discount_amount, subtotal, total, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $deliveryAddress = $input['delivery_address'] ?? null;
    $lat = isset($input['delivery_lat']) ? (float)$input['delivery_lat'] : null;
    $lng = isset($input['delivery_lng']) ? (float)$input['delivery_lng'] : null;
    $distanceKm = isset($input['delivery_distance_km']) ? (float)$input['delivery_distance_km'] : null;
    $notes = $input['notes'] ?? null;

    $stmt->bind_param(
        'sssssddddsddds',
        $orderRef, $name, $phone, $fulfillment, $deliveryAddress,
        $lat, $lng, $distanceKm, $deliveryFee, $couponCode, $discountAmount, $subtotal, $total, $notes
    );
    $stmt->execute();
    $orderId = $conn->insert_id;

    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, color, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $variantStmt = $conn->prepare("SELECT id FROM product_variants WHERE product_id = ? AND size <=> ? AND color <=> ?");
    $variantStockStmt = $conn->prepare("UPDATE product_variants SET stock = GREATEST(0, stock - ?) WHERE id = ?");
    $stockStmt = $conn->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
    $lowStockAlerts = [];

    foreach ($input['items'] as $item) {
        $productId = $item['product_id'] ?? null;
        $pname = $item['name'];
        $size = $item['size'] ?? null;
        $color = $item['color'] ?? null;
        $qty = (int)$item['quantity'];
        $unitPrice = (float)$item['unit_price'];
        $lineTotal = $qty * $unitPrice;
        $itemStmt->bind_param('iisssidd', $orderId, $productId, $pname, $size, $color, $qty, $unitPrice, $lineTotal);
        $itemStmt->execute();

        if ($productId) {
            $variantStmt->bind_param('iss', $productId, $size, $color);
            $variantStmt->execute();
            $variant = $variantStmt->get_result()->fetch_assoc();
            if ($variant) {
                $variantStockStmt->bind_param('ii', $qty, $variant['id']);
                $variantStockStmt->execute();
                $newStock = (int) $conn->query("SELECT stock FROM product_variants WHERE id = {$variant['id']}")->fetch_assoc()['stock'];
                if ($newStock <= 5) {
                    $variantLabel = trim(($size ?: '') . ' ' . ($color ?: ''));
                    $lowStockAlerts[] = "{$pname}" . ($variantLabel ? " ({$variantLabel})" : '') . ": {$newStock} left";
                }
            } else {
                $stockStmt->bind_param('ii', $qty, $productId);
                $stockStmt->execute();
                $newStock = (int) $conn->query("SELECT stock FROM products WHERE id = {$productId}")->fetch_assoc()['stock'];
                if ($newStock <= 5) {
                    $lowStockAlerts[] = "{$pname}: {$newStock} left";
                }
            }
        }
    }

    if ($couponCode) {
        $couponUpd = $conn->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE code = ?");
        $couponUpd->bind_param('s', $couponCode);
        $couponUpd->execute();
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    json_response(['error' => 'Could not save order. Please try again.'], 500);
}

// ---- Build WhatsApp message ----
$currency = get_setting('currency_symbol', '₦');
$waNumber = get_setting('whatsapp_number', '2349048239391');

$lines = [];
$lines[] = "Hello FAAF Collections & Souvenirs! 👋";
$lines[] = "I'd like to place an order — Ref: *{$orderRef}*";
$lines[] = "";
$lines[] = "*Items:*";
foreach ($input['items'] as $item) {
    $variant = [];
    if (!empty($item['size'])) $variant[] = "Size: {$item['size']}";
    if (!empty($item['color'])) $variant[] = "Color: {$item['color']}";
    $variantStr = $variant ? ' (' . implode(', ', $variant) . ')' : '';
    $lines[] = "• {$item['quantity']}x {$item['name']}{$variantStr} — {$currency}" . number_format($item['unit_price'] * $item['quantity']);
}
$lines[] = "";
$lines[] = "Subtotal: {$currency}" . number_format($subtotal);
if ($fulfillment === 'delivery') {
    $lines[] = "Delivery (" . ($distanceKm ?? '?') . " km est.): {$currency}" . number_format($deliveryFee);
} else {
    $lines[] = "Fulfillment: Store Pickup";
}
if ($discountAmount > 0) {
    $lines[] = "Coupon ({$couponCode}): -{$currency}" . number_format($discountAmount);
}
$lines[] = "*Total: {$currency}" . number_format($total) . "*";
$lines[] = "";
$lines[] = "*Name:* {$name}";
$lines[] = "*Phone:* {$phone}";
if ($fulfillment === 'delivery') {
    $lines[] = "*Delivery Address:* " . ($deliveryAddress ?? '');
}
if (!empty($notes)) {
    $lines[] = "*Notes:* {$notes}";
}

$message = implode("\n", $lines);
$waLink = "https://wa.me/{$waNumber}?text=" . rawurlencode($message);

// ---- Notify the store owner by email (best-effort, doesn't block the response) ----
$adminEmail = get_setting('admin_email', '');
if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    $storeName = get_setting('store_name', 'FAAF Collections & Souvenirs');
    $subject = "New order {$orderRef} — {$currency}" . number_format($total);
    $body = "A new order was just placed on {$storeName}.\n\n" . strip_tags(str_replace(['*', '•'], ['', '-'], $message)) .
            "\n\nView it in your admin panel: " . (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] . '/admin/orders.php' : '/admin/orders.php');
    if (!empty($lowStockAlerts)) {
        $body .= "\n\n⚠️ Low stock alert:\n" . implode("\n", $lowStockAlerts);
    }
    $headers = "From: no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'faafcollections.com') . "\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($adminEmail, $subject, $body, $headers);
}

json_response([
    'success' => true,
    'order_ref' => $orderRef,
    'total' => $total,
    'whatsapp_url' => $waLink,
]);
