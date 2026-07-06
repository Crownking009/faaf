<?php
/**
 * POST /api/coupons.php
 * Body: { code: string, subtotal: number }
 * Returns discount details if the code is valid, or an error otherwise.
 */
require_once __DIR__ . '/../config/db.php';
$conn = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$code = strtoupper(trim($input['code'] ?? ''));
$subtotal = (float)($input['subtotal'] ?? 0);

if ($code === '') {
    json_response(['error' => 'Please enter a coupon code.'], 400);
}

$stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND active = 1");
$stmt->bind_param('s', $code);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();

if (!$coupon) {
    json_response(['error' => 'That coupon code is not valid.'], 404);
}
if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < strtotime('today')) {
    json_response(['error' => 'This coupon has expired.'], 400);
}
if ($coupon['usage_limit'] !== null && $coupon['times_used'] >= $coupon['usage_limit']) {
    json_response(['error' => 'This coupon has reached its usage limit.'], 400);
}
if ($subtotal < (float)$coupon['min_order_amount']) {
    $currency = get_setting('currency_symbol', '₦');
    json_response(['error' => "This coupon requires a minimum order of {$currency}" . number_format($coupon['min_order_amount']) . '.'], 400);
}

$discount = $coupon['discount_type'] === 'percent'
    ? round($subtotal * ((float)$coupon['discount_value'] / 100))
    : (float)$coupon['discount_value'];
$discount = min($discount, $subtotal);

json_response([
    'valid' => true,
    'code' => $coupon['code'],
    'discount_type' => $coupon['discount_type'],
    'discount_value' => (float)$coupon['discount_value'],
    'discount_amount' => (float)$discount,
]);
