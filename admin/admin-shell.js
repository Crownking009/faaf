const STORAGE_KEY = 'faaf_admin_products_v1';
let pendingProductImageUrls = [];

function escapeHtml(str) {
  if (str == null) return '';
  return String(str).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
}

function slugify(value) {
  return String(value || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');
}

function parseList(value) {
  if (Array.isArray(value)) return value.map((item) => String(item).trim()).filter(Boolean);
  if (!value) return [];
  return String(value)
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);
}

function normalizeProduct(product, fallbackId) {
  const sizes = Array.isArray(product.sizes) ? product.sizes : parseList(product.sizes);
  const colors = Array.isArray(product.colors) ? product.colors : parseList(product.colors);
  const images = Array.isArray(product.images) ? product.images.filter(Boolean) : parseList(product.images);
  const image = product.image || images[0] || '';
  const categoryName = product.category_name || product.category || 'Uncategorized';
  const categorySlug = product.category_slug || slugify(categoryName);
  return {
    id: Number(product.id || fallbackId || Date.now()),
    category_id: Number(product.category_id || 0),
    category_name: categoryName,
    category_slug: categorySlug,
    name: product.name || 'Untitled product',
    slug: product.slug || slugify(product.name || 'untitled-product'),
    description: product.description || '',
    price: Number(product.price || 0),
    compare_price: product.compare_price != null && product.compare_price !== '' ? Number(product.compare_price) : null,
    gender: product.gender || 'unisex',
    sizes,
    colors,
    stock: Number(product.stock || 0),
    is_featured: Number(product.is_featured || 0),
    is_new: Number(product.is_new || 0),
    status: product.status || 'active',
    image,
    images: images.length ? images : (image ? [image] : []),
    variants: Array.isArray(product.variants) ? product.variants : [],
  };
}

function getDemoProducts() {
  const source = window.FAAF_DEMO_DATA?.products || [];
  return source.map((product, index) => normalizeProduct(product, index + 1));
}

function loadProducts() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length) {
        return parsed.map((product, index) => normalizeProduct(product, index + 1));
      }
    }
  } catch (err) {
    console.warn('Unable to read saved admin products', err);
  }

  const seeded = getDemoProducts();
  saveProducts(seeded);
  return seeded;
}

function saveProducts(products) {
  const normalized = products.map((product, index) => normalizeProduct(product, index + 1));
  localStorage.setItem(STORAGE_KEY, JSON.stringify(normalized));
  if (window.FAAF_DEMO_DATA) {
    window.FAAF_DEMO_DATA.products = normalized;
    window.FAAF_DEMO_DATA.categories = buildCategories(normalized);
  }
  return normalized;
}

function buildCategories(products) {
  const map = new Map();
  products.forEach((product) => {
    const slug = product.category_slug || slugify(product.category_name || 'uncategorized');
    const name = product.category_name || 'Uncategorized';
    if (!map.has(slug)) {
      map.set(slug, { id: map.size + 1, name, slug, gender: product.gender || 'unisex', sort_order: map.size + 1, product_count: 0 });
    }
    map.get(slug).product_count += 1;
  });
  return Array.from(map.values());
}

function getCategories(products) {
  return buildCategories(products);
}

function readFileAsDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(String(reader.result || ''));
    reader.onerror = () => reject(new Error('Unable to read image file.'));
    reader.readAsDataURL(file);
  });
}

function renderImagePreviewList(imageUrls) {
  const wrap = document.getElementById('imagePreviewList');
  if (!wrap) return;
  const images = (imageUrls || []).filter(Boolean);
  wrap.innerHTML = images.length
    ? images.map((image) => `<div class="img-tile"><img src="${escapeHtml(image)}" alt="Product preview" /></div>`).join('')
    : '<div class="muted">No images selected yet.</div>';
}

