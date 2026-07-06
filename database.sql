-- =====================================================
-- FAAF Collections & Souvenirs — Database Schema
-- Import this file in phpMyAdmin (or via mysql CLI) on your shared hosting
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------
-- Table: categories
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  gender ENUM('male','female','unisex') DEFAULT 'unisex',
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: products
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  description TEXT,
  price DECIMAL(12,2) NOT NULL,
  compare_price DECIMAL(12,2) DEFAULT NULL,
  gender ENUM('male','female','unisex') DEFAULT 'unisex',
  sizes VARCHAR(255) DEFAULT NULL,        -- comma separated e.g. "S,M,L,XL"
  colors VARCHAR(255) DEFAULT NULL,       -- comma separated e.g. "Black,Gold,White"
  stock INT DEFAULT 10,
  is_featured TINYINT(1) DEFAULT 0,
  is_new TINYINT(1) DEFAULT 0,
  status ENUM('active','draft') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: product_variants (optional per-size/color stock)
-- If a product has no rows here, its stock is tracked on products.stock instead.
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  size VARCHAR(20) DEFAULT NULL,
  color VARCHAR(40) DEFAULT NULL,
  stock INT NOT NULL DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY unique_variant (product_id, size, color)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: product_images
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: orders
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_ref VARCHAR(40) NOT NULL UNIQUE,
  customer_name VARCHAR(150) NOT NULL,
  customer_phone VARCHAR(40) NOT NULL,
  fulfillment_type ENUM('pickup','delivery') NOT NULL,
  delivery_address VARCHAR(500) DEFAULT NULL,
  delivery_lat DECIMAL(10,7) DEFAULT NULL,
  delivery_lng DECIMAL(10,7) DEFAULT NULL,
  delivery_distance_km DECIMAL(8,2) DEFAULT NULL,
  delivery_fee DECIMAL(12,2) DEFAULT 0,
  coupon_code VARCHAR(40) DEFAULT NULL,
  discount_amount DECIMAL(12,2) DEFAULT 0,
  subtotal DECIMAL(12,2) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('pending','confirmed','fulfilled','cancelled') DEFAULT 'pending',
  seen_by_admin TINYINT(1) DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: order_items
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  product_name VARCHAR(180) NOT NULL,
  size VARCHAR(20) DEFAULT NULL,
  color VARCHAR(40) DEFAULT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(12,2) NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: product_reviews
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS product_reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  reviewer_name VARCHAR(120) NOT NULL,
  rating TINYINT NOT NULL,
  review_text TEXT,
  is_approved TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: coupons
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL UNIQUE,
  discount_type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
  discount_value DECIMAL(12,2) NOT NULL,
  min_order_amount DECIMAL(12,2) DEFAULT 0,
  usage_limit INT DEFAULT NULL,
  times_used INT DEFAULT 0,
  expires_at DATE DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: settings (key/value store editable from admin)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(80) PRIMARY KEY,
  setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, setting_value) VALUES
('store_name', 'FAAF Collections & Souvenirs'),
('store_address', 'No 2 Church Avenue, off A.I.T. Road, Alagbado, Lagos'),
('store_lat', '6.6469'),
('store_lng', '3.2871'),
('whatsapp_number', '2349048239391'),
('delivery_rate_per_km', '300'),
('delivery_base_fee', '500'),
('free_delivery_threshold', '0'),
('currency_symbol', '₦'),
('admin_email', '')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ---------------------------------------------------
-- Table: rate_limits (basic spam/abuse throttling)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS rate_limits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(64) NOT NULL,
  action VARCHAR(40) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rate_lookup (ip_address, action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: admin_activity_log
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT DEFAULT NULL,
  admin_username VARCHAR(80) DEFAULT NULL,
  action VARCHAR(80) NOT NULL,
  details VARCHAR(500) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- Table: admin_users
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin login: username = userfaaf / password = Everyone1
-- You can change this anytime from Admin -> My Account -> Change Password
INSERT INTO admin_users (username, password_hash) VALUES
('userfaaf', '$2b$10$kPPpJTX.30pADKJG5Oz/MuZCEU4s2cFpxGiBr5sCa/RsNI2PRffAG')
ON DUPLICATE KEY UPDATE username = username, password_hash = VALUES(password_hash);

-- ---------------------------------------------------
-- Seed categories
-- ---------------------------------------------------
INSERT INTO categories (name, slug, gender, sort_order) VALUES
('Jeans', 'jeans', 'unisex', 1),
('T-Shirts', 't-shirts', 'unisex', 2),
('Jean Skirts', 'jean-skirts', 'female', 3),
('Short Dresses', 'short-dresses', 'female', 4),
('Jalabia', 'jalabia', 'male', 5),
('Abayah', 'abayah', 'female', 6),
('Shoes', 'shoes', 'unisex', 7),
('Slippers', 'slippers', 'unisex', 8),
('Bags', 'bags', 'unisex', 9),
('Sunglasses', 'sunglasses', 'unisex', 10),
('Party Souvenirs', 'souvenirs', 'unisex', 11)
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET FOREIGN_KEY_CHECKS = 1;
