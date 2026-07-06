<?php
require_once __DIR__ . '/../config/db.php';
$conn = db();

$res = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'active') AS product_count
                      FROM categories c ORDER BY sort_order ASC");
$categories = $res->fetch_all(MYSQLI_ASSOC);

json_response(['categories' => $categories]);