function collectProductImageState(product) {
  const manualValues = parseList(document.getElementById('productImages')?.value || '');
  const uploadedValues = Array.isArray(pendingProductImageUrls) ? pendingProductImageUrls.filter(Boolean) : [];
  const fallbackValues = product?.images?.length ? product.images : (product?.image ? [product.image] : []);
  const imageEntries = uploadedValues.length ? uploadedValues : (manualValues.length ? manualValues : fallbackValues);
  return {
    image: imageEntries[0] || '',
    images: imageEntries,
  };
}

function formatCurrency(value) {
  return '₦' + Number(value || 0).toLocaleString('en-NG', { maximumFractionDigits: 0 });
}

function getPageKey() {
  return (window.location.pathname.split('/').pop() || 'index.html').replace(/\.html$/i, '') || 'index';
}

function renderShell(pageKey, title, content) {
  const shell = `
    <div class="shell">
      ${pageKey === 'login' ? '' : `
        <aside class="sidebar">
          <div class="brand">FAAF ADMIN</div>
          <nav>
            <a href="index.html" class="${pageKey === 'index' ? 'active' : ''}">📊 Dashboard</a>
            <a href="products.html" class="${pageKey === 'products' || pageKey === 'product-form' ? 'active' : ''}">👕 Products</a>
            <a href="categories.html" class="${pageKey === 'categories' ? 'active' : ''}">🗂️ Categories</a>
            <a href="orders.html" class="${pageKey === 'orders' || pageKey === 'invoice' || pageKey === 'export-orders' ? 'active' : ''}">🧾 Orders</a>
            <a href="customers.html" class="${pageKey === 'customers' ? 'active' : ''}">👥 Customers</a>
            <a href="coupons.html" class="${pageKey === 'coupons' ? 'active' : ''}">🏷️ Coupons</a>
            <a href="reviews.html" class="${pageKey === 'reviews' ? 'active' : ''}">⭐ Reviews</a>
            <a href="settings.html" class="${pageKey === 'settings' ? 'active' : ''}">⚙️ Settings</a>
            <a href="users.html" class="${pageKey === 'users' ? 'active' : ''}">🔐 Admin Users</a>
            <a href="activity-log.html" class="${pageKey === 'activity-log' ? 'active' : ''}">📜 Activity Log</a>
            <a href="import-products.html" class="${pageKey === 'import-products' ? 'active' : ''}">📥 Import Products</a>
            <a href="account.html" class="${pageKey === 'account' ? 'active' : ''}">👤 My Account</a>
          </nav>
          <a href="logout.html" class="logout">Signed in as local-preview · Log out</a>
        </aside>
      `}
      <main class="main">
        ${pageKey === 'login' ? content : `
          <div class="topbar">
            <h1>${title}</h1>
            <a class="btn btn-outline" href="../index.html">← Back to Storefront</a>
          </div>
          ${content}
        `}
      </main>
    </div>
  `;
  document.getElementById('app').innerHTML = shell;
  document.title = `${title} · FAAF Admin`;
}

function renderDashboard() {
  const products = loadProducts();
  const activeProducts = products.filter((product) => product.status === 'active');
  const lowStockProducts = products.filter((product) => Number(product.stock || 0) <= 5);
  const categories = getCategories(products);
  const content = `
    <div class="cards-row">
      <div class="stat-card"><div class="num">${products.length}</div><div class="label">Products in catalog</div></div>
      <div class="stat-card"><div class="num">${activeProducts.length}</div><div class="label">Active products</div></div>
      <div class="stat-card"><div class="num">${categories.length}</div><div class="label">Categories</div></div>
      <div class="stat-card"><div class="num">${lowStockProducts.length}</div><div class="label">Low stock items</div></div>
    </div>
    <div class="form-card">
      <h3 style="margin-top:0;">Product catalog</h3>
      <p class="muted">The admin dashboard is now reading your local product catalog so you can manage it directly from this page.</p>
      <a class="btn" href="products.html">Manage products</a>
    </div>
    <div class="form-card" style="margin-top:16px;">
      <h3 style="margin-top:0;">Latest products</h3>
      <table>
        <thead><tr><th>Product</th><th>Category</th><th>Status</th><th>Stock</th></tr></thead>
        <tbody>
          ${products.slice(0, 6).map((product) => `
            <tr>
              <td>${escapeHtml(product.name)}</td>
              <td>${escapeHtml(product.category_name)}</td>
              <td><span class="badge ${product.status === 'active' ? 'badge-active' : 'badge-draft'}">${escapeHtml(product.status || 'draft')}</span></td>
              <td>${Number(product.stock || 0)}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
  renderShell('index', 'Dashboard', content);
}

