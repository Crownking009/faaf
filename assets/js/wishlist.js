/* =====================================================
   FAAF Collections & Souvenirs — wishlist.js
   Simple localStorage-based wishlist (no customer account needed)
   ===================================================== */

const WISHLIST_KEY = 'faaf_wishlist_v1';

function getWishlist() {
  try { return JSON.parse(localStorage.getItem(WISHLIST_KEY)) || []; }
  catch { return []; }
}
function saveWishlist(list) {
  localStorage.setItem(WISHLIST_KEY, JSON.stringify(list));
  updateWishlistBadge();
}
function isWishlisted(productId) {
  return getWishlist().some(w => w.product_id === productId);
}

function toggleWishlist(item, btnEl) {
  const list = getWishlist();
  const idx = list.findIndex(w => w.product_id === item.product_id);
  if (idx > -1) {
    list.splice(idx, 1);
    if (btnEl) btnEl.classList.remove('active');
    showToast(`Removed from wishlist`);
  } else {
    list.push(item);
    if (btnEl) btnEl.classList.add('active');
    showToast(`Saved to wishlist`);
  }
  saveWishlist(list);
}

function removeFromWishlist(productId) {
  saveWishlist(getWishlist().filter(w => w.product_id !== productId));
}

function updateWishlistBadge() {
  const count = getWishlist().length;
  document.querySelectorAll('.badge-count.wishlist-count').forEach(el => {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}

document.addEventListener('DOMContentLoaded', updateWishlistBadge);
