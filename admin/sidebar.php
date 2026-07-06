<?php if (empty($_SESSION['admin_id'])) { exit; } ?>
<div class="admin-topbar-mobile">
  <button class="admin-burger" id="adminBurger" aria-label="Open menu">☰</button>
  <span class="admin-topbar-brand">FAAF ADMIN</span>
</div>
<div class="sidebar-dim" id="sidebarDim"></div>
<div class="sidebar" id="adminSidebar">
  <div class="brand">FAAF ADMIN</div>
  <nav>
    <a href="/admin/index.php" class="<?= $active === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a>
    <a href="/admin/products.php" class="<?= $active === 'products' ? 'active' : '' ?>">👕 Products</a>
    <a href="/admin/categories.php" class="<?= $active === 'categories' ? 'active' : '' ?>">🗂️ Categories</a>
    <a href="/admin/orders.php" class="<?= $active === 'orders' ? 'active' : '' ?>">🧾 Orders</a>
    <a href="/admin/customers.php" class="<?= $active === 'customers' ? 'active' : '' ?>">👥 Customers</a>
    <a href="/admin/coupons.php" class="<?= $active === 'coupons' ? 'active' : '' ?>">🏷️ Coupons</a>
    <a href="/admin/reviews.php" class="<?= $active === 'reviews' ? 'active' : '' ?>">⭐ Reviews</a>
    <a href="/admin/settings.php" class="<?= $active === 'settings' ? 'active' : '' ?>">⚙️ Settings</a>
    <a href="/admin/users.php" class="<?= $active === 'users' ? 'active' : '' ?>">🔐 Admin Users</a>
    <a href="/admin/activity-log.php" class="<?= $active === 'activity' ? 'active' : '' ?>">📜 Activity Log</a>
    <a href="/admin/import-products.php" class="<?= $active === 'import' ? 'active' : '' ?>">📥 Import Products</a>
    <a href="/admin/account.php" class="<?= $active === 'account' ? 'active' : '' ?>">👤 My Account</a>
  </nav>
  <a href="/admin/logout.php" class="logout">Signed in as <?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?> · Log out</a>
</div>
<script>
(function(){
  var burger = document.getElementById('adminBurger');
  var sidebar = document.getElementById('adminSidebar');
  var dim = document.getElementById('sidebarDim');
  function close(){ sidebar.classList.remove('open'); dim.classList.remove('open'); }
  if (burger) burger.addEventListener('click', function(){ sidebar.classList.add('open'); dim.classList.add('open'); });
  if (dim) dim.addEventListener('click', close);
})();
</script>
