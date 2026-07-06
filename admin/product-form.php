<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'products';
$conn = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = ['name'=>'','slug'=>'','description'=>'','price'=>'','compare_price'=>'','category_id'=>'','gender'=>'unisex','sizes'=>'','colors'=>'','stock'=>10,'is_featured'=>0,'is_new'=>0,'status'=>'active'];

// ---- Handle image-only actions (delete / set primary) via GET, redirect back ----
if (isset($_GET['delete_image']) && $id) {
    $imgId = (int)$_GET['delete_image'];
    $imgStmt = $conn->prepare("SELECT image_url, is_primary FROM product_images WHERE id = ? AND product_id = ?");
    $imgStmt->bind_param('ii', $imgId, $id);
    $imgStmt->execute();
    $img = $imgStmt->get_result()->fetch_assoc();
    if ($img) {
        $wasPrimary = (bool)$img['is_primary'];
        $del = $conn->prepare("DELETE FROM product_images WHERE id = ?");
        $del->bind_param('i', $imgId);
        $del->execute();
        if (strpos($img['image_url'], UPLOADS_URL) === 0) {
            $path = __DIR__ . '/../uploads/products/' . basename($img['image_url']);
            if (file_exists($path)) @unlink($path);
        }
        if ($wasPrimary) {
            $conn->query("UPDATE product_images SET is_primary = 1 WHERE product_id = $id ORDER BY sort_order ASC LIMIT 1");
        }
    }
    header('Location: /admin/product-form.php?id=' . $id . '&img_updated=1');
    exit;
}
if (isset($_GET['set_primary']) && $id) {
    $imgId = (int)$_GET['set_primary'];
    $conn->query("UPDATE product_images SET is_primary = 0 WHERE product_id = $id");
    $upd = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?");
    $upd->bind_param('ii', $imgId, $id);
    $upd->execute();
    header('Location: /admin/product-form.php?id=' . $id . '&img_updated=1');
    exit;
}

// ---- Handle variant stock save ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_variants']) && $id) {
    $sizesIn = $_POST['variant_size'] ?? [];
    $colorsIn = $_POST['variant_color'] ?? [];
    $qtysIn = $_POST['variant_qty'] ?? [];
    $upsert = $conn->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?, ?, ?, ?)
                               ON DUPLICATE KEY UPDATE stock = VALUES(stock)");
    for ($i = 0; $i < count($qtysIn); $i++) {
        $vSize = $sizesIn[$i] !== '' ? $sizesIn[$i] : null;
        $vColor = $colorsIn[$i] !== '' ? $colorsIn[$i] : null;
        $vQty = (int) $qtysIn[$i];
        $upsert->bind_param('issi', $id, $vSize, $vColor, $vQty);
        $upsert->execute();
    }
    log_admin_activity('variant_stock_updated', "Updated variant stock for product #{$id}");
    header('Location: /admin/product-form.php?id=' . $id . '&variants_saved=1');
    exit;
}

// ---- Handle image reorder (drag-and-drop, called via fetch) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reorder_images']) && $id) {
    $order = json_decode($_POST['order'] ?? '[]', true);
    if (is_array($order)) {
        $upd = $conn->prepare("UPDATE product_images SET sort_order = ? WHERE id = ? AND product_id = ?");
        foreach ($order as $index => $imgId) {
            $imgId = (int) $imgId;
            $upd->bind_param('iii', $index, $imgId, $id);
            $upd->execute();
        }
    }
    json_response(['success' => true]);
}

