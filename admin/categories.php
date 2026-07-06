<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'categories';
$conn = db();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        $gender = $_POST['gender'];
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, gender, sort_order) VALUES (?, ?, ?, 0)");
        $stmt->bind_param('sss', $name, $slug, $gender);
        $stmt->execute();
        $msg = 'Category added.';
    } elseif ($_POST['action'] === 'edit') {
        $catId = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        $gender = $_POST['gender'];
        $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, gender=? WHERE id=?");
        $stmt->bind_param('sssi', $name, $slug, $gender, $catId);
        $stmt->execute();
        $msg = 'Category updated.';
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $msg = 'Category deleted.';
    }
}

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id=c.id) AS pcount FROM categories c ORDER BY sort_order ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Categories — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Categories</h1></div>
    <?php if ($msg): ?><div class="alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <div class="form-card" style="margin-bottom:26px;">
      <form method="post" class="form-grid">
        <input type="hidden" name="action" value="add">
        <div><label>Category Name</label><input type="text" name="name" required placeholder="e.g. Sunglasses"></div>
        <div><label>Gender</label>
          <select name="gender"><option value="unisex">Unisex</option><option value="male">Male</option><option value="female">Female</option></select>
        </div>
        <div class="full"><button class="btn" type="submit">+ Add Category</button></div>
      </form>
    </div>

    <table>
      <tr><th>Name</th><th>Slug</th><th>Gender</th><th>Products</th><th style="width:150px;"></th></tr>
      <?php foreach ($categories as $c): ?>
      <tr data-cat-row="<?= $c['id'] ?>">
        <td>
          <span class="view-mode"><?= htmlspecialchars($c['name']) ?></span>
          <input class="edit-mode name-input" style="display:none;margin:0;" type="text" value="<?= htmlspecialchars($c['name']) ?>">
        </td>
        <td><?= htmlspecialchars($c['slug']) ?></td>
        <td>
          <span class="view-mode"><?= ucfirst($c['gender']) ?></span>
          <select class="edit-mode gender-input" style="display:none;margin:0;width:auto;">
            <option value="unisex" <?= $c['gender']=='unisex'?'selected':'' ?>>Unisex</option>
            <option value="male" <?= $c['gender']=='male'?'selected':'' ?>>Male</option>
            <option value="female" <?= $c['gender']=='female'?'selected':'' ?>>Female</option>
          </select>
        </td>
        <td><?= $c['pcount'] ?></td>
        <td>
          <span class="view-mode">
            <a class="icon-link" href="#" onclick="toggleEdit(<?= $c['id'] ?>);return false;">Edit</a>
            <a class="icon-link" style="color:#c0392b" href="#" onclick="submitDelete(<?= $c['id'] ?>);return false;">Delete</a>
          </span>
          <span class="edit-mode" style="display:none;">
            <a class="icon-link" href="#" onclick="submitEdit(<?= $c['id'] ?>);return false;" style="font-weight:700;">Save</a>
            <a class="icon-link" href="#" onclick="toggleEdit(<?= $c['id'] ?>);return false;">Cancel</a>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$categories): ?><tr><td colspan="5" class="empty-state">No categories yet.</td></tr><?php endif; ?>
    </table>

    <form method="post" id="actionForm" style="display:none;">
      <input type="hidden" name="action" id="actionField">
      <input type="hidden" name="id" id="idField">
      <input type="hidden" name="name" id="nameField">
      <input type="hidden" name="gender" id="genderField">
    </form>
    <script>
      function toggleEdit(id) {
        const row = document.querySelector(`tr[data-cat-row="${id}"]`);
        row.querySelectorAll('.view-mode').forEach(e => e.style.display = e.style.display === 'none' ? '' : 'none');
        row.querySelectorAll('.edit-mode').forEach(e => e.style.display = e.style.display === 'none' ? '' : 'none');
      }
      function submitEdit(id) {
        const row = document.querySelector(`tr[data-cat-row="${id}"]`);
        document.getElementById('actionField').value = 'edit';
        document.getElementById('idField').value = id;
        document.getElementById('nameField').value = row.querySelector('.name-input').value;
        document.getElementById('genderField').value = row.querySelector('.gender-input').value;
        document.getElementById('actionForm').submit();
      }
      function submitDelete(id) {
        if (!confirm('Delete this category? Products in it will also be removed.')) return;
        document.getElementById('actionField').value = 'delete';
        document.getElementById('idField').value = id;
        document.getElementById('actionForm').submit();
      }
    </script>
  </div>
</div>
</body>
</html>
