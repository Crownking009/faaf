# FAAF Collections & Souvenirs — Website

A full e-commerce website (frontend + PHP/MySQL backend + admin panel) built for
shared hosting (cPanel-style). WhatsApp checkout, pickup/delivery with distance-based
delivery fees, and a full product/order admin panel.

## 1. What's inside

```
/                     storefront (index.html, shop.html, product.html, about.html, contact.html,
                      wishlist.html, order-confirmation.html, terms.html, privacy.html, 404.html)
/assets/css/style.css design system
/assets/js/           main.js, cart.js, shop.js, partials.js, carousel.js, wishlist.js
/api/                 PHP API (products, categories, distance, orders, settings, reviews, coupons)
/admin/               admin panel -- login, products, categories, orders, customers, coupons,
                      reviews, settings, account, users, activity-log, import-products,
                      invoice, export-orders
/config/db.php        database connection -- EDIT THIS FIRST
/uploads/products/    uploaded product images land here
database.sql          run this first (creates tables + settings + categories)
migration-v2.sql      run this ONLY if you already imported database.sql before this update
migration-v3.sql      run this ONLY if you already imported database.sql before this update
demo-products.sql     optional -- 110 sample products so the site isn't empty
sitemap.xml           update the domain, then submit to Google Search Console
robots.txt            allows search engines to crawl the site
```

## 2. Deploy to shared hosting (cPanel)

1. Create a MySQL database in cPanel -> MySQL Databases. Note the DB name, username, password.
2. Import the schema: cPanel -> phpMyAdmin -> select your database -> Import -> upload database.sql.
   Then (optional but recommended for a live demo) import demo-products.sql the same way.
3. Upload all files in this folder to public_html (or a subfolder) via File Manager or FTP.
4. Edit /config/db.php and fill in your real DB_HOST (usually localhost), DB_NAME, DB_USER, DB_PASS.
5. Set folder permissions: make sure /uploads/products/ is writable (755 or 775) so the admin
   panel can save uploaded images.
6. Visit https://yourdomain.com/ -- the storefront should load. Visit /admin/login.php for the admin panel.

## 3. Admin panel login

- URL: /admin/login.php
- Default username: userfaaf
- Default password: Everyone1

You can change this anytime from **Admin -> My Account** (top of sidebar) once logged in --
no phpMyAdmin needed.

**If you already imported database.sql before this update** (i.e. you previously had the
`admin`/`FaafAdmin2026!` login), run this once in phpMyAdmin's SQL tab to switch to the new
credentials instead of re-importing everything:

```sql
UPDATE admin_users
SET username = 'userfaaf',
    password_hash = '$2b$10$kPPpJTX.30pADKJG5Oz/MuZCEU4s2cFpxGiBr5sCa/RsNI2PRffAG'
WHERE username = 'admin';
```

That hash corresponds to the password `Everyone1` -- change it via My Account right after logging in.

## 4. What's in the admin panel

- **Dashboard** -- revenue (all-time + last 7 days), order counts, low-stock alerts, products-by-category breakdown, recent orders.
- **Products** -- search by name, filter by category/status/stock level, duplicate a product as a draft, edit any product (including the seeded demo catalog), delete, and manage multiple images per product (upload from your device via drag-and-drop or file picker, set any image as the main photo, delete individual images).
- **Categories** -- add, inline-edit, and delete.
- **Orders** -- search by name/ref/phone, filter by status, view full order detail, update status (pending -> confirmed -> fulfilled/cancelled).
- **Settings** -- store name/address/coordinates, WhatsApp number, delivery pricing, currency symbol.
- **My Account** -- change your own admin password.

The whole admin panel is mobile responsive: on small screens the sidebar becomes a slide-out
menu (tap the ☰ icon), and all tables/forms adapt to the screen width.

## 5. Store settings