$images = [];
$variants = [];
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    if ($found) $product = $found;
    $imgStmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
    $imgStmt->bind_param('i', $id);
    $imgStmt->execute();
    $images = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $varStmt = $conn->prepare("SELECT size, color, stock FROM product_variants WHERE product_id = ?");
    $varStmt->bind_param('i', $id);
    $varStmt->execute();
    foreach ($varStmt->get_result()->fetch_all(MYSQLI_ASSOC) as $v) {
        $key = ($v['size'] ?? '') . '||' . ($v['color'] ?? '');
        $variants[$key] = (int) $v['stock'];
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . ($id ? '' : '-' . substr(uniqid(), -4));
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $comparePrice = $_POST['compare_price'] !== '' ? (float)$_POST['compare_price'] : null;
    $categoryId = (int)$_POST['category_id'];
    $gender = $_POST['gender'];
    $sizes = trim($_POST['sizes']);
    $colors = trim($_POST['colors']);
    $stock = (int)$_POST['stock'];
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $status = $_POST['status'];

    if ($name === '' || $price <= 0 || !$categoryId) {
        $error = 'Please fill in name, price and category.';
    } else {
        if ($id) {
            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, compare_price=?, category_id=?, gender=?, sizes=?, colors=?, stock=?, is_featured=?, is_new=?, status=? WHERE id=?");
            $stmt->bind_param('ssddisssiiisi', $name, $description, $price, $comparePrice, $categoryId, $gender, $sizes, $colors, $stock, $isFeatured, $isNew, $status, $id);
            $stmt->execute();
            $productId = $id;
            log_admin_activity('product_updated', "Updated product: {$name}");
        } else {
            $stmt = $conn->prepare("INSERT INTO products (category_id, name, slug, description, price, compare_price, gender, sizes, colors, stock, is_featured, is_new, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('isssddsssiiis', $categoryId, $name, $slug, $description, $price, $comparePrice, $gender, $sizes, $colors, $stock, $isFeatured, $isNew, $status);
            $stmt->execute();
            $productId = $conn->insert_id;
            log_admin_activity('product_created', "Created product: {$name}");
        }

        // handle multiple image uploads from local device
        if (!empty($_FILES['images']['name'][0])) {
            if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);
            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $origName = $_FILES['images']['name'][$i];
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','webp','gif'])) continue;
                $filename = 'p' . $productId . '-' . time() . '-' . $i . '-' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$i], UPLOADS_DIR . $filename)) {
                    $url = UPLOADS_URL . $filename;
                    $hasPrimary = $conn->query("SELECT COUNT(*) c FROM product_images WHERE product_id=$productId AND is_primary=1")->fetch_assoc()['c'];
                    $isPrimary = $hasPrimary ? 0 : 1;
                    $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
                    $imgStmt->bind_param('isi', $productId, $url, $isPrimary);
                    $imgStmt->execute();
                }
            }
        }
        // or image URL pasted in
        if (!empty($_POST['image_url'])) {
            $url = trim($_POST['image_url']);
            $hasPrimary = $conn->query("SELECT COUNT(*) c FROM product_images WHERE product_id=$productId AND is_primary=1")->fetch_assoc()['c'];
            $isPrimary = $hasPrimary ? 0 : 1;
            $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
            $imgStmt->bind_param('isi', $productId, $url, $isPrimary);
            $imgStmt->execute();
        }

        header('Location: /admin/products.php?saved=1');
        exit;
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title><?= $id ? 'Edit' : 'Add' ?> Product — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1><?= $id ? 'Edit Product' : 'Add Product' ?></h1><a class="btn btn-outline" href="/admin/products.php">← Back to Products</a></div>
    <?php if ($id): ?>
    <form method="post" action="/admin/products.php" class="quick-delete-form" onsubmit="return confirm('Delete this product? This cannot be undone.');">
      <button class="btn btn-danger btn-sm" type="submit" name="delete_one" value="<?= $id ?>">Delete Product</button>
    </form>
    <?php endif; ?>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if (isset($_GET['img_updated'])): ?><div class="alert-success">Image updated.</div><?php endif; ?>

    <div class="form-card" style="max-width:760px;">
      <form method="post" enctype="multipart/form-data" id="productForm">
        <div class="form-grid">
          <div class="full"><label>Product Name</label><input type="text" name="name" required value="<?= htmlspecialchars($product['name']) ?>"></div>
          <div class="full"><label>Description</label><textarea name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea></div>
          <div><label>Category</label>
            <select name="category_id" required>
              <option value="">Select category</option>
              <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $product['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div><label>Gender</label>
            <select name="gender">
              <option value="unisex" <?= $product['gender']=='unisex'?'selected':'' ?>>Unisex</option>
              <option value="male" <?= $product['gender']=='male'?'selected':'' ?>>Male</option>
              <option value="female" <?= $product['gender']=='female'?'selected':'' ?>>Female</option>
            </select>
          </div>
          <div><label>Price (₦)</label><input type="number" step="0.01" name="price" required value="<?= htmlspecialchars($product['price']) ?>"></div>
          <div><label>Compare-at Price (₦, optional)</label><input type="number" step="0.01" name="compare_price" value="<?= htmlspecialchars($product['compare_price'] ?? '') ?>"></div>
          <div><label>Sizes (comma separated)</label><input type="text" name="sizes" placeholder="S,M,L,XL" value="<?= htmlspecialchars($product['sizes']) ?>"></div>
          <div><label>Colors (comma separated)</label><input type="text" name="colors" placeholder="Black,Gold,White" value="<?= htmlspecialchars($product['colors']) ?>"></div>
          <div><label>Stock Quantity</label><input type="number" name="stock" value="<?= htmlspecialchars($product['stock']) ?>"></div>
          <div><label>Status</label>
            <select name="status">
              <option value="active" <?= $product['status']=='active'?'selected':'' ?>>Active</option>
              <option value="draft" <?= $product['status']=='draft'?'selected':'' ?>>Draft</option>
            </select>
          </div>
          <div class="full" style="display:flex;gap:24px;margin-bottom:14px;">
            <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" style="width:auto;" name="is_featured" <?= $product['is_featured']?'checked':'' ?>> Featured on homepage</label>
            <label style="display:flex;align-items:center;gap:6px;"><input type="checkbox" style="width:auto;" name="is_new" <?= $product['is_new']?'checked':'' ?>> Mark as "New"</label>
          </div>

          <?php if ($images): ?>
          <div class="full">
            <label>Current Images (drag to reorder · hover to set as main photo or delete)</label>
            <div class="image-manager" id="imageManager">
              <?php foreach ($images as $img): ?>
              <div class="img-tile <?= $img['is_primary'] ? 'primary' : '' ?>" draggable="true" data-img-id="<?= $img['id'] ?>">
                <img src="<?= htmlspecialchars($img['image_url']) ?>" alt="">
                <?php if ($img['is_primary']): ?><span class="primary-badge">MAIN</span><?php endif; ?>
                <div class="tile-actions">
                  <?php if (!$img['is_primary']): ?>
                  <button type="button" onclick="window.location='/admin/product-form.php?id=<?= $id ?>&set_primary=<?= $img['id'] ?>'">Set Main</button>
                  <?php endif; ?>
                  <button type="button" style="color:#ff8a80;" onclick="if(confirm('Delete this image?'))window.location='/admin/product-form.php?id=<?= $id ?>&delete_image=<?= $img['id'] ?>'">Delete</button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="full">
            <label>Upload Images From Your Device (you can select multiple)</label>
            <div class="dropzone" id="dropzone">
              📷 Click to choose photos, or drag &amp; drop here<br>
              <span id="fileChosenLabel" style="font-size:11.5px;"></span>
            </div>
            <input type="file" name="images[]" id="imagesInput" accept="image/*" multiple style="display:none;">
          </div>
          <div class="full"><label>Or Add Image by URL (optional)</label><input type="text" name="image_url" placeholder="https://..."></div>

          <div class="full"><button class="btn" type="submit"><?= $id ? 'Save Changes' : 'Create Product' ?></button></div>
        </div>
      </form>
    </div>

    <?php if ($id && (trim($product['sizes']) !== '' || trim($product['colors']) !== '')): ?>
    <?php
      $sizeList = $product['sizes'] ? array_map('trim', explode(',', $product['sizes'])) : [''];
      $colorList = $product['colors'] ? array_map('trim', explode(',', $product['colors'])) : [''];
    ?>
    <h2 style="font-size:16px;margin:30px 0 12px;">Variant Stock</h2>
    <?php if (isset($_GET['variants_saved'])): ?><div class="alert-success">Variant stock updated.</div><?php endif; ?>
    <div class="form-card" style="max-width:760px;">
      <p class="muted" style="margin-bottom:16px;">Set stock per size/color combination. Leave everything at 0 and the product will fall back to the single "Stock Quantity" field above.</p>
      <form method="post">
        <input type="hidden" name="save_variants" value="1">
        <table>
          <tr>
            <?php if ($product['sizes']): ?><th>Size</th><?php endif; ?>
            <?php if ($product['colors']): ?><th>Color</th><?php endif; ?>
            <th>Stock</th>
          </tr>
          <?php foreach ($sizeList as $sz): foreach ($colorList as $cl): ?>
          <tr>
            <?php if ($product['sizes']): ?><td><?= htmlspecialchars($sz) ?><input type="hidden" name="variant_size[]" value="<?= htmlspecialchars($sz) ?>"></td><?php else: ?><input type="hidden" name="variant_size[]" value=""><?php endif; ?>
            <?php if ($product['colors']): ?><td><?= htmlspecialchars($cl) ?><input type="hidden" name="variant_color[]" value="<?= htmlspecialchars($cl) ?>"></td><?php else: ?><input type="hidden" name="variant_color[]" value=""><?php endif; ?>
            <td><input type="number" min="0" name="variant_qty[]" style="width:100px;margin:0;" value="<?= $variants[$sz . '||' . $cl] ?? 0 ?>"></td>
          </tr>
          <?php endforeach; endforeach; ?>
        </table>
        <button class="btn" type="submit" style="margin-top:16px;">Save Variant Stock</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>
<script>
  const dropzone = document.getElementById('dropzone');
  const fileInput = document.getElementById('imagesInput');
  const label = document.getElementById('fileChosenLabel');
  dropzone.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', () => {
    label.textContent = fileInput.files.length ? fileInput.files.length + ' file(s) selected' : '';
  });
  ['dragover','dragenter'].forEach(ev => dropzone.addEventListener(ev, (e) => { e.preventDefault(); dropzone.classList.add('dragover'); }));
  ['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); }));
  dropzone.addEventListener('drop', (e) => {
    fileInput.files = e.dataTransfer.files;
    label.textContent = fileInput.files.length ? fileInput.files.length + ' file(s) selected' : '';
  });

  // ---- Drag-to-reorder existing images ----
  const imageManager = document.getElementById('imageManager');
  if (imageManager) {
    let draggedTile = null;
    imageManager.querySelectorAll('.img-tile').forEach(tile => {
      tile.addEventListener('dragstart', () => { draggedTile = tile; tile.style.opacity = '0.4'; });
      tile.addEventListener('dragend', () => { tile.style.opacity = ''; saveImageOrder(); });
      tile.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!draggedTile || draggedTile === tile) return;
        const rect = tile.getBoundingClientRect();
        const after = (e.clientX - rect.left) > rect.width / 2;
        imageManager.insertBefore(draggedTile, after ? tile.nextSibling : tile);
      });
    });
  }
  function saveImageOrder() {
    const ids = [...imageManager.querySelectorAll('.img-tile')].map(t => t.dataset.imgId);
    const params = new URLSearchParams();
    params.set('reorder_images', '1');
    params.set('order', JSON.stringify(ids));
    fetch(window.location.pathname + window.location.search, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
      .catch(() => {});
  }
</script>
</body>
</html>
