-- =====================================================
-- FAAF Collections & Souvenirs — Migration v2
-- Run this ONCE in phpMyAdmin if you already imported database.sql before
-- this update. Safe to run even if some parts already exist (uses IF NOT EXISTS
-- / catches duplicate-column errors where possible).
-- If you are doing a brand new install, skip this file — database.sql already
-- includes everything below.
-- =====================================================

-- New columns on orders
ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(40) DEFAULT NULL AFTER delivery_fee;
ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(12,2) DEFAULT 0 AFTER coupon_code;
ALTER TABLE orders ADD COLUMN seen_by_admin TINYINT(1) DEFAULT 0 AFTER status;

-- New tables
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

-- New setting
INSERT INTO settings (setting_key, setting_value) VALUES ('admin_email', '')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