function renderProductsPage() {
  const products = loadProducts();
  const params = new URLSearchParams(window.location.search);
  const search = params.get('q') || '';
  const statusFilter = params.get('status') || '';
  const categoryFilter = params.get('category') || '';
  const filtered = products.filter((product) => {
    const matchesSearch = !search || `${product.name} ${product.description} ${product.category_name}`.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = !statusFilter || product.status === statusFilter;
    const matchesCategory = !categoryFilter || product.category_slug === categoryFilter;
    return matchesSearch && matchesStatus && matchesCategory;
  });
  const categories = getCategories(products);
  const content = `
    <div class="topbar">
      <h1>Products</h1>
      <div class="row-actions">
        <button class="btn btn-danger" type="button" id="deleteAllProductsButton">Delete all products</button>
        <a class="btn" href="product-form.html">+ Add Product</a>
      </div>
    </div>
    ${params.get('saved') ? '<div class="alert-success">Product saved successfully.</div>' : ''}
    ${params.get('deleted') ? '<div class="alert-success">Product deleted successfully.</div>' : ''}
    <div class="filters-inline">
      <input id="productSearch" type="text" placeholder="Search products" value="${escapeHtml(search)}" />
      <select id="productStatusFilter">
        <option value="" ${!statusFilter ? 'selected' : ''}>All statuses</option>
        <option value="active" ${statusFilter === 'active' ? 'selected' : ''}>Active</option>
        <option value="draft" ${statusFilter === 'draft' ? 'selected' : ''}>Draft</option>
        <option value="archived" ${statusFilter === 'archived' ? 'selected' : ''}>Archived</option>
      </select>
      <select id="productCategoryFilter">
        <option value="" ${!categoryFilter ? 'selected' : ''}>All categories</option>
        ${categories.map((category) => `<option value="${escapeHtml(category.slug)}" ${categoryFilter === category.slug ? 'selected' : ''}>${escapeHtml(category.name)}</option>`).join('')}
      </select>
    </div>
    <table>
      <thead><tr><th>Product</th><th>Category</th><th>Status</th><th>Stock</th><th>Price</th><th>Actions</th></tr></thead>
      <tbody>
        ${filtered.length ? filtered.map((product) => `
          <tr>
            <td>${escapeHtml(product.name)}</td>
            <td>${escapeHtml(product.category_name)}</td>
            <td><span class="badge ${product.status === 'active' ? 'badge-active' : 'badge-draft'}">${escapeHtml(product.status || 'draft')}</span></td>
            <td>${Number(product.stock || 0)}</td>
            <td>${formatCurrency(product.price || 0)}</td>
            <td>
              <div class="row-actions">
                <a class="action-btn primary" href="product-form.html?id=${product.id}">Edit</a>
                <button class="action-btn danger" type="button" data-delete-id="${product.id}">Delete</button>
              </div>
            </td>
          </tr>
        `).join('') : '<tr><td colspan="6" class="empty-state">No products match your filters.</td></tr>'}
      </tbody>
    </table>
  `;
  renderShell('products', 'Products', content);

  document.getElementById('productSearch')?.addEventListener('input', (event) => {
    const value = event.target.value;
    const params = new URLSearchParams(window.location.search);
    params.set('q', value);
    history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
    renderProductsPage();
  });

  document.getElementById('productStatusFilter')?.addEventListener('change', (event) => {
    const params = new URLSearchParams(window.location.search);
    params.set('status', event.target.value);
    history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
    renderProductsPage();
  });

  document.getElementById('productCategoryFilter')?.addEventListener('change', (event) => {
    const params = new URLSearchParams(window.location.search);
    params.set('category', event.target.value);
    history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
    renderProductsPage();
  });

  document.querySelectorAll('[data-delete-id]').forEach((button) => {
    button.addEventListener('click', () => {
      const id = Number(button.getAttribute('data-delete-id'));
      if (!confirm('Delete this product?')) return;
      const nextProducts = loadProducts().filter((product) => product.id !== id);
      saveProducts(nextProducts);
      window.location.href = 'products.html?deleted=1';
    });
  });

  document.getElementById('deleteAllProductsButton')?.addEventListener('click', () => {
    if (!confirm('Delete all products from the catalog? This cannot be undone.')) return;
    saveProducts([]);
    window.location.href = 'products.html?deleted=1';
  });
}

