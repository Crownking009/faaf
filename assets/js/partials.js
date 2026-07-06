/* =====================================================
   FAAF Collections & Souvenirs — partials.js
   Injects header / footer / overlays so markup lives in one place.
   Each page includes: <div id="site-header" data-active="home"></div>
   ===================================================== */

function headerHTML(active) {
  const link = (href, label, key) => `<a href="${href}" class="${active === key ? 'active' : ''}">${label}</a>`;
  return `
  <div class="topbar">Free pickup at our Alagbado store · <b>Bright styles, every season</b> · WhatsApp orders welcome</div>
  <header class="site-header">
    <div class="container header-row">
      <a href="index.html" class="logo">
        <span class="mark">FAAF<span>.</span></span>
        <span class="tag">Collections &amp; Souvenirs</span>
      </a>
      <nav class="main-nav">
        ${link('index.html', 'Home', 'home')}
        ${link('shop.html', 'Shop', 'shop')}
        ${link('about.html', 'About Us', 'about')}
        ${link('contact.html', 'Contact Us', 'contact')}
      </nav>
      <div class="header-actions">
        <button class="icon-btn" data-search-open aria-label="Search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        </button>
        <a href="wishlist.html" class="icon-btn" aria-label="Wishlist">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>
          <span class="badge-count wishlist-count">0</span>
        </a>
        <button class="icon-btn" data-cart-open aria-label="Open cart">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
          <span class="badge-count cart-count">0</span>
        </button>
        <div class="burger" aria-label="Open menu">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </div>
      </div>
    </div>
    <div class="marquee">
      <div class="marquee-track">
        <span>Jeans &amp; T-Shirts</span><span>Jalabia &amp; Abayah</span><span>Shoes &amp; Slippers</span><span>Bags &amp; Sunglasses</span><span>Party Souvenirs</span><span>Jeans &amp; T-Shirts</span><span>Jalabia &amp; Abayah</span><span>Shoes &amp; Slippers</span><span>Bags &amp; Sunglasses</span><span>Party Souvenirs</span>
      </div>
    </div>
  </header>`;
}

function footerHTML() {
  return `
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-logo">FAAF<span>.</span></div>
          <p style="font-size:14px;line-height:1.7;max-width:280px;">Lagos's go-to store for fashion essentials and party souvenirs — bright pieces for every man and woman, every occasion.</p>
          <div class="social-row" style="margin-top:20px;">
            <a href="#" aria-label="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg></a>
            <a href="https://wa.me/2349048239391" aria-label="WhatsApp"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 20l1.1-5.4A8.5 8.5 0 1 1 21 11.5Z"/></svg></a>
            <a href="#" aria-label="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 9h3V6h-3a4 4 0 0 0-4 4v2H7v3h3v6h3v-6h3l1-3h-4v-2a1 1 0 0 1 1-1Z"/></svg></a>
          </div>
        </div>
        <div>
          <h4>Shop</h4>
          <ul>
            <li><a href="shop.html?category=jeans">Jeans</a></li>
            <li><a href="shop.html?category=t-shirts">T-Shirts</a></li>
            <li><a href="shop.html?category=jalabia">Jalabia</a></li>
            <li><a href="shop.html?category=abayah">Abayah</a></li>
            <li><a href="shop.html?category=souvenirs">Party Souvenirs</a></li>
          </ul>
        </div>
        <div>
          <h4>Company</h4>
          <ul>
            <li><a href="about.html">About Us</a></li>
            <li><a href="contact.html">Contact Us</a></li>
            <li><a href="shop.html">Shop All</a></li>
            <li><a href="wishlist.html">My Wishlist</a></li>
          </ul>
        </div>
        <div>
          <h4>Visit / Contact</h4>
          <ul>
            <li><a href="#">No 2 Church Avenue, off A.I.T. Road, Alagbado, Lagos</a></li>
            <li><a href="https://wa.me/2349048239391">WhatsApp: 0904 823 9391</a></li>
            <li><a href="#">Mon – Sat, 9am – 7pm</a></li>
          </ul>
        </div>
        <div>
          <h4>Legal</h4>
          <ul>
            <li><a href="terms.html">Terms of Service</a></li>
            <li><a href="privacy.html">Privacy Policy</a></li>
            <li><a href="/admin/login.html" data-admin-link>Admin</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© ${new Date().getFullYear()} FAAF Collections &amp; Souvenirs. All rights reserved.</span>
        <span>Designed &amp; built with care in Lagos 🇳🇬</span>
      </div>
    </div>
  </footer>`;
}

