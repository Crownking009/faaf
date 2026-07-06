<?php
require_once __DIR__ . '/auth.php';
require_admin_login();
$active = 'reviews';
$conn = db();

if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $conn->query("UPDATE product_reviews SET is_approved = 1 WHERE id = $id");
    header('Location: /admin/reviews.php?approved=1');
    exit;
}
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $conn->query("UPDATE product_reviews SET is_approved = 0 WHERE id = $id");
    header('Location: /admin/reviews.php?rejected=1');
    exit;
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM product_reviews WHERE id = $id");
    header('Location: /admin/reviews.php?deleted=1');
    exit;
}

$filter = $_GET['status'] ?? 'pending';
$where = $filter === 'approved' ? 'r.is_approved = 1' : ($filter === 'all' ? '1=1' : 'r.is_approved = 0');

$reviews = $conn->query("SELECT r.*, p.name AS product_name, p.slug AS product_slug
                          FROM product_reviews r JOIN products p ON p.id = r.product_id
                          WHERE $where ORDER BY r.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pendingCount = $conn->query("SELECT COUNT(*) c FROM product_reviews WHERE is_approved = 0")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><title>Reviews — FAAF Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="/admin/admin.css">
</head>
<body>
<div class="shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main">
    <div class="topbar"><h1>Reviews <?= $pendingCount ? "<span style=\"font-size:14px;color:#c0392b;font-weight:600;\">({$pendingCount} pending)</span>" : '' ?></h1></div>
    <?php if (isset($_GET['approved'])): ?><div class="alert-success">Review approved and now visible on the site.</div><?php endif; ?>
    <?php if (isset($_GET['rejected'])): ?><div class="alert-success">Review hidden.</div><?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?><div class="alert-success">Review deleted.</div><?php endif; ?>

    <div class="filters-inline">
      <a class="btn btn-sm <?= $filter=='pending'?'':'btn-outline' ?>" href="/admin/reviews.php?status=pending">Pending</a>
      <a class="btn btn-sm <?= $filter=='approved'?'':'btn-outline' ?>" href="/admin/reviews.php?status=approved">Approved</a>
      <a class="btn btn-sm <?= $filter=='all'?'':'btn-outline' ?>" href="/admin/reviews.php?status=all">All</a>
    </div>

    <table>
      <tr><th>Product</th><th>Reviewer</th><th>Rating</th><th>Review</th><th>Date</th><th></th></tr>
      <?php if (!$reviews): ?><tr><td colspan="6" class="empty-state">No reviews here.</td></tr><?php endif; ?>
      <?php foreach ($reviews as $r): ?>
      <tr>
        <td><a class="icon-link" href="/product.html?slug=<?= urlencode($r['product_slug']) ?>" target="_blank"><?= htmlspecialchars($r['product_name']) ?></a></td>
        <td><?= htmlspecialchars($r['reviewer_name']) ?></td>
        <td style="color:var(--gold-deep);"><?= str_repeat('★', (int)$r['rating']) . str_repeat('☆', 5 - (int)$r['rating']) ?></td>
        <td style="max-width:280px;white-space:normal;"><?= htmlspecialchars($r['review_text'] ?: '—') ?></td>
        <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
        <td>
          <?php if (!$r['is_approved']): ?>
          <a class="icon-link" href="/admin/reviews.php?approve=<?= $r['id'] ?>&status=<?= $filter ?>">Approve</a>
          <?php else: ?>
          <a class="icon-link" href="/admin/reviews.php?reject=<?= $r['id'] ?>&status=<?= $filter ?>">Hide</a>
          <?php endif; ?>
          <a class="icon-link" style="color:#c0392b" href="/admin/reviews.php?delete=<?= $r['id'] ?>&status=<?= $filter ?>" onclick="return confirm('Delete this review permanently?');">Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
</body>
</html>