function renderProductFormPage() {
  const products = loadProducts();
  const params = new URLSearchParams(window.location.search);
  const editId = Number(params.get('id'));
  const product = products.find((item) => item.id === editId) || null;
  const categories = getCategories(products);
  const categoryOptions = categories.map((category) => `
    <option value="${escapeHtml(category.slug)}" data-name="${escapeHtml(category.name)}" ${product?.category_slug === category.slug ? 'selected' : ''}>${escapeHtml(category.name)}</option>
  `).join('');
  const content = `
    <div class="form-card">
      <form id="productForm">
        <input type="hidden" id="productId" value="${product?.id || ''}" />
        <div class="form-grid">
          <div><label>Name</label><input id="productName" type="text" value="${escapeHtml(product?.name || '')}" required /></div>
          <div><label>Slug</label><input id="productSlug" type="text" value="${escapeHtml(product?.slug || '')}" /></div>
          <div><label>Price</label><input id="productPrice" type="number" step="0.01" value="${Number(product?.price || 0)}" required /></div>
          <div><label>Compare Price</label><input id="productComparePrice" type="number" step="0.01" value="${product?.compare_price != null ? Number(product.compare_price) : ''}" /></div>
          <div><label>Category</label><select id="productCategory">${categoryOptions}</select></div>
          <div><label>New category (optional)</label><input id="productNewCategory" type="text" placeholder="Create a new category name" /></div>
          <div><label>Gender</label><select id="productGender"><option value="unisex" ${product?.gender === 'unisex' ? 'selected' : ''}>Unisex</option><option value="male" ${product?.gender === 'male' ? 'selected' : ''}>Male</option><option value="female" ${product?.gender === 'female' ? 'selected' : ''}>Female</option></select></div>
          <div><label>Stock</label><input id="productStock" type="number" value="${Number(product?.stock || 0)}" /></div>
          <div><label>Status</label><select id="productStatus"><option value="active" ${product?.status === 'active' ? 'selected' : ''}>Active</option><option value="draft" ${product?.status === 'draft' ? 'selected' : ''}>Draft</option><option value="archived" ${product?.status === 'archived' ? 'selected' : ''}>Archived</option></select></div>
          <div><label>Sizes</label><input id="productSizes" type="text" value="${escapeHtml((product?.sizes || []).join(', '))}" /></div>
          <div><label>Colors</label><input id="productColors" type="text" value="${escapeHtml((product?.colors || []).join(', '))}" /></div>
          <div><label>Featured</label><select id="productFeatured"><option value="1" ${product?.is_featured ? 'selected' : ''}>Yes</option><option value="0" ${!product?.is_featured ? 'selected' : ''}>No</option></select></div>
          <div><label>New</label><select id="productIsNew"><option value="1" ${product?.is_new ? 'selected' : ''}>Yes</option><option value="0" ${!product?.is_new ? 'selected' : ''}>No</option></select></div>
          <div class="full"><label>Description</label><textarea id="productDescription" rows="4">${escapeHtml(product?.description || '')}</textarea></div>
          <div class="full"><label>Primary image URL</label><input id="productImage" type="text" value="${escapeHtml(product?.image || '')}" /></div>
          <div class="full"><label>Upload images from your device</label><input id="productImageFiles" type="file" accept="image/*" multiple /></div>
          <div class="full"><label>Additional image URLs</label><textarea id="productImages" rows="3">${escapeHtml((product?.images || []).join(', '))}</textarea></div>
          <div class="full"><div id="imagePreviewList" class="image-manager"></div></div>
        </div>
        <div class="row-actions" style="margin-top:16px;">
          <button class="btn" type="submit">${product ? 'Save product' : 'Add product'}</button>
          <a class="btn btn-outline" href="products.html">Cancel</a>
          ${product ? '<button class="btn btn-danger" type="button" id="deleteProductButton">Delete</button>' : ''}
        </div>
      </form>
    </div>
  `;
  renderShell('product-form', product ? 'Edit Product' : 'Add Product', content);
  pendingProductImageUrls = (product?.images || []).filter(Boolean);
  renderImagePreviewList(pendingProductImageUrls);

  document.getElementById('productImageFiles')?.addEventListener('change', async (event) => {
    const files = Array.from(event.target.files || []);
    if (!files.length) return;
    const urls = [];
    for (const file of files) {
      urls.push(await readFileAsDataUrl(file));
    }
    pendingProductImageUrls = urls;
    renderImagePreviewList([...urls, ...parseList(document.getElementById('productImages')?.value || '')]);
  });

  document.getElementById('productForm')?.addEventListener('submit', (event) => {
    event.preventDefault();
    const products = loadProducts();
    const form = event.target;
    const id = Number(document.getElementById('productId').value || Date.now());
    const categorySelect = document.getElementById('productCategory');
    const newCategoryInput = document.getElementById('productNewCategory');
    const categoryName = (newCategoryInput?.value || '').trim() || categorySelect.selectedOptions[0]?.getAttribute('data-name') || 'Uncategorized';
    const categorySlug = (newCategoryInput?.value || '').trim() ? slugify(newCategoryInput.value) : categorySelect.value || slugify(categoryName);
    const existing = products.find((item) => item.id === id);
    const imageState = collectProductImageState(existing || null);
    const nextProduct = normalizeProduct({
      ...existing,
      id,
      category_id: existing?.category_id || id,
      category_name: categoryName,
      category_slug: categorySlug,
      name: document.getElementById('productName').value.trim(),
      slug: document.getElementById('productSlug').value.trim() || slugify(document.getElementById('productName').value),
      description: document.getElementById('productDescription').value.trim(),
      price: Number(document.getElementById('productPrice').value || 0),
      compare_price: document.getElementById('productComparePrice').value ? Number(document.getElementById('productComparePrice').value) : null,
      gender: document.getElementById('productGender').value,
      sizes: parseList(document.getElementById('productSizes').value),
      colors: parseList(document.getElementById('productColors').value),
      stock: Number(document.getElementById('productStock').value || 0),
      status: document.getElementById('productStatus').value,
      is_featured: Number(document.getElementById('productFeatured').value || 0),
      is_new: Number(document.getElementById('productIsNew').value || 0),
      image: document.getElementById('productImage').value.trim() || imageState.image,
      images: imageState.images,
    }, id);

    const filteredProducts = products.filter((item) => item.id !== id);
    saveProducts([...filteredProducts, nextProduct]);
    window.location.href = 'products.html?saved=1';
  });

  document.getElementById('deleteProductButton')?.addEventListener('click', () => {
    if (!confirm('Delete this product permanently?')) return;
    const productId = Number(document.getElementById('productId').value);
    const nextProducts = loadProducts().filter((item) => item.id !== productId);
    saveProducts(nextProducts);
    window.location.href = 'products.html?deleted=1';
  });
}

