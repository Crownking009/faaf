<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'products';
$conn = db();

function delete_product(mysqli $conn, int $id): ?string {
    $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) return null;

    $imgStmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $imgStmt->bind_param('i', $id);
    $imgStmt->execute();
    $imgs = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($imgs as $img) {
        if (strpos($img['image_url'], UPLOADS_URL) === 0) {
            $path = __DIR__ . '/../uploads/products/' . basename($img['image_url']);
            if (file_exists($path)) @unlink($path);
        }
    }

    $del = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del->bind_param('i', $id);
    $del->execute();
    return $product['name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['delete_one']) || isset($_POST['bulk_delete']))) {
    $ids = isset($_POST['delete_one']) ? [(int)$_POST['delete_one']] : array_map('intval', $_POST['selected_products'] ?? []);
    $deleted = 0;
    foreach (array_unique(array_filter($ids)) as $id) {
        $productName = delete_product($conn, $id);
        if ($productName !== null) {
            $deleted++;
            log_admin_activity('product_deleted', "Deleted product: {$productName}");
        }
    }
    header('Location: /admin/products.php?deleted=' . $deleted);
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $productName = delete_product($conn, $id) ?? "#$id";
    log_admin_activity('product_deleted', "Deleted product: {$productName}");
    header('Location: /admin/products.php?deleted=1');
    exit;
}

