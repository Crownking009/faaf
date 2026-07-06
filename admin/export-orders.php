<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$conn = db();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=faaf-orders-' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Order Ref', 'Customer Name', 'Phone', 'Fulfillment', 'Delivery Address', 'Subtotal', 'Delivery Fee', 'Coupon Code', 'Discount', 'Total', 'Status', 'Date Placed']);

$orders = $conn->query("SELECT * FROM orders ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
foreach ($orders as $o) {
    fputcsv($output, [
        $o['order_ref'],
        $o['customer_name'],
        $o['customer_phone'],
        ucfirst($o['fulfillment_type']),
        $o['delivery_address'] ?? '',
        $o['subtotal'],
        $o['delivery_fee'],
        $o['coupon_code'] ?? '',
        $o['discount_amount'],
        $o['total'],
        ucfirst($o['status']),
        $o['created_at'],
    ]);
}

fclose($output);
exit;
