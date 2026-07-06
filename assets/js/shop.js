/* =====================================================
   FAAF Collections & Souvenirs — shop.js
   ===================================================== */

let allCategories = [];
let currentPage = 1;
const SHOP_PAGE_SIZE = 12;
let currentFilters = {
  category: '',
  gender: '',
  q: '',
  min_price: '',
  max_price: '',
  sort: 'newest',
};

function listify(value) {
  if (Array.isArray(value)) return value.filter(Boolean);
  if (value == null) return [];
  if (typeof value === 'string') return value.split(',').map(v => v.trim()).filter(Boolean);
  return [value].filter(Boolean);
}

function readFiltersFromURL() {
  const params = new URLSearchParams(window.location.search);
  currentFilters.category = params.get('category') || '';
  currentFilters.q = params.get('q') || '';
  currentFilters.gender = params.get('gender') || '';
}

function buildQuery() {
  const params = new URLSearchParams();
  Object.entries(currentFilters).forEach(([k, v]) => { if (v) params.set(k, v); });
  params.set('page', currentPage);
  params.set('per_page', SHOP_PAGE_SIZE);
  return params.toString();
}

async function loadCategoryFilters() {
  const { categories } = await apiGet('/categories.php');
  allCategories = categories;
  const wrap = document.querySelector('#categoryFilters');
  wrap.innerHTML = `
    <label class="filter-option ${!currentFilters.category ? 'active-label' : ''}">
      <span><input type="radio" name="categoryFilter" value="" ${!currentFilters.category ? 'checked' : ''}> All Categories</span>
    </label>
    ${categories.map(c => `
      <label class="filter-option ${currentFilters.category === c.slug ? 'active-label' : ''}">
        <span><input type="radio" name="categoryFilter" value="${c.slug}" ${currentFilters.category === c.slug ? 'checked' : ''}> ${escapeHtml(c.name)}</span>
        <span class="fc">${c.product_count}</span>
      </label>
    `).join('')}
  `;
  wrap.querySelectorAll('input[name="categoryFilter"]').forEach(input => {
    input.addEventListener('change', () => {
      currentFilters.category = input.value;
      currentPage = 1;
      loadProducts();
      renderChips();
    });
  });
}

function renderChips() {
  const chips = [];
  if (currentFilters.category) {
    const cat = allCategories.find(c => c.slug === currentFilters.category);
    chips.push({ key: 'category', label: cat ? cat.name : currentFilters.category });
  }
  if (currentFilters.gender) chips.push({ key: 'gender', label: currentFilters.gender.charAt(0).toUpperCase() + currentFilters.gender.slice(1) });
  if (currentFilters.q) chips.push({ key: 'q', label: `"${currentFilters.q}"` });
  if (currentFilters.min_price || currentFilters.max_price) chips.push({ key: 'price', label: `₦${currentFilters.min_price || 0} – ₦${currentFilters.max_price || '∞'}` });

  const wrap = document.querySelector('#activeChips');
  wrap.innerHTML = chips.map(c => `<div class="chip">${escapeHtml(c.label)} <span onclick="removeFilter('${c.key}')">✕</span></div>`).join('');
}

function removeFilter(key) {
  if (key === 'price') { currentFilters.min_price = ''; currentFilters.max_price = ''; document.querySelector('#minPrice').value = ''; document.querySelector('#maxPrice').value = ''; }
  else currentFilters[key] = '';
  currentPage = 1;
  loadCategoryFilters();
  loadProducts();
  renderChips();
}

function clearAllFilters() {
  currentFilters = { category: '', gender: '', q: '', min_price: '', max_price: '', sort: 'newest' };
  currentPage = 1;
  document.querySelector('#minPrice').value = '';
  document.querySelector('#maxPrice').value = '';
  document.querySelector('#shopSearchInput').value = '';
  document.querySelector('#sortSelect').value = 'newest';
  document.querySelectorAll('input[name="genderFilter"]')[0].checked = true;
  loadCategoryFilters();
  loadProducts();
  renderChips();
}