function renderPage() {
  const pageKey = getPageKey();
  if (pageKey === 'index') {
    renderDashboard();
    return;
  }
  if (pageKey === 'products') {
    renderProductsPage();
    return;
  }
  if (pageKey === 'product-form') {
    renderProductFormPage();
    return;
  }
  if (pageKey === 'login') {
    renderShell('login', 'Admin Login', `
      <div class="login-body">
        <div class="login-card">
          <div class="login-mark">FAAF COLLECTIONS</div>
          <h1>Local admin access</h1>
          <p class="muted">This preview uses static HTML files so the admin can open locally without PHP.</p>
          <form>
            <label for="username">Username</label>
            <input id="username" type="text" value="local-preview" />
            <label for="password">Password</label>
            <input id="password" type="password" value="demo" />
            <button type="button" onclick="window.location='index.html'">Enter Admin</button>
          </form>
        </div>
      </div>
    `);
    return;
  }

  const placeholders = {
    orders: { title: 'Orders', content: '<div class="form-card"><h3 style="margin-top:0;">Orders</h3><p class="muted">Local order management placeholder.</p></div>' },
    categories: { title: 'Categories', content: '<div class="form-card"><h3 style="margin-top:0;">Categories</h3><p class="muted">Local category management placeholder.</p></div>' },
    customers: { title: 'Customers', content: '<div class="form-card"><h3 style="margin-top:0;">Customers</h3><p class="muted">Local customer list placeholder.</p></div>' },
    coupons: { title: 'Coupons', content: '<div class="form-card"><h3 style="margin-top:0;">Coupons</h3><p class="muted">Local coupon management placeholder.</p></div>' },
    reviews: { title: 'Reviews', content: '<div class="form-card"><h3 style="margin-top:0;">Reviews</h3><p class="muted">Local review moderation placeholder.</p></div>' },
    settings: { title: 'Settings', content: '<div class="form-card"><h3 style="margin-top:0;">Settings</h3><p class="muted">Local settings placeholder.</p></div>' },
    users: { title: 'Admin Users', content: '<div class="form-card"><h3 style="margin-top:0;">Admin Users</h3><p class="muted">Local admin user placeholder.</p></div>' },
    'activity-log': { title: 'Activity Log', content: '<div class="form-card"><h3 style="margin-top:0;">Activity Log</h3><p class="muted">Local activity log placeholder.</p></div>' },
    'import-products': { title: 'Import Products', content: '<div class="form-card"><h3 style="margin-top:0;">Import Products</h3><p class="muted">Local import placeholder.</p></div>' },
    account: { title: 'My Account', content: '<div class="form-card"><h3 style="margin-top:0;">My Account</h3><p class="muted">Local account placeholder.</p></div>' },
    logout: { title: 'Log out', content: '<div class="form-card"><h3 style="margin-top:0;">Logged out</h3><p class="muted">Return to the local login page to continue.</p><a class="btn" href="login.html">Back to login</a></div>' },
    invoice: { title: 'Invoice', content: '<div class="form-card"><h3 style="margin-top:0;">Invoice</h3><p class="muted">Local invoice preview placeholder.</p></div>' },
    'export-orders': { title: 'Export Orders', content: '<div class="form-card"><h3 style="margin-top:0;">Export Orders</h3><p class="muted">Local export placeholder.</p></div>' },
  };
  const placeholder = placeholders[pageKey] || { title: 'Admin', content: '<div class="form-card"><h3 style="margin-top:0;">Local admin page</h3><p class="muted">This page is available from the static preview.</p></div>' };
  renderShell(pageKey, placeholder.title, placeholder.content);
}

function loadDemoDataScript() {
  const script = document.createElement('script');
  script.src = '../assets/js/demo-data.js';
  script.onload = renderPage;
  script.onerror = renderPage;
  document.head.appendChild(script);
}

if (window.FAAF_DEMO_DATA) {
  renderPage();
} else {
  loadDemoDataScript();
}