function overlaysHTML() {
  return `
  <!-- Search -->
  <div class="search-overlay">
    <span class="search-close">Close ✕</span>
    <form class="search-box" id="siteSearchForm">
      <input type="text" id="siteSearchInput" placeholder="Search for jeans, abayah, sunglasses…" autocomplete="off">
    </form>
  </div>

  <!-- Mobile nav -->
  <div class="mobile-nav-dim overlay-dim" style="z-index:3400;"></div>
  <div class="mobile-nav">
    <div class="footer-logo" style="color:var(--black);margin-bottom:20px;">FAAF<span style="color:var(--gold-deep);">.</span></div>
    <a href="index.html">Home</a>
    <a href="shop.html">Shop</a>
    <a href="about.html">About Us</a>
    <a href="contact.html">Contact Us</a>
  </div>

  <!-- Cart drawer -->
  <div class="overlay-dim cart-overlay"></div>
  <div class="cart-drawer">
    <div class="cart-head">
      <h3>Your Bag</h3>
      <div class="cart-close" aria-label="Close cart">✕</div>
    </div>
    <div class="cart-items"></div>
    <div class="cart-foot"></div>
  </div>

  <!-- Checkout modal -->
  <div class="modal-overlay">
    <div class="modal-box">
      <h3>Checkout</h3>
      <p class="sub">We'll send your order straight to our WhatsApp to confirm payment &amp; fulfillment.</p>

      <div class="fulfillment-toggle">
        <button type="button" data-type="pickup" class="active">
          <span style="font-size:20px;">🏬</span><b>Store Pickup</b><span>Alagbado, Lagos</span>
        </button>
        <button type="button" data-type="delivery">
          <span style="font-size:20px;">🛵</span><b>Delivery</b><span>Fee by distance</span>
        </button>
      </div>

      <form id="checkoutForm">
        <div id="deliveryAddressField" style="display:none;">
          <div class="field">
            <label>Delivery Address</label>
            <input type="text" id="deliveryAddressInput" name="delivery_address" placeholder="e.g. 14 Adeyemi Street, Ikeja, Lagos">
          </div>
          <div class="distance-result"></div>
        </div>
        <div class="field"><label>Full Name</label><input type="text" name="customer_name" required></div>
        <div class="field"><label>Phone Number (WhatsApp)</label><input type="tel" name="customer_phone" placeholder="080..." required></div>
        <div class="field"><label>Order Notes (optional)</label><textarea name="notes" rows="2" placeholder="Sizes, colors, special requests…"></textarea></div>

        <div class="coupon-row">
          <input type="text" id="couponInput" placeholder="Have a coupon code?">
          <button type="button" class="btn btn-outline btn-sm" onclick="applyCoupon()">Apply</button>
        </div>
        <div id="couponMessage" class="coupon-message"></div>

        <div id="checkoutSummary" style="margin:18px 0;padding-top:14px;border-top:1px solid var(--border);"></div>
        <div class="field-error" id="checkoutError"></div>

        <button type="submit" class="btn btn-gold btn-block">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 20l1.1-5.4A8.5 8.5 0 1 1 21 11.5Z"/></svg>
          Continue on WhatsApp
        </button>
        <p style="text-align:center;font-size:11px;color:var(--muted);margin-top:12px;">By continuing you agree to confirm payment details directly with our team on WhatsApp.</p>
      </form>
    </div>
  </div>

  <!-- Souvenir bulk quote request modal -->
  <div class="modal-overlay quote-modal" id="quoteModalOverlay">
    <div class="modal-box">
      <h3>Request a Bulk Souvenir Quote</h3>
      <p class="sub">Tell us about your event and we'll put together custom pricing on WhatsApp.</p>

      <form id="quoteForm">
        <label style="display:block;font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px;">Event Type</label>
        <div class="event-type-grid">
          <label><input type="radio" name="event_type" value="Wedding" checked><span>💍 Wedding</span></label>
          <label><input type="radio" name="event_type" value="Birthday"><span>🎂 Birthday</span></label>
          <label><input type="radio" name="event_type" value="Owambe"><span>🎉 Owambe</span></label>
          <label><input type="radio" name="event_type" value="Baby Shower"><span>👶 Baby Shower</span></label>
          <label><input type="radio" name="event_type" value="Corporate Event"><span>🏢 Corporate</span></label>
          <label><input type="radio" name="event_type" value="Other"><span>✨ Other</span></label>
        </div>

        <div class="field"><label>Full Name</label><input type="text" id="quoteName" required></div>
        <div class="field"><label>Phone Number (WhatsApp)</label><input type="tel" id="quotePhone" required></div>
        <div class="field">
          <label>Souvenir Item(s) of Interest</label>
          <input type="text" id="quoteItems" placeholder="e.g. Personalized cups, keychains…">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
          <div class="field"><label>Estimated Quantity</label><input type="text" id="quoteQty" placeholder="e.g. 100 pieces"></div>
          <div class="field"><label>Event Date</label><input type="date" id="quoteDate"></div>
        </div>
        <div class="field"><label>Anything else? (budget, colors, branding)</label><textarea id="quoteNotes" rows="2" placeholder="Optional details…"></textarea></div>

        <button type="submit" class="btn btn-gold btn-block">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.5L3 20l1.1-5.4A8.5 8.5 0 1 1 21 11.5Z"/></svg>
          Send Quote Request on WhatsApp
        </button>
        <p style="text-align:center;font-size:11px;color:var(--muted);margin-top:12px;">We usually respond within a few hours during business hours.</p>
      </form>
    </div>
  </div>
  `;
}

