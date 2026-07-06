/* =====================================================
   FAAF Collections & Souvenirs — cart.js
   Cart state (localStorage) + pickup/delivery checkout + WhatsApp order
   ===================================================== */

const CART_KEY = 'faaf_cart_v1';
let deliveryQuote = null; // { distance_km, delivery_fee, matched_address, lat, lng }
let selectedFulfillment = 'pickup';
let appliedCoupon = null; // { code, discount_amount }

function getCart() {
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
  catch { return []; }
}
function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
  updateCartBadge();
}
function cartLineKey(item) {
  return [item.product_id, item.size || '', item.color || ''].join('|');
}

function addToCart(item) {
  const cart = getCart();
  const key = cartLineKey(item);
  const existing = cart.find(c => cartLineKey(c) === key);
  if (existing) {
    existing.quantity += item.quantity || 1;
  } else {
    cart.push({ ...item, quantity: item.quantity || 1 });
  }
  saveCart(cart);
  showToast(`${item.name} added to cart`);
  renderCart();
  openCart();
}

function removeCartLine(key) {
  saveCart(getCart().filter(c => cartLineKey(c) !== key));
  renderCart();
}

function changeCartQty(key, delta) {
  const cart = getCart();
  const item = cart.find(c => cartLineKey(c) === key);
  if (!item) return;
  item.quantity = Math.max(1, item.quantity + delta);
  saveCart(cart);
  renderCart();
}

function cartSubtotal() {
  return getCart().reduce((sum, i) => sum + i.price * i.quantity, 0);
}

function updateCartBadge() {
  const count = getCart().reduce((s, i) => s + i.quantity, 0);
  document.querySelectorAll('.badge-count.cart-count').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}

