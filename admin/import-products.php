<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'import';
$conn = db();
$results = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['csv_file']['tmp_name'])) {
    $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
    if (!$handle) {
        $error = 'Could not read the uploaded file.';
    } else {
        $header = fgetcsv($handle);
        $header = array_map(fn($h) => strtolower(trim($h)), $header);
        $required = ['name', 'category', 'price'];
        $missing = array_diff($required, $header);
        if ($missing) {
            $error = 'Your CSV is missing required column(s): ' . implode(', ', $missing);
        } else {
            $categories = $conn->query("SELECT id, name, slug FROM categories")->fetch_all(MYSQLI_ASSOC);
            $catByName = [];
            foreach ($categories as $c) { $catByName[strtolower($c['name'])] = $c['id']; $catByName[strtolower($c['slug'])] = $c['id']; }

            $created = 0; $skipped = 0; $errors = [];
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                $data = array_combine($header, $row);
                if (empty($data['name']) || empty($data['category']) || !isset($data['price']) || $data['price'] === '') {
                    $errors[] = "Row {$rowNum}: missing name, category, or price — skipped.";
                    $skipped++;
                    continue;
                }
                $categoryId = $catByName[strtolower(trim($data['category']))] ?? null;
                if (!$categoryId) {
                    $errors[] = "Row {$rowNum}: category \"{$data['category']}\" not found — skipped.";
                    $skipped++;
                    continue;
                }
                $name = trim($data['name']);
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)) . '-' . substr(uniqid(), -4);
                $price = (float) $data['price'];
                $comparePrice = !empty($data['compare_price']) ? (float) $data['compare_price'] : null;
                $gender = in_array($data['gender'] ?? '', ['male', 'female', 'unisex']) ? $data['gender'] : 'unisex';
                $sizes = trim($data['sizes'] ?? '');
                $colors = trim($data['colors'] ?? '');
                $stock = isset($data['stock']) && $data['stock'] !== '' ? (int) $data['stock'] : 10;
                $description = trim($data['description'] ?? '');

                $stmt = $conn->prepare("INSERT INTO products (category_id, name, slug, description, price, compare_price, gender, sizes, colors, stock, status) VALUES (?,?,?,?,?,?,?,?,?,?,'active')");
                $stmt->bind_param('isssddsssi', $categoryId, $name, $slug, $description, $price, $comparePrice, $gender, $sizes, $colors, $stock);
                if ($stmt->execute()) {
                    $productId = $conn->insert_id;
                    if (!empty($data['image_url'])) {
                        $imgStmt = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, 1)");
                        $url = trim($data['image_url']);
                        $imgStmt->bind_param('is', $productId, $url);
                        $imgStmt->execute();
                    }
                    $created++;
                } else {
                    $errors[] = "Row {$rowNum}: could not save \"{$name}\".";
                    $skipped++;
                }
            }
            fclose($handle);
            log_admin_activity('products_imported', "Imported {$created} products via CSV ({$skipped} skipped)");
            $results = ['created' => $created, 'skipped' => $skipped, 'errors' => $errors];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Import Products — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Import Products</h1></div>
    <?php if ($error): ?><div class="alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($results): ?>
    <div class="alert-success">Imported <?= $results['created'] ?> product(s). <?= $results['skipped'] ? $results['skipped'] . ' row(s) skipped.' : '' ?></div>
    <?php if ($results['errors']): ?>
    <div class="form-card" style="margin-bottom:20px;">
      <p style="font-weight:700;font-size:13px;margin-bottom:10px;">Issues found:</p>
      <ul style="font-size:12.5px;color:var(--muted);padding-left:18px;">
        <?php foreach ($results['errors'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="form-card" style="margin-bottom:26px;">
      <p class="muted" style="margin-bottom:16px;">Upload a CSV file to add many products at once. Required columns: <b>name</b>, <b>category</b> (must match an existing category name or slug), <b>price</b>. Optional columns: <b>compare_price</b>, <b>gender</b> (male/female/unisex), <b>sizes</b> (comma-separated), <b>colors</b> (comma-separated), <b>stock</b>, <b>description</b>, <b>image_url</b>.</p>
      <form method="post" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required style="margin-bottom:14px;">
        <button class="btn" type="submit">Upload &amp; Import</button>
      </form>
    </div>

    <div class="form-card">
      <p style="font-weight:700;font-size:13px;margin-bottom:10px;">Sample CSV format</p>
      <pre style="font-size:11.5px;background:var(--ivory);padding:14px;border-radius:8px;overflow-x:auto;">name,category,price,compare_price,gender,sizes,colors,stock,description,image_url
Classic Denim Jacket,Jeans,25000,30000,unisex,"S,M,L,XL","Blue,Black",20,A timeless denim jacket.,https://example.com/photo.jpg</pre>
    </div>
  </div>
</div>
</body>
</html>
