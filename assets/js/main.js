/* =====================================================
   FAAF Collections & Souvenirs — main.js
   Shared site behavior: header, mobile nav, search, reveal-on-scroll
   ===================================================== */

const API_BASE = '/api';

// ---------- Header scroll state ----------
window.addEventListener('scroll', () => {
  const header = document.querySelector('.site-header');
  if (!header) return;
  header.classList.toggle('scrolled', window.scrollY > 12);
}, { passive: true });

// ---------- Mobile nav ----------
function toggleMobileNav(open) {
  const nav = document.querySelector('.mobile-nav');
  const dim = document.querySelector('.mobile-nav-dim');
  if (!nav) return;
  nav.classList.toggle('open', open);
  if (dim) dim.classList.toggle('open', open);
  document.body.style.overflow = open ? 'hidden' : '';
}

// ---------- Search overlay ----------
function toggleSearch(open) {
  const overlay = document.querySelector('.search-overlay');
  if (!overlay) return;
  overlay.classList.toggle('open', open);
  if (open) setTimeout(() => overlay.querySelector('input')?.focus(), 200);
}

function submitSiteSearch(e) {
  e.preventDefault();
  const q = document.querySelector('#siteSearchInput')?.value?.trim();
  if (q) window.location.href = `shop.html?q=${encodeURIComponent(q)}`;
  return false;
}

// ---------- Scroll reveal ----------
function initReveal() {
  const els = document.querySelectorAll('.reveal');
  if (!('IntersectionObserver' in window) || !els.length) {
    els.forEach(el => el.classList.add('in'));
    return;
  }
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  els.forEach(el => io.observe(el));
}

// ---------- Toast ----------
let toastTimer;
function showToast(message) {
  let toast = document.querySelector('.toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<span class="dot"></span><span class="msg"></span>`;
    document.body.appendChild(toast);
  }
  toast.querySelector('.msg').textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
}

// ---------- Helpers ----------
function formatCurrency(n) {
  return '₦' + Number(n).toLocaleString('en-NG', { maximumFractionDigits: 0 });
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function canUseStaticDemoApi() {
  const host = window.location.hostname;
  const params = new URLSearchParams(window.location.search);
  const isPrivatePreviewHost =
    host === '' ||
    host.startsWith('192.168.') ||
    host.startsWith('10.') ||
    /^172\.(1[6-9]|2\d|3[0-1])\./.test(host);

  return Boolean(window.FAAF_DEMO_DATA) && (
    window.location.protocol === 'file:' ||
    host === 'localhost' ||
    host === '127.0.0.1' ||
    isPrivatePreviewHost ||
    host.endsWith('.local') ||
    params.get('demo') === '1'
  );
}

function cloneDemoData(value) {
  return JSON.parse(JSON.stringify(value));
}

function deriveCategoriesFromProducts(products) {
  const map = new Map();
  (products || []).forEach((product) => {
    const slug = product.category_slug || (product.category_name ? product.category_name.toLowerCase().replace(/[^a-z0-9]+/g, '-') : 'uncategorized');
    const name = product.category_name || 'Uncategorized';
    if (!map.has(slug)) {
      map.set(slug, { id: map.size + 1, name, slug, gender: product.gender || 'unisex', sort_order: map.size + 1, product_count: 0 });
    }
    map.get(slug).product_count += 1;
  });
  return Array.from(map.values());
}

function getCurrentDemoCatalog() {
  const fallback = window.FAAF_DEMO_DATA || { products: [], categories: [] };
  let products = fallback.products || [];
  let categories = fallback.categories || [];

  try {
    const saved = localStorage.getItem('faaf_admin_products_v1');
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length) {
        products = parsed;
        categories = deriveCategoriesFromProducts(parsed);
      }
    }
  } catch (err) {
    console.warn('Unable to load updated storefront catalog.', err);
  }

  return { ...fallback, products, categories };
}