Go to /admin/settings.php to edit:
- Store name & address
- Store latitude/longitude (used to calculate delivery distance -- get free coordinates from
  https://www.latlong.net by searching your address)
- WhatsApp number (international format, no +, e.g. 2349048239391)
- Delivery base fee + rate per km
- Currency symbol

## 6. How delivery pricing works

When a customer chooses "Delivery" at checkout and types an address, the site:
1. Sends the address to the free OpenStreetMap Nominatim geocoder to get coordinates.
2. Calculates straight-line ("as the crow flies") distance from your store using the Haversine formula.
3. Multiplies by 1.25 to roughly approximate real road distance.
4. Applies: delivery_fee = base_fee + (distance_km x rate_per_km), rounded to the nearest N50.

This is a free, no-API-key solution. It's an estimate, not live traffic routing -- the checkout
message tells the customer this, and you can always adjust price via WhatsApp before confirming payment.

If you ever want live driving-distance accuracy, this can later be swapped for the Google Maps
Distance Matrix API (requires billing) -- only /api/distance.php would need to change.

## 7. How WhatsApp checkout works

When a customer completes checkout, the backend saves the order in the database (so you have a record
in /admin/orders.php) and generates a wa.me link with a pre-filled message containing their items,
sizes/colors, subtotal, delivery fee (if any), and contact info. The customer is redirected straight to
WhatsApp to send that message to 0904 823 9391 and finalize payment with you directly.

## 8. Managing products

/admin/products.php -> "+ Add Product". Use the search box and category/status/stock filters
to find products quickly, or "Duplicate" an existing product to quickly create a variant.

For images, drag-and-drop or click to choose **multiple photos at once** from your device --
they'll all upload together. On the edit screen, hover any existing photo to set it as the
main photo or delete it. You can also paste an external image URL instead of uploading.

Set sizes/colors as comma-separated values (e.g. `S,M,L,XL` or `Black,Gold,White`) -- leave blank
for items without size/color variants (like sunglasses or souvenirs).

**Note:** if uploading several large photos at once fails, your host's PHP `upload_max_filesize`,
`post_max_size`, or `max_file_uploads` limits may be too low. Most cPanel hosts let you raise
these in "MultiPHP INI Editor" -- ask your host to increase them if needed.

## 9. Replacing placeholder images

All current images use placehold.co (a placeholder image generator) so you can preview the full site
today. Replace them anytime by:
- Uploading real photos via the admin product form, or
- Editing the image_url values directly in product_images via phpMyAdmin, or
- Swapping the hero/lookbook images in index.html and about.html (search for "placehold.co" in those files).

## 10. Local testing before upload (optional)

For a frontend-only preview with demo products, no PHP or MySQL needed:
```
node static-preview-server.js
```
Then visit http://127.0.0.1:8000. The storefront will use `assets/js/demo-data.js`
for products/categories whenever the backend API is not available on localhost.

If you have PHP installed locally:
```
php -S localhost:8000
```
Then visit http://localhost:8000. You'll still need a MySQL server running locally with the same
schema imported, and config/db.php pointed at it.

## 11. Visual polish & interactions

This build includes several extra touches worth knowing about:

- **Custom 404 page** (`404.html`) — the `.htaccess` file routes any broken link to it automatically via `ErrorDocument 404 /404.html`. If you move the site into a subfolder, update that path to match.
- **Page loader splash** — a brief FAAF monogram appears on the homepage while the page loads, then fades out (auto-hides within 4 seconds even on a slow connection, so it never blocks the page).
- **Scroll progress bar** — a thin gold line at the top of every page fills in as you scroll.
- **Back-to-top button** — appears bottom-right after scrolling ~600px on any page.
- **Featured Collections carousel** and **Featured Products carousel** on the homepage — swipeable on mobile, arrow/dot navigation on desktop, auto-rotating collections banner.
- **Fly-to-cart animation** — clicking "Add to Bag" anywhere on the site sends a small flying copy of the product photo into the cart icon.
- **Product image zoom** — click/tap the main photo on a product page to zoom in; move your cursor to pan around.
- **Sticky "Add to Bag" bar** — once you scroll past the main add-to-cart buttons on a product page, a slim bar with the price and an Add to Bag button sticks to the bottom of the screen.
- **Wave dividers** around the black souvenirs section, staggered card reveal animations, and a subtle 3D tilt-on-hover for product/category cards on desktop (automatically disabled on touch devices).
- **Custom scrollbar** styled in gold to match the theme (falls back to the browser default on older browsers that don't support scrollbar styling).

## 12. What's new: stock, reviews, coupons, wishlist, and more

This update added a large batch of features. Here's what changed and what you need to do:

### If you already have this site running (imported database.sql before)
Import `migration-v2.sql` once via phpMyAdmin — it adds the new columns/tables this update
needs (coupon tracking on orders, product reviews, coupons, an admin notification email
setting) without touching your existing products or orders. Skip this if you're installing
fresh — `database.sql` already includes everything.

### Storefront changes
- **Stock awareness**: products that hit 0 stock now show a "Sold Out" badge and can't be
  added to cart anywhere on the site. Items with 5 or fewer left show a "Only X left" hint.
- **Stock auto-deducts** when an order is placed (never goes below 0).
- **Wishlist**: a heart icon on every product card and the product page saves items to a
  wishlist stored in the browser (`wishlist.html`) — no customer account needed.
- **Recently Viewed**: the product page now remembers the last few items you looked at and
  shows them near the bottom.
- **Product reviews**: shoppers can leave a star rating + comment on any product page. New
  reviews are held for approval in **Admin → Reviews** before they appear publicly.
- **Coupon codes**: the checkout modal has a "Have a coupon code?" field. Create codes in
  **Admin → Coupons** (percentage or fixed amount, with optional minimum order, usage limit,
  and expiry date).
- **Order confirmation page**: after checkout, customers land on `order-confirmation.html`
  showing their order reference before continuing to WhatsApp (WhatsApp also opens
  automatically in a new tab for convenience).
- **Pagination**: the shop page now loads 12 products per page with page controls, instead of
  loading the whole catalog at once.

### Admin panel changes
- **Reviews** (`/admin/reviews.php`) — approve, hide, or delete submitted reviews.
- **Coupons** (`/admin/coupons.php`) — create and manage discount codes.
- **Customers** (`/admin/customers.php`) — a list of everyone who's ordered, derived from your
  order history, with order count and total spent. Includes a quick WhatsApp link per customer.
- **Export Orders** — a new "⬇ Export CSV" button on the Orders page downloads all orders as a
  spreadsheet for bookkeeping.
- **New order alerts** — the dashboard now shows how many orders you haven't opened yet, and
  each unseen order is flagged "NEW" in the orders list until you view its details.
- **Email notifications** — set an email address in **Admin → Settings → Admin Notification
  Email** to get emailed every time a new order comes in. This uses PHP's built-in `mail()`
  function, which works out of the box on most cPanel hosts; if emails don't arrive, check
  with your host about outgoing mail configuration, or ask them to enable SMTP.

### Legal, SEO & analytics
- **Terms of Service** (`terms.html`) and **Privacy Policy** (`privacy.html`) — linked in the
  footer. Written for a Nigerian WhatsApp-checkout business; review and adjust the wording to
  match your actual practices before relying on them legally.
- **sitemap.xml** and **robots.txt** — added at the root. Open `sitemap.xml` and replace
  `https://example.com` with your real domain once you deploy.
- **Analytics (opt-in)** — every page has a `<meta name="ga-measurement-id" content="">` tag
  in the `<head>`. Paste your Google Analytics 4 Measurement ID (looks like `G-XXXXXXXXXX`)
  into that `content` attribute on each page to turn on tracking. Leave it blank and nothing
  is loaded — no tracking scripts run until you add an ID.

## 13. What's new: variants, order editing, multi-admin, and more

### If you already have this site running
Run **`migration-v3.sql`** once in phpMyAdmin (in addition to `migration-v2.sql` if you hadn't
already). Brand new installs can skip both — `database.sql` includes everything.

### Per-size/color stock
Products with sizes and/or colors can now track stock per combination instead of one number
for the whole product. On any existing product's edit page (`/admin/product-form.php?id=...`),
scroll to **Variant Stock** and set quantities per size/color. Leave it alone (all zeros) and
the product keeps using the single "Stock Quantity" field like before — this is fully optional
per product.

### Floating WhatsApp button
A WhatsApp bubble now sits in the bottom-left corner of every page for quick questions, separate
from the checkout flow.

### Order editing (Admin → Orders → view an order)
You can now, after an order is placed:
- Change an item's quantity or remove it (stock is automatically adjusted back)
- Add another product to the order
- Edit the delivery fee, discount amount, or notes directly
- Print a clean invoice (🖨 Print Invoice button)

### Multi-admin accounts
**Admin → Admin Users** lets you create logins for staff. Every admin has full access — there
are no restricted roles yet, so only add people you trust with full control. You can't delete
your own account or remove the last remaining admin.

### Activity log
**Admin → Activity Log** shows the last 200 actions taken across all admin accounts — logins,
product/order/coupon changes, settings updates — so you can see who did what.

### Bulk product import
**Admin → Import Products** accepts a CSV file to create many products at once. Required
columns: `name`, `category` (must match an existing category name/slug), `price`. A sample
format is shown on the page.

### Spam protection
Product reviews and order submissions are now rate-limited per IP address (a handful per hour)
to deter abuse, and the review form includes a hidden honeypot field that silently rejects bots
without showing them an error.

### Low stock email alerts
If you've set an Admin Notification Email (Settings), any time an order drops a product/variant
to 5 units or fewer, that's included at the bottom of the order notification email.

### SEO structured data
Product pages now include schema.org markup (name, price, availability, and rating once reviews
exist) so Google can potentially show rich results like star ratings in search.

### Where to change your admin password
Two places, same result: **Admin → Settings** has a "Security" section at the bottom, or use
**Admin → My Account**. Both work — use whichever you land on first.

- **Terms of Service** (`terms.html`) and **Privacy Policy** (`privacy.html`) — linked in the
  footer. Written for a Nigerian WhatsApp-checkout business; review and adjust the wording to
  match your actual practices before relying on them legally.
- **sitemap.xml** and **robots.txt** — added at the root. Open `sitemap.xml` and replace
  `https://example.com` with your real domain once you deploy.
- **Analytics (opt-in)** — every page has a `<meta name="ga-measurement-id" content="">` tag
  in the `<head>`. Paste your Google Analytics 4 Measurement ID (looks like `G-XXXXXXXXXX`)
  into that `content` attribute on each page to turn on tracking. Leave it blank and nothing
  is loaded — no tracking scripts run until you add an ID.