function renderSkeletons() {
  const grid = document.querySelector('#shopGrid');
  grid.innerHTML = Array.from({ length: 8 }).map(() => `<div class="skeleton"></div>`).join('');
}

async function loadProducts() {
  renderSkeletons();
  const grid = document.querySelector('#shopGrid');
  const countEl = document.querySelector('#resultCount');
  try {
    const { products, total, total_pages, page } = await apiGet(`/products.php?${buildQuery()}`);
    countEl.textContent = `${total} product${total === 1 ? '' : 's'} found`;
    if (!products.length) {
      grid.innerHTML = `<div class="shop-empty">No products match your filters yet. Try clearing a filter, or check back soon — new stock is added regularly.</div>`;
      renderPagination(0, 1);
      return;
    }
    lastLoadedProducts = products;
    grid.innerHTML = products.map(productCardHTML).join('');
    renderPagination(total_pages, page);
    window.scrollTo({ top: document.querySelector('.shop-toolbar').offsetTop - 100, behavior: currentPage === 1 ? 'auto' : 'smooth' });
  } catch (e) {
    countEl.textContent = 'Could not load products';
    grid.innerHTML = `<div class="shop-empty">We couldn't load products right now. Please refresh the page.</div>`;
  }
}

function renderPagination(totalPages, page) {
  const wrap = document.querySelector('#shopPagination');
  if (!wrap) return;
  if (totalPages <= 1) { wrap.innerHTML = ''; return; }

  let buttons = '';
  const addBtn = (n) => { buttons += `<button class="${n === page ? 'active' : ''}" onclick="goToPage(${n})">${n}</button>`; };

  addBtn(1);
  if (page > 3) buttons += `<span style="padding:0 4px;color:var(--muted);">…</span>`;
  for (let n = Math.max(2, page - 1); n <= Math.min(totalPages - 1, page + 1); n++) addBtn(n);
  if (page < totalPages - 2) buttons += `<span style="padding:0 4px;color:var(--muted);">…</span>`;
  if (totalPages > 1) addBtn(totalPages);

  wrap.innerHTML = buttons;
}

function goToPage(n) {
  currentPage = n;
  loadProducts();
}

function productCardHTML(p) {
  const img = p.image || `https://placehold.co/600x740/c9a227/15130f?text=${encodeURIComponent(p.name)}`;
  const altImg = p.images && p.images.length > 1 ? p.images[1] : '';
  const oos = p.stock !== undefined && p.stock <= 0;
  const lowStock = p.stock !== undefined && p.stock > 0 && p.stock <= 5;
  const wished = isWishlisted(p.id);
  return `
  <div class="product-card">
    <a href="product.html?slug=${p.slug}" class="frame ${oos ? 'is-oos' : ''}">
      <img class="main" src="${img}" alt="${escapeHtml(p.name)}">
      ${altImg ? `<img class="alt" src="${altImg}" alt="${escapeHtml(p.name)} alternate view">` : ''}
      ${p.is_new && !oos ? `<div class="product-tags"><span class="tag tag-new">New</span></div>` : ''}
      ${oos ? `<div class="product-tags"><span class="tag tag-oos">Sold Out</span></div>` : (p.compare_price ? `<div class="product-tags" style="left:auto;right:12px;"><span class="tag tag-sale">Sale</span></div>` : '')}
      ${!oos ? `
      <div class="product-quick">
        <button class="qa-btn" onclick="event.preventDefault();openQuickView(${p.id})">Quick View</button>
        <button class="qa-btn icon-only" onclick="event.preventDefault();quickAddToCart(${p.id},'${escapeHtml(p.name).replace(/'/g, "\\'")}',${p.price},'${img}',null,null,this)" aria-label="Add to cart">＋</button>
      </div>` : ''}
    </a>
    <button class="wishlist-heart ${wished ? 'active' : ''}" onclick="event.preventDefault();toggleWishlist({product_id:${p.id},name:'${escapeHtml(p.name).replace(/'/g, "\\'")}',price:${p.price},image:'${img}',slug:'${p.slug}'},this)" aria-label="Save to wishlist">
      <svg viewBox="0 0 24 24" stroke-width="1.8"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
    </button>
    <div class="product-info">
      <div class="cat">${escapeHtml(p.category_name)}</div>
      <a href="product.html?slug=${p.slug}"><h3>${escapeHtml(p.name)}</h3></a>
      <div class="price">
        <span class="now">${formatCurrency(p.price)}</span>
        ${p.compare_price ? `<span class="was">${formatCurrency(p.compare_price)}</span>` : ''}
      </div>
      ${lowStock ? `<div class="low-stock-hint">Only ${p.stock} left</div>` : ''}
    </div>
  </div>`;
}