document.addEventListener('DOMContentLoaded', () => {
  const headerSlot = document.querySelector('#site-header');
  if (headerSlot) headerSlot.outerHTML = headerHTML(headerSlot.dataset.active || '');
  const footerSlot = document.querySelector('#site-footer');
  if (footerSlot) footerSlot.outerHTML = footerHTML();
  document.body.insertAdjacentHTML('beforeend', overlaysHTML());
  // re-init listeners that depend on injected markup
  document.dispatchEvent(new Event('partialsReady'));
  initQuoteModal();
  initAdminFooterLink();
});

function initAdminFooterLink() {
  document.querySelectorAll('[data-admin-link]').forEach(link => {
    link.addEventListener('click', async (e) => {
      e.preventDefault();
      const href = link.getAttribute('href');
      try {
        const res = await fetch(href, { method: 'GET', cache: 'no-store' });
        const type = res.headers.get('content-type') || '';
        if (res.status === 501 || type.includes('application/octet-stream') || type.includes('text/plain')) {
          alert('The admin panel is available locally as static HTML. Open /admin/login.html from the preview server to continue.');
          return;
        }
      } catch (err) {
        // Navigation may still work on a real PHP host even if the probe fails.
      }
      window.location.href = href;
    });
  });
}

// ---------- Souvenir bulk quote request ----------
function openQuoteModal() {
  document.querySelector('#quoteModalOverlay')?.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeQuoteModal() {
  document.querySelector('#quoteModalOverlay')?.classList.remove('open');
  document.body.style.overflow = '';
}

function initQuoteModal() {
  const overlay = document.querySelector('#quoteModalOverlay');
  if (!overlay) return;

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeQuoteModal();
  });

  // visual active state for event-type tiles (fallback for browsers without :has())
  overlay.querySelectorAll('.event-type-grid input').forEach(input => {
    if (input.checked) input.closest('label').classList.add('active-tile');
    input.addEventListener('change', () => {
      overlay.querySelectorAll('.event-type-grid label').forEach(l => l.classList.remove('active-tile'));
      input.closest('label').classList.add('active-tile');
    });
  });

  document.querySelector('#quoteForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    const eventType = overlay.querySelector('input[name="event_type"]:checked')?.value || 'Event';
    const name = document.querySelector('#quoteName').value.trim();
    const phone = document.querySelector('#quotePhone').value.trim();
    const items = document.querySelector('#quoteItems').value.trim();
    const qty = document.querySelector('#quoteQty').value.trim();
    const date = document.querySelector('#quoteDate').value;
    const notes = document.querySelector('#quoteNotes').value.trim();

    const lines = [
      `Hello FAAF Collections! 👋 I'd like a bulk souvenir quote.`,
      ``,
      `*Event Type:* ${eventType}`,
      `*Name:* ${name}`,
      `*Phone:* ${phone}`,
    ];
    if (items) lines.push(`*Item(s) of Interest:* ${items}`);
    if (qty) lines.push(`*Estimated Quantity:* ${qty}`);
    if (date) lines.push(`*Event Date:* ${date}`);
    if (notes) lines.push(`*Additional Details:* ${notes}`);

    const text = lines.join('\n');
    window.open(`https://wa.me/2349048239391?text=${encodeURIComponent(text)}`, '_blank');
    closeQuoteModal();
  });
}
