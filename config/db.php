<?php
/**
 * FAAF Collections & Souvenirs — Database Connection
 * Edit the 4 values below with the credentials your hosting cPanel gives you
 * (cPanel → MySQL Databases). Typical shared-hosting DB names look like
 * "username_faaf" and usernames look like "username_faafadmin".
 */

// ====== EDIT THESE 4 LINES ======
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_cpanel_db_name');
define('DB_USER', 'your_cpanel_db_user');
define('DB_PASS', 'your_cpanel_db_password');
// =================================

define('UPLOADS_DIR', __DIR__ . '/../uploads/products/');
define('UPLOADS_URL', '/uploads/products/');

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed. Check config/db.php credentials.']);
            exit;
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_setting(string $key, $default = null) {
    $conn = db();
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

function get_client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            return trim($ip);
        }
    }
    return '0.0.0.0';
}

/**
 * Basic abuse throttle. Returns true if the request should be ALLOWED,
 * false if the caller has exceeded $maxAttempts within $windowSeconds.
 */
function check_rate_limit(string $action, int $maxAttempts, int $windowSeconds): bool {
    $conn = db();
    $ip = get_client_ip();

    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM rate_limits WHERE ip_address = ? AND action = ? AND created_at >= (NOW() - INTERVAL ? SECOND)");
    $stmt->bind_param('ssi', $ip, $action, $windowSeconds);
    $stmt->execute();
    $count = (int) $stmt->get_result()->fetch_assoc()['c'];

    if ($count >= $maxAttempts) {
        return false;
    }

    $ins = $conn->prepare("INSERT INTO rate_limits (ip_address, action) VALUES (?, ?)");
    $ins->bind_param('ss', $ip, $action);
    $ins->execute();

    // opportunistic cleanup of old rows so this table doesn't grow forever
    if (random_int(1, 50) === 1) {
        $conn->query("DELETE FROM rate_limits WHERE created_at < (NOW() - INTERVAL 1 DAY)");
    }

    return true;
}

function log_admin_activity(string $action, string $details = ''): void {
    if (empty($_SESSION['admin_id'])) return;
    $conn = db();
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, admin_username, action, details) VALUES (?, ?, ?, ?)");
    $adminId = (int) $_SESSION['admin_id'];
    $username = $_SESSION['admin_username'] ?? '';
    $stmt->bind_param('isss', $adminId, $username, $action, $details);
    $stmt->execute();
}

/**
 * Adjusts stock for a product, respecting per-variant tracking when it exists.
 * $delta positive = restock (add back), negative = consume further.
 */
function adjust_stock(?int $productId, ?string $size, ?string $color, int $delta): void {
    if (!$productId || $delta === 0) return;
    $conn = db();
    $variantStmt = $conn->prepare("SELECT id FROM product_variants WHERE product_id = ? AND size <=> ? AND color <=> ?");
    $variantStmt->bind_param('iss', $productId, $size, $color);
    $variantStmt->execute();
    $variant = $variantStmt->get_result()->fetch_assoc();
    $delta = (int) $delta;
    if ($variant) {
        $variantId = (int) $variant['id'];
        $conn->query("UPDATE product_variants SET stock = GREATEST(0, stock + ({$delta})) WHERE id = {$variantId}");
    } else {
        $conn->query("UPDATE products SET stock = GREATEST(0, stock + ({$delta})) WHERE id = {$productId}");
    }
}

/**
 * Recalculates an order's subtotal and total from its current line items,
 * preserving delivery fee and discount as they currently stand.
 */
function recalc_order_totals(int $orderId): void {
    $conn = db();
    $subtotal = (float) ($conn->query("SELECT COALESCE(SUM(line_total),0) s FROM order_items WHERE order_id = {$orderId}")->fetch_assoc()['s']);
    $order = $conn->query("SELECT delivery_fee, discount_amount FROM orders WHERE id = {$orderId}")->fetch_assoc();
    $total = max(0, $subtotal + (float)$order['delivery_fee'] - (float)$order['discount_amount']);
    $stmt = $conn->prepare("UPDATE orders SET subtotal = ?, total = ? WHERE id = ?");
    $stmt->bind_param('ddi', $subtotal, $total, $orderId);
    $stmt->execute();
}

// CORS — allow same-origin AJAX calls from the frontend
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