function demoProductList(params) {
  const catalog = getCurrentDemoCatalog();
  let products = catalog.products.filter(p => p.status === 'active');

  if (params.get('category')) {
    products = products.filter(p => p.category_slug === params.get('category'));
  }
  if (params.get('gender') && ['male', 'female', 'unisex'].includes(params.get('gender'))) {
    const gender = params.get('gender');
    products = products.filter(p => p.gender === gender || p.gender === 'unisex');
  }
  if (params.get('q')) {
    const q = params.get('q').trim().toLowerCase();
    products = products.filter(p => `${p.name} ${p.description}`.toLowerCase().includes(q));
  }
  if (params.get('featured')) products = products.filter(p => Number(p.is_featured) === 1);
  if (params.get('min_price')) products = products.filter(p => Number(p.price) >= Number(params.get('min_price')));
  if (params.get('max_price')) products = products.filter(p => Number(p.price) <= Number(params.get('max_price')));

  switch (params.get('sort') || 'newest') {
    case 'price_asc':
      products.sort((a, b) => Number(a.price) - Number(b.price));
      break;
    case 'price_desc':
      products.sort((a, b) => Number(b.price) - Number(a.price));
      break;
    case 'name_asc':
      products.sort((a, b) => a.name.localeCompare(b.name));
      break;
    case 'newest':
    default:
      products.sort((a, b) => Number(b.id) - Number(a.id));
      break;
  }

  const perPage = Math.max(1, Math.min(100, Number(params.get('per_page')) || 12));
  const page = Math.max(1, Number(params.get('page')) || 1);
  const total = products.length;
  const start = (page - 1) * perPage;
  const paged = products.slice(start, start + perPage);

  return {
    products: cloneDemoData(paged),
    count: paged.length,
    total,
    page,
    per_page: perPage,
    total_pages: Math.max(1, Math.ceil(total / perPage)),
  };
}

function demoApiGet(path) {
  const url = new URL(path, 'https://faaf.local');
  const endpoint = url.pathname.split('/').pop();
  const params = url.searchParams;

  const catalog = getCurrentDemoCatalog();

  if (endpoint === 'categories.php') {
    return { categories: cloneDemoData(catalog.categories) };
  }

  if (endpoint === 'products.php') {
    if (params.get('action') === 'detail') {
      const product = catalog.products.find(p => p.slug === params.get('slug') && p.status === 'active');
      if (!product) throw new Error('Product not found');
      return { product: cloneDemoData(product) };
    }
    return demoProductList(params);
  }

  if (endpoint === 'reviews.php') {
    return { reviews: [], average_rating: 0, count: 0 };
  }

  if (endpoint === 'distance.php') {
    const subtotal = Number(params.get('subtotal')) || 0;
    const fee = subtotal >= 75000 ? 0 : 2500;
    return {
      distance_km: '8.0',
      delivery_fee: fee,
      free_delivery_applied: fee === 0,
      matched_address: params.get('address') || 'Demo address',
      lat: null,
      lng: null,
      note: 'Demo delivery quote for local preview.',
    };
  }

  throw new Error('Demo API route not available');
}

async function apiGet(path) {
  if (canUseStaticDemoApi()) return demoApiGet(path);

  try {
    const res = await fetch(API_BASE + path);
    if (!res.ok) {
      const err = await res.json().catch(() => ({ error: 'Something went wrong.' }));
      throw new Error(err.error || 'Request failed');
    }
    return res.json();
  } catch (err) {
    if (canUseStaticDemoApi()) return demoApiGet(path);
    throw err;
  }
}

function demoApiPost(path, body) {
  const url = new URL(path, 'https://faaf.local');
  const endpoint = url.pathname.split('/').pop();

  if (endpoint === 'reviews.php') {
    return { message: 'Demo review received. It will not be saved in static preview.' };
  }

  if (endpoint === 'coupons.php') {
    const code = String(body.code || '').trim().toUpperCase();
    if (code !== 'DEMO10') throw new Error('Use DEMO10 for a local preview discount.');
    return {
      code,
      discount_amount: Math.round((Number(body.subtotal) || 0) * 0.1),
    };
  }

  if (endpoint === 'orders.php') {
    const subtotal = (body.items || []).reduce((sum, item) => sum + Number(item.unit_price || 0) * Number(item.quantity || 1), 0);
    const deliveryFee = Number(body.delivery_fee || 0);
    const discount = Number(body.discount_amount || 0);
    const total = Math.max(0, subtotal + deliveryFee - discount);
    const ref = `DEMO-${Date.now().toString().slice(-6)}`;
    const text = `Hello FAAF Collections! I am testing a demo order. Ref: ${ref}. Total: ${formatCurrency(total)}.`;
    return {
      order_ref: ref,
      total,
      whatsapp_url: `https://wa.me/2349048239391?text=${encodeURIComponent(text)}`,
    };
  }

  throw new Error('Demo API route not available');
}