// ---------- Drawer ----------
function openCart() {
  renderCart();
  document.querySelector('.cart-drawer')?.classList.add('open');
  document.querySelector('.cart-overlay')?.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeCart() {
  document.querySelector('.cart-drawer')?.classList.remove('open');
  document.querySelector('.cart-overlay')?.classList.remove('open');
  document.body.style.overflow = '';
}

function renderCart() {
  const cart = getCart();
  const wrap = document.querySelector('.cart-items');
  const foot = document.querySelector('.cart-foot');
  if (!wrap) return;

  if (!cart.length) {
    wrap.innerHTML = `
      <div class="cart-empty">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h7.2a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
        <p>Your bag is empty.</p>
        <a href="shop.html" class="btn btn-outline btn-sm" style="margin-top:16px;">Start Shopping</a>
      </div>`;
    if (foot) foot.style.display = 'none';
    return;
  }
  if (foot) foot.style.display = 'block';

  wrap.innerHTML = cart.map(item => {
    const key = cartLineKey(item);
    const meta = [item.size, item.color].filter(Boolean).join(' · ');
    return `
    <div class="cart-line">
      <img src="${escapeHtml(item.image || 'https://placehold.co/160x190?text=FAAF')}" alt="${escapeHtml(item.name)}">
      <div class="info">
        <h4>${escapeHtml(item.name)}</h4>
        ${meta ? `<div class="meta">${escapeHtml(meta)}</div>` : ''}
        <div style="display:flex;align-items:center;justify-content:space-between;">
          <div class="qty-stepper">
            <button onclick="changeCartQty('${key}',-1)" aria-label="Decrease quantity">−</button>
            <span>${item.quantity}</span>
            <button onclick="changeCartQty('${key}',1)" aria-label="Increase quantity">+</button>
          </div>
          <span class="line-price">${formatCurrency(item.price * item.quantity)}</span>
        </div>
        <span class="line-remove" onclick="removeCartLine('${key}')">Remove</span>
      </div>
    </div>`;
  }).join('');

  const subtotal = cartSubtotal();
  if (foot) {
    foot.innerHTML = `
      <div class="cart-row"><span>Subtotal</span><span>${formatCurrency(subtotal)}</span></div>
      <div class="cart-row"><span>Delivery</span><span>Calculated at checkout</span></div>
      <div class="cart-row total"><span>Estimated Total</span><span>${formatCurrency(subtotal)}</span></div>
      <button class="btn btn-gold btn-block" onclick="openCheckout()">Checkout via WhatsApp</button>
      <p style="text-align:center;font-size:11.5px;color:var(--muted);margin-top:12px;">You'll confirm your order with us directly on WhatsApp.</p>
    `;
  }
}

// ---------- Checkout modal ----------
function openCheckout() {
  if (!getCart().length) { showToast('Your bag is empty'); return; }
  closeCart();
  document.querySelector('.modal-overlay')?.classList.add('open');
  document.body.style.overflow = 'hidden';
  renderCheckoutSummary();
}
function closeCheckout() {
  document.querySelector('.modal-overlay')?.classList.remove('open');
  document.body.style.overflow = '';
  appliedCoupon = null;
  const couponInput = document.querySelector('#couponInput');
  const couponMsg = document.querySelector('#couponMessage');
  if (couponInput) couponInput.value = '';
  if (couponMsg) { couponMsg.textContent = ''; couponMsg.className = 'coupon-message'; }
}

function selectFulfillment(type) {
  selectedFulfillment = type;
  document.querySelectorAll('.fulfillment-toggle button').forEach(b => {
    b.classList.toggle('active', b.dataset.type === type);
  });
  const addrField = document.querySelector('#deliveryAddressField');
  if (addrField) addrField.style.display = type === 'delivery' ? 'block' : 'none';
  const distResult = document.querySelector('.distance-result');
  if (type === 'pickup') {
    deliveryQuote = null;
    distResult?.classList.remove('show');
  }
  renderCheckoutSummary();
}

function renderCheckoutSummary() {
  const subtotal = cartSubtotal();
  const fee = selectedFulfillment === 'delivery' && deliveryQuote ? deliveryQuote.delivery_fee : 0;
  const discount = appliedCoupon ? appliedCoupon.discount_amount : 0;
  const total = Math.max(0, subtotal + fee - discount);
  const summary = document.querySelector('#checkoutSummary');
  if (summary) {
    summary.innerHTML = `
      <div class="cart-row"><span>Subtotal</span><span>${formatCurrency(subtotal)}</span></div>
      <div class="cart-row"><span>${selectedFulfillment === 'delivery' ? 'Delivery Fee' : 'Pickup'}</span><span>${selectedFulfillment === 'delivery' ? (deliveryQuote ? formatCurrency(fee) : '—') : formatCurrency(0)}</span></div>
      ${appliedCoupon ? `<div class="cart-row"><span>Coupon (${escapeHtml(appliedCoupon.code)})</span><span>−${formatCurrency(discount)}</span></div>` : ''}
      <div class="cart-row total"><span>Total</span><span>${formatCurrency(total)}</span></div>
    `;
  }
}

async function applyCoupon() {
  const input = document.querySelector('#couponInput');
  const msgBox = document.querySelector('#couponMessage');
  const code = input.value.trim();
  if (!code) return;

  msgBox.className = 'coupon-message';
  msgBox.textContent = 'Checking code…';

  try {
    const data = await apiPost('/coupons.php', { code, subtotal: cartSubtotal() });
    appliedCoupon = { code: data.code, discount_amount: data.discount_amount };
    msgBox.className = 'coupon-message success';
    msgBox.textContent = `"${data.code}" applied — you saved ${formatCurrency(data.discount_amount)}.`;
    renderCheckoutSummary();
  } catch (err) {
    appliedCoupon = null;
    msgBox.className = 'coupon-message error';
    msgBox.textContent = err.message || 'That coupon code is not valid.';
    renderCheckoutSummary();
  }
}

let addressDebounceTimer;
function onAddressInput(value) {
  clearTimeout(addressDebounceTimer);
  const resultBox = document.querySelector('.distance-result');
  if (!value || value.trim().length < 8) {
    deliveryQuote = null;
    resultBox?.classList.remove('show');
    renderCheckoutSummary();
    return;
  }
  resultBox.classList.add('show');
  resultBox.innerHTML = `<span class="spinner"></span> Calculating distance & delivery fee…`;
  addressDebounceTimer = setTimeout(() => fetchDistanceQuote(value.trim()), 900);
}

async function fetchDistanceQuote(address) {
  const resultBox = document.querySelector('.distance-result');
  try {
    const data = await apiGet(`/distance.php?address=${encodeURIComponent(address)}&subtotal=${cartSubtotal()}`);
    deliveryQuote = data;
    resultBox.innerHTML = `
      📍 ~<b>${data.distance_km} km</b> from our store
      <div class="fee">${data.free_delivery_applied ? '🎉 Free delivery!' : 'Delivery fee: ' + formatCurrency(data.delivery_fee)}</div>
      <div style="color:var(--muted);font-size:11.5px;margin-top:4px;">${escapeHtml(data.note)}</div>
    `;
    renderCheckoutSummary();
  } catch (err) {
    deliveryQuote = null;
    resultBox.innerHTML = `⚠️ ${escapeHtml(err.message)}`;
    renderCheckoutSummary();
  }
}

async function submitCheckout(e) {
  e.preventDefault();
  const form = e.target;
  const name = form.customer_name.value.trim();
  const phone = form.customer_phone.value.trim();
  const notes = form.notes.value.trim();
  const address = form.delivery_address?.value.trim();
  const errorBox = document.querySelector('#checkoutError');
  const submitBtn = form.querySelector('button[type="submit"]');

  errorBox.classList.remove('show');

  if (!name || !phone) {
    errorBox.textContent = 'Please enter your name and WhatsApp-reachable phone number.';
    errorBox.classList.add('show');
    return;
  }
  if (selectedFulfillment === 'delivery') {
    if (!address) {
      errorBox.textContent = 'Please enter your delivery address.';
      errorBox.classList.add('show');
      return;
    }
    if (!deliveryQuote) {
      errorBox.textContent = 'Please wait for the delivery fee to finish calculating (or refine your address).';
      errorBox.classList.add('show');
      return;
    }
  }

  const cart = getCart();
  const payload = {
    customer_name: name,
    customer_phone: phone,
    fulfillment_type: selectedFulfillment,
    notes,
    items: cart.map(i => ({
      product_id: i.product_id,
      name: i.name,
      size: i.size || null,
      color: i.color || null,
      quantity: i.quantity,
      unit_price: i.price,
    })),
  };
  if (selectedFulfillment === 'delivery' && deliveryQuote) {
    payload.delivery_address = deliveryQuote.matched_address || address;
    payload.delivery_lat = deliveryQuote.lat;
    payload.delivery_lng = deliveryQuote.lng;
    payload.delivery_distance_km = deliveryQuote.distance_km;
    payload.delivery_fee = deliveryQuote.delivery_fee;
  }
  if (appliedCoupon) {
    payload.coupon_code = appliedCoupon.code;
    payload.discount_amount = appliedCoupon.discount_amount;
  }

  submitBtn.disabled = true;
  submitBtn.innerHTML = `<span class="spinner"></span> Placing order…`;

  try {
    const res = await apiPost('/orders.php', payload);
    localStorage.removeItem(CART_KEY);
    updateCartBadge();
    appliedCoupon = null;
    const params = new URLSearchParams({ ref: res.order_ref, total: res.total, wa: res.whatsapp_url });
    window.location.href = `order-confirmation.html?${params.toString()}`;
  } catch (err) {
    errorBox.textContent = err.message || 'Could not place order. Please try again.';
    errorBox.classList.add('show');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Continue on WhatsApp';
  }
}

// ---------- Fly-to-cart animation ----------
function flyToCart(imgEl) {
  const cartIcon = document.querySelector('[data-cart-open]');
  if (!imgEl || !cartIcon) return;
  const startRect = imgEl.getBoundingClientRect();
  const endRect = cartIcon.getBoundingClientRect();
  if (!startRect.width || !endRect.width) return;

  const flyer = document.createElement('img');
  flyer.src = imgEl.src;
  flyer.className = 'fly-to-cart';
  flyer.style.left = startRect.left + 'px';
  flyer.style.top = startRect.top + 'px';
  flyer.style.width = startRect.width + 'px';
  flyer.style.height = startRect.height + 'px';
  document.body.appendChild(flyer);

  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      flyer.style.left = (endRect.left + endRect.width / 2 - 10) + 'px';
      flyer.style.top = (endRect.top + endRect.height / 2 - 10) + 'px';
      flyer.style.width = '20px';
      flyer.style.height = '20px';
      flyer.style.opacity = '0.15';
      flyer.style.borderRadius = '50%';
    });
  });

  setTimeout(() => {
    flyer.remove();
    cartIcon.classList.add('bump');
    setTimeout(() => cartIcon.classList.remove('bump'), 400);
  }, 720);
}

document.addEventListener('DOMContentLoaded', () => {
  renderCart();
  document.querySelector('[data-cart-open]')?.addEventListener('click', openCart);
  document.querySelector('.cart-close')?.addEventListener('click', closeCart);
  document.querySelector('.cart-overlay')?.addEventListener('click', closeCart);
  document.querySelector('.modal-overlay')?.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal-overlay')) closeCheckout();
  });
  document.querySelector('#checkoutForm')?.addEventListener('submit', submitCheckout);
  document.querySelector('#deliveryAddressInput')?.addEventListener('input', (e) => onAddressInput(e.target.value));
  document.querySelectorAll('.fulfillment-toggle button').forEach(b => {
    b.addEventListener('click', () => selectFulfillment(b.dataset.type));
  });
});