function quickAddToCart(id, name, price, image, size, color, originEl) {
  if (originEl) {
    const img = originEl.closest('.product-card')?.querySelector('img.main');
    if (img) flyToCart(img);
  }
  addToCart({ product_id: id, name, price, image, size: size || null, color: color || null, quantity: 1 });
}

// ---------- Quick View ----------
let qvProduct = null;
let qvSelectedSize = null;
let qvSelectedColor = null;
let lastLoadedProducts = [];

async function openQuickView(id) {
  const modal = document.querySelector('#quickViewModal');
  const content = document.querySelector('#quickViewContent');
  content.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:60px;"><span class="spinner"></span></div>`;
  modal.classList.add('open');
  document.body.style.overflow = 'hidden';

  try {
    const detail = await fetchProductDetailById(id);
    if (!detail) { content.innerHTML = `<div style="grid-column:1/-1;padding:40px;">Could not load product.</div>`; return; }
    qvProduct = detail;
    const sizes = listify(detail.sizes);
    const colors = listify(detail.colors);
    qvSelectedSize = sizes[0] || null;
    qvSelectedColor = colors[0] || null;
    renderQuickView();
  } catch (e) {
    content.innerHTML = `<div style="grid-column:1/-1;padding:40px;">Could not load product. ${escapeHtml(e.message)}</div>`;
  }
}

async function fetchProductDetailById(id) {
  let match = lastLoadedProducts.find(p => p.id === id);
  if (!match) {
    const { products } = await apiGet('/products.php?sort=newest&per_page=100');
    match = products.find(p => p.id === id);
  }
  if (!match) return null;
  const { product } = await apiGet(`/products.php?action=detail&slug=${match.slug}`);
  return product;
}

function renderQuickView() {
  const p = qvProduct;
  const content = document.querySelector('#quickViewContent');
  const images = listify(p.images);
  const sizes = listify(p.sizes);
  const colors = listify(p.colors);
  const displayImages = images.length ? images : [`https://placehold.co/600x740/c9a227/15130f?text=${encodeURIComponent(p.name)}`];
  content.innerHTML = `
    <div>
      <img id="qvMainImg" src="${displayImages[0]}" alt="${escapeHtml(p.name)}">
      ${displayImages.length > 1 ? `<div class="qv-thumbs">${displayImages.map((img, i) => `<img src="${img}" class="${i === 0 ? 'active' : ''}" onclick="switchQvImage('${img}', this)">`).join('')}</div>` : ''}
    </div>
    <div>
      <div class="cat" style="font-family:var(--font-mono);font-size:11px;text-transform:uppercase;color:var(--muted);">${escapeHtml(p.category_name)}</div>
      <h3 style="font-size:24px;margin:6px 0 10px;">${escapeHtml(p.name)}</h3>
      <div class="price" style="margin-bottom:18px;"><span class="now" style="font-size:20px;">${formatCurrency(p.price)}</span> ${p.compare_price ? `<span class="was">${formatCurrency(p.compare_price)}</span>` : ''}</div>
      <p style="font-size:14px;color:var(--muted);line-height:1.7;margin-bottom:20px;">${escapeHtml(p.description || 'A FAAF Collections favorite — quality fabric, comfortable fit, made to last.')}</p>

      ${sizes.length ? `
      <label style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px;display:block;">Size</label>
      <div class="size-row">${sizes.map(s => `<div class="swatch ${s === qvSelectedSize ? 'active' : ''}" onclick="selectQvOption('size','${s}',this)">${escapeHtml(s)}</div>`).join('')}</div>` : ''}

      ${colors.length ? `
      <label style="font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px;display:block;">Color</label>
      <div class="color-row">${colors.map(c => `<div class="swatch ${c === qvSelectedColor ? 'active' : ''}" onclick="selectQvOption('color','${c}',this)">${escapeHtml(c)}</div>`).join('')}</div>` : ''}

      <button class="btn btn-gold btn-block" style="margin-top:14px;" onclick="addQvToCart()">Add to Bag — ${formatCurrency(p.price)}</button>
      <a href="product.html?slug=${p.slug}" class="btn btn-outline btn-block" style="margin-top:10px;">View Full Details</a>
    </div>
  `;
}