async function apiPost(path, body) {
  if (canUseStaticDemoApi()) return demoApiPost(path, body);

  try {
    const res = await fetch(API_BASE + path, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
  } catch (err) {
    if (canUseStaticDemoApi()) return demoApiPost(path, body);
    throw err;
  }
}

// ---------- Scroll progress bar ----------
function initScrollProgress() {
  const bar = document.createElement('div');
  bar.className = 'scroll-progress-bar';
  document.body.appendChild(bar);
  window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docHeight = document.documentElement.scrollHeight - window.innerHeight;
    const pct = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
    bar.style.width = pct + '%';
  }, { passive: true });
}

// ---------- Back to top ----------
function initBackToTop() {
  const btn = document.createElement('button');
  btn.className = 'back-to-top';
  btn.setAttribute('aria-label', 'Back to top');
  btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19V5M5 12l7-7 7 7"/></svg>`;
  document.body.appendChild(btn);

  const updateVisibility = () => {
    const threshold = window.innerWidth <= 760 ? 180 : 600;
    btn.classList.toggle('show', window.scrollY > threshold);
  };

  updateVisibility();
  window.addEventListener('scroll', updateVisibility, { passive: true });
  window.addEventListener('resize', updateVisibility);
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

// ---------- Analytics (opt-in via meta tag) ----------
function initAnalytics() {
  const meta = document.querySelector('meta[name="ga-measurement-id"]');
  const gaId = meta?.content?.trim();
  if (!gaId) return; // no ID configured — do nothing, no external requests made

  const script1 = document.createElement('script');
  script1.async = true;
  script1.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
  document.head.appendChild(script1);

  window.dataLayer = window.dataLayer || [];
  function gtag() { dataLayer.push(arguments); }
  gtag('js', new Date());
  gtag('config', gaId);
}

// ---------- Floating WhatsApp button ----------
function initFloatingWhatsapp() {
  // Skip on pages where a WhatsApp CTA is already the primary action (checkout/confirmation flows are fine to keep it on too, but avoid on admin — this file isn't loaded there anyway)
  const btn = document.createElement('a');
  btn.className = 'float-whatsapp';
  btn.href = 'https://wa.me/2349048239391?text=' + encodeURIComponent('Hi FAAF Collections! I have a question.');
  btn.target = '_blank';
  btn.rel = 'noopener';
  btn.setAttribute('aria-label', 'Chat with us on WhatsApp');
  btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 20l1.1-5.4A8.5 8.5 0 1 1 21 11.5Z"/></svg>`;
  document.body.appendChild(btn);
}

// ---------- Init on DOM ready ----------
document.addEventListener('DOMContentLoaded', () => {
  initReveal();
  updateCartBadge();
  initScrollProgress();
  initBackToTop();
  initAnalytics();
  initFloatingWhatsapp();

  document.querySelector('.burger')?.addEventListener('click', () => toggleMobileNav(true));
  document.querySelector('.mobile-nav-dim')?.addEventListener('click', () => toggleMobileNav(false));
  document.querySelectorAll('.mobile-nav a').forEach(a => a.addEventListener('click', () => toggleMobileNav(false)));

  document.querySelector('[data-search-open]')?.addEventListener('click', () => toggleSearch(true));
  document.querySelector('.search-close')?.addEventListener('click', () => toggleSearch(false));
  document.querySelector('.search-overlay')?.addEventListener('click', (e) => {
    if (e.target.classList.contains('search-overlay')) toggleSearch(false);
  });
  document.querySelector('#siteSearchForm')?.addEventListener('submit', submitSiteSearch);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      toggleSearch(false);
      toggleMobileNav(false);
      closeCart();
      closeCheckout();
      closeQuoteModal();
    }
  });
});
