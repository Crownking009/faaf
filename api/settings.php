<?php
require_once __DIR__ . '/../config/db.php';
$conn = db();

// Only expose settings that are safe for anyone on the internet to read.
// admin_email is intentionally excluded — it's for internal notifications only.
$publicKeys = [
    'store_name', 'store_address', 'store_lat', 'store_lng',
    'whatsapp_number', 'delivery_rate_per_km', 'delivery_base_fee',
    'free_delivery_threshold', 'currency_symbol',
];

$res = $conn->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $res->fetch_assoc()) {
    if (in_array($row['setting_key'], $publicKeys, true)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

json_response(['settings' => $settings]);