function switchQvImage(src, el) {
  document.querySelector('#qvMainImg').src = src;
  el.parentElement.querySelectorAll('img').forEach(i => i.classList.remove('active'));
  el.classList.add('active');
}

function selectQvOption(type, value, el) {
  if (type === 'size') qvSelectedSize = value;
  if (type === 'color') qvSelectedColor = value;
  el.parentElement.querySelectorAll('.swatch').forEach(s => s.classList.remove('active'));
  el.classList.add('active');
}

function addQvToCart() {
  const p = qvProduct;
  const img = p.images[0] || `https://placehold.co/600x740/c9a227/15130f?text=${encodeURIComponent(p.name)}`;
  const qvImgEl = document.querySelector('#qvMainImg');
  if (qvImgEl) flyToCart(qvImgEl);
  addToCart({ product_id: p.id, name: p.name, price: parseFloat(p.price), image: img, size: qvSelectedSize, color: qvSelectedColor, quantity: 1 });
  document.querySelector('#quickViewModal').classList.remove('open');
  document.body.style.overflow = '';
}

// ---------- Init ----------
let shopInitialized = false;

async function initShopPage() {
  if (shopInitialized) return;
  const grid = document.querySelector('#shopGrid');
  const searchInput = document.querySelector('#shopSearchInput');
  const sortSelect = document.querySelector('#sortSelect');
  if (!grid || !searchInput || !sortSelect) return;
  shopInitialized = true;

  readFiltersFromURL();
  searchInput.value = currentFilters.q;
  if (currentFilters.gender) {
    const radio = document.querySelector(`input[name="genderFilter"][value="${currentFilters.gender}"]`);
    if (radio) radio.checked = true;
  }

  await loadCategoryFilters();
  await loadProducts();
  renderChips();

  document.querySelectorAll('input[name="genderFilter"]').forEach(input => {
    input.addEventListener('change', () => { currentFilters.gender = input.value; currentPage = 1; loadProducts(); renderChips(); });
  });

  let searchTimer;
  searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentFilters.q = e.target.value.trim(); currentPage = 1; loadProducts(); renderChips(); }, 450);
  });

  sortSelect.addEventListener('change', (e) => { currentFilters.sort = e.target.value; currentPage = 1; loadProducts(); });

  let priceTimer;
  ['minPrice', 'maxPrice'].forEach(id => {
    document.querySelector(`#${id}`).addEventListener('input', () => {
      clearTimeout(priceTimer);
      priceTimer = setTimeout(() => {
        currentFilters.min_price = document.querySelector('#minPrice').value;
        currentFilters.max_price = document.querySelector('#maxPrice').value;
        currentPage = 1;
        loadProducts(); renderChips();
      }, 500);
    });
  });

  document.querySelector('#quickViewModal').addEventListener('click', (e) => {
    if (e.target.id === 'quickViewModal') { e.target.classList.remove('open'); document.body.style.overflow = ''; }
  });
}

document.addEventListener('partialsReady', initShopPage);
document.addEventListener('DOMContentLoaded', () => setTimeout(initShopPage, 0));