if (isset($_GET['duplicate'])) {
    $id = (int)$_GET['duplicate'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if ($p) {
        $newName = $p['name'] . ' (Copy)';
        $newSlug = $p['slug'] . '-copy-' . substr(uniqid(), -4);
        $ins = $conn->prepare("INSERT INTO products (category_id, name, slug, description, price, compare_price, gender, sizes, colors, stock, is_featured, is_new, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?, 'draft')");
        $ins->bind_param('isssddsssiii', $p['category_id'], $newName, $newSlug, $p['description'], $p['price'], $p['compare_price'], $p['gender'], $p['sizes'], $p['colors'], $p['stock'], $p['is_featured'], $p['is_new']);
        $ins->execute();
        $newId = $conn->insert_id;
        $conn->query("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) SELECT $newId, image_url, is_primary, sort_order FROM product_images WHERE product_id=$id");
        log_admin_activity('product_duplicated', "Duplicated product: {$p['name']}");
    }
    header('Location: /admin/products.php?duplicated=1');
    exit;
}

$q = trim($_GET['q'] ?? '');
$categoryFilter = (int)($_GET['cat'] ?? 0);
$statusFilter = $_GET['status'] ?? '';
$stockFilter = $_GET['stock'] ?? '';

$where = ['1=1'];
$params = [];
$types = '';
if ($q !== '') { $where[] = 'p.name LIKE ?'; $params[] = "%$q%"; $types .= 's'; }
if ($categoryFilter) { $where[] = 'p.category_id = ?'; $params[] = $categoryFilter; $types .= 'i'; }
if ($statusFilter) { $where[] = 'p.status = ?'; $params[] = $statusFilter; $types .= 's'; }
if ($stockFilter === 'low') { $where[] = 'p.stock <= 5'; }
if ($stockFilter === 'out') { $where[] = 'p.stock = 0'; }

$sql = "SELECT p.*, c.name AS category_name,
        (SELECT image_url FROM product_images WHERE product_id=p.id ORDER BY is_primary DESC LIMIT 1) AS image
        FROM products p JOIN categories c ON c.id = p.category_id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Products — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Products <span style="font-size:14px;color:var(--muted);font-weight:500;">(<?= count($products) ?>)</span></h1><a class="btn" href="/admin/product-form.php">+ Add Product</a></div>
    <?php if (isset($_GET['deleted'])): ?><div class="alert-success"><?= (int)$_GET['deleted'] > 1 ? (int)$_GET['deleted'] . ' products deleted.' : 'Product deleted.' ?></div><?php endif; ?>
    <?php if (isset($_GET['saved'])): ?><div class="alert-success">Product saved.</div><?php endif; ?>
    <?php if (isset($_GET['duplicated'])): ?><div class="alert-success">Product duplicated as a draft — edit it to publish.</div><?php endif; ?>

    <form method="get" class="filters-inline">
      <input type="text" name="q" placeholder="Search product name…" value="<?= htmlspecialchars($q) ?>">
      <select name="cat">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $categoryFilter==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All Status</option>
        <option value="active" <?= $statusFilter=='active'?'selected':'' ?>>Active</option>
        <option value="draft" <?= $statusFilter=='draft'?'selected':'' ?>>Draft</option>
      </select>
      <select name="stock">
        <option value="">All Stock</option>
        <option value="low" <?= $stockFilter=='low'?'selected':'' ?>>Low Stock (≤5)</option>
        <option value="out" <?= $stockFilter=='out'?'selected':'' ?>>Out of Stock</option>
      </select>
      <button class="btn btn-outline btn-sm" type="submit">Filter</button>
      <?php if ($q || $categoryFilter || $statusFilter || $stockFilter): ?>
      <a class="btn btn-outline btn-sm" href="/admin/products.php">Clear</a>
      <?php endif; ?>
    </form>

    <form method="post" id="productsBulkForm">
      <div class="bulk-actions">
        <button class="btn btn-danger btn-sm" type="submit" name="bulk_delete" value="1" onclick="return confirmBulkDelete();">Delete Selected</button>
        <span class="muted">Select products below, then delete them together.</span>
      </div>
      <table>
        <tr><th><input type="checkbox" id="selectAllProducts" aria-label="Select all products"></th><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
        <?php if (!$products): ?><tr><td colspan="8" class="empty-state">No products match. Try adjusting filters, or click "+ Add Product" to create your first item.</td></tr><?php endif; ?>
      <?php foreach ($products as $p): ?>
      <tr>
        <td><input type="checkbox" name="selected_products[]" value="<?= $p['id'] ?>" class="product-select" aria-label="Select <?= htmlspecialchars($p['name']) ?>"></td>
        <td><img src="<?= htmlspecialchars($p['image'] ?: 'https://placehold.co/60x60?text=No+Img') ?>" width="44" height="44" style="object-fit:cover;border-radius:6px;"></td>
        <td><?= htmlspecialchars($p['name']) ?> <?= $p['is_featured'] ? '⭐' : '' ?></td>
        <td><?= htmlspecialchars($p['category_name']) ?></td>
        <td>₦<?= number_format($p['price']) ?></td>
        <td><?= $p['stock'] ?> <?php if ($p['stock'] <= 5): ?><span class="low-stock-tag"><?= $p['stock']==0 ? 'OUT' : 'LOW' ?></span><?php endif; ?></td>
        <td><span class="badge badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
        <td class="row-actions">
          <a class="action-btn primary" href="/admin/product-form.php?id=<?= $p['id'] ?>">Edit</a>
          <a class="action-btn" href="/product.html?slug=<?= urlencode($p['slug']) ?>" target="_blank">View</a>
          <a class="action-btn" href="/admin/products.php?duplicate=<?= $p['id'] ?>">Duplicate</a>
          <button class="action-btn danger" type="submit" name="delete_one" value="<?= $p['id'] ?>" onclick="return confirm('Delete this product? This cannot be undone.');">Delete</button>
        </td>
      </tr>
      <?php endforeach; ?>
      </table>
    </form>
  </div>
</div>
<script>
  const selectAllProducts = document.getElementById('selectAllProducts');
  const productChecks = [...document.querySelectorAll('.product-select')];
  if (selectAllProducts) {
    selectAllProducts.addEventListener('change', () => {
      productChecks.forEach(check => { check.checked = selectAllProducts.checked; });
    });
  }
  function confirmBulkDelete() {
    const count = productChecks.filter(check => check.checked).length;
    if (!count) {
      alert('Please select at least one product to delete.');
      return false;
    }
    return confirm('Delete ' + count + ' selected product' + (count === 1 ? '' : 's') + '? This cannot be undone.');
  }
</script>
</body>
</html>
