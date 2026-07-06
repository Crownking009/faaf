<?php
require_once __DIR__ . '/../config/db.php';
$conn = db();

$action = $_GET['action'] ?? 'list';

if ($action === 'detail') {
    $slug = $conn->real_escape_string($_GET['slug'] ?? '');
    $stmt = $conn->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                             FROM products p JOIN categories c ON c.id = p.category_id
                             WHERE p.slug = ? AND p.status = 'active' LIMIT 1");
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) json_response(['error' => 'Product not found'], 404);

    $imgStmt = $conn->prepare("SELECT image_url, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $imgStmt->bind_param('i', $product['id']);
    $imgStmt->execute();
    $images = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $product['images'] = array_map(fn($i) => $i['image_url'], $images);
    $product['sizes'] = $product['sizes'] ? explode(',', $product['sizes']) : [];
    $product['colors'] = $product['colors'] ? explode(',', $product['colors']) : [];

    $variantStmt = $conn->prepare("SELECT size, color, stock FROM product_variants WHERE product_id = ?");
    $variantStmt->bind_param('i', $product['id']);
    $variantStmt->execute();
    $variants = $variantStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $product['variants'] = $variants;
    if ($variants) {
        $product['stock'] = array_sum(array_column($variants, 'stock'));
    }

    json_response(['product' => $product]);
}

// ----- list / filter -----
$where = ["p.status = 'active'"];
$params = [];
$types = '';

if (!empty($_GET['category'])) {
    $where[] = "c.slug = ?";
    $params[] = $_GET['category'];
    $types .= 's';
}
if (!empty($_GET['gender']) && in_array($_GET['gender'], ['male', 'female', 'unisex'])) {
    $where[] = "p.gender IN (?, 'unisex')";
    $params[] = $_GET['gender'];
    $types .= 's';
}
if (!empty($_GET['q'])) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $like = '%' . $_GET['q'] . '%';
    $params[] = $like; $params[] = $like;
    $types .= 'ss';
}
if (!empty($_GET['featured'])) {
    $where[] = "p.is_featured = 1";
}
if (!empty($_GET['min_price'])) {
    $where[] = "p.price >= ?";
    $params[] = (float)$_GET['min_price'];
    $types .= 'd';
}
if (!empty($_GET['max_price'])) {
    $where[] = "p.price <= ?";
    $params[] = (float)$_GET['max_price'];
    $types .= 'd';
}

$sort = "p.created_at DESC";
switch ($_GET['sort'] ?? '') {
    case 'price_asc': $sort = "p.price ASC"; break;
    case 'price_desc': $sort = "p.price DESC"; break;
    case 'name_asc': $sort = "p.name ASC"; break;
    case 'newest': $sort = "p.created_at DESC"; break;
}

$whereSql = implode(' AND ', $where);

// ---- pagination ----
$perPage = isset($_GET['per_page']) ? max(1, min(100, (int)$_GET['per_page'])) : 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// total count (uses the same filters, no limit/offset)
$countSql = "SELECT COUNT(*) AS total FROM products p JOIN categories c ON c.id = p.category_id WHERE $whereSql";
$countStmt = $conn->prepare($countSql);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$totalCount = (int)$countStmt->get_result()->fetch_assoc()['total'];

$sql = "SELECT p.id, p.name, p.slug, p.price, p.compare_price, p.gender, p.is_featured, p.is_new,
               COALESCE((SELECT SUM(stock) FROM product_variants WHERE product_id = p.id), p.stock) AS stock,
               c.name AS category_name, c.slug AS category_slug,
               (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) AS image
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE $whereSql
        ORDER BY $sort
        LIMIT ? OFFSET ?";

$listParams = $params;
$listTypes = $types . 'ii';
$listParams[] = $perPage;
$listParams[] = $offset;

$stmt = $conn->prepare($sql);
$stmt->bind_param($listTypes, ...$listParams);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

json_response([
    'products' => $products,
    'count' => count($products),
    'total' => $totalCount,
    'page' => $page,
    'per_page' => $perPage,
    'total_pages' => max(1, (int)ceil($totalCount / $perPage)),
]);
