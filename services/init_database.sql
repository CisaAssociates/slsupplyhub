-- Create database if not exists
CREATE DATABASE IF NOT EXISTS slsupplyhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE slsupplyhub;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('customer', 'supplier', 'driver', 'admin') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    role ENUM('customer', 'supplier', 'driver', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    business_name VARCHAR(100) NOT NULL,
    business_address TEXT NOT NULL,
    business_phone VARCHAR(20) NOT NULL,
    business_email VARCHAR(255) NOT NULL,
    business_permit_number VARCHAR(50),
    tax_id VARCHAR(50),
    rating DECIMAL(3,2) DEFAULT 0,
    total_orders INT DEFAULT 0,
    status ENUM('pending', 'approved', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Drivers table
CREATE TABLE IF NOT EXISTS drivers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_type VARCHAR(50) NOT NULL,
    vehicle_plate VARCHAR(20) NOT NULL,
    license_number VARCHAR(50) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0,
    total_deliveries INT DEFAULT 0,
    status ENUM('available', 'busy', 'offline') DEFAULT 'offline',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    parent_id INT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Groceries', 'Food and household essentials'),
('Electronics', 'Electronic devices and accessories'),
('Fashion', 'Clothing and accessories'),
('Home & Living', 'Home decor and furniture'),
('Health & Beauty', 'Personal care and wellness products'),
('Sports & Outdoor', 'Sports equipment and outdoor gear'),
('Books & Stationery', 'Books, office, and school supplies'),
('Toys & Games', 'Toys, games, and entertainment'),
('Automotive', 'Car parts and accessories'),
('Pet Supplies', 'Pet food and accessories');

-- Insert sub-categories
INSERT INTO categories (name, description, parent_id) 
SELECT 'Fresh Food', 'Fresh fruits, vegetables, and meat', id 
FROM categories WHERE name = 'Groceries';

INSERT INTO categories (name, description, parent_id)
SELECT 'Packaged Food', 'Canned goods, snacks, and beverages', id
FROM categories WHERE name = 'Groceries';

INSERT INTO categories (name, description, parent_id)
SELECT 'Smartphones', 'Mobile phones and accessories', id
FROM categories WHERE name = 'Electronics';

INSERT INTO categories (name, description, parent_id)
SELECT 'Computers', 'Laptops, desktops, and accessories', id
FROM categories WHERE name = 'Electronics';

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_id INT NOT NULL,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    regular_price DECIMAL(10,2),
    stock INT NOT NULL DEFAULT 0,
    unit VARCHAR(20) NOT NULL,
    minimum_order INT DEFAULT 1,
    image_path VARCHAR(255) DEFAULT NULL,
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product Images table
CREATE TABLE IF NOT EXISTS product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Addresses table
CREATE TABLE IF NOT EXISTS addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    street VARCHAR(255) NOT NULL,
    barangay VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_default (user_id, is_default),
    INDEX idx_city_barangay (city, barangay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ensure only one default address per user
CREATE TRIGGER before_address_insert 
BEFORE INSERT ON addresses
FOR EACH ROW
BEGIN
    IF NEW.is_default = TRUE THEN
        UPDATE addresses SET is_default = FALSE 
        WHERE user_id = NEW.user_id;
    END IF;
END;

CREATE TRIGGER before_address_update
BEFORE UPDATE ON addresses
FOR EACH ROW
BEGIN
    IF NEW.is_default = TRUE AND OLD.is_default = FALSE THEN
        UPDATE addresses SET is_default = FALSE 
        WHERE user_id = NEW.user_id AND id != NEW.id;
    END IF;
END;

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    supplier_id INT NOT NULL,
    driver_id INT,
    address_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'assigned', 'picked_up', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (address_id) REFERENCES addresses(id)
);

-- Order Items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Order Status History table
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    transaction_id VARCHAR(100),
    status VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    order_id INT NOT NULL,
    supplier_id INT,
    driver_id INT,
    product_id INT,
    rating INT NOT NULL,
    comment TEXT,
    type ENUM('supplier', 'driver', 'product') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) NOT NULL,
    reference_id INT,
    reference_type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    is_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Loyalty Rewards table
CREATE TABLE IF NOT EXISTS loyalty_rewards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    transaction_count INT DEFAULT 0,
    tier VARCHAR(20) DEFAULT 'None',
    reward_amount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password Reset Tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Email Verification Tokens table
CREATE TABLE IF NOT EXISTS email_verification_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Wishlist table
CREATE TABLE IF NOT EXISTS wishlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_customer_product (customer_id, product_id)
);

CREATE INDEX idx_wishlist_customer ON wishlists(customer_id);
CREATE INDEX idx_wishlist_product ON wishlists(product_id);

-- Create indexes for better query performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_products_supplier ON products(supplier_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_supplier ON orders(supplier_id);
CREATE INDEX idx_orders_driver ON orders(driver_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
-- Removed duplicate idx_feedback_order index
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_loyalty_customer ON loyalty_rewards(customer_id);

-- Add indexes for product filtering and sorting
CREATE INDEX idx_rating ON products (rating);
CREATE INDEX idx_review_count ON products (review_count);
CREATE INDEX idx_price ON products (price);
CREATE INDEX idx_created ON products (created_at);
CREATE INDEX idx_category ON products (category_id);
CREATE INDEX idx_supplier ON products (supplier_id);
CREATE INDEX idx_stock ON products (stock);
CREATE INDEX idx_status ON products (status);

-- Add indexes for feedback queries
CREATE INDEX idx_feedback_type ON feedback (type);
CREATE INDEX idx_feedback_product ON feedback (product_id, type);
CREATE INDEX idx_feedback_supplier ON feedback (supplier_id, type);
CREATE INDEX idx_feedback_driver ON feedback (driver_id, type);
CREATE INDEX idx_feedback_customer ON feedback (customer_id);

-- Triggers for product ratings
DELIMITER //

CREATE TRIGGER update_product_rating_after_feedback_insert
AFTER INSERT ON feedback
FOR EACH ROW
BEGIN
    IF NEW.type = 'product' THEN
        UPDATE products p
        SET p.rating = (
            SELECT AVG(rating)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        ),
        p.review_count = (
            SELECT COUNT(*)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        )
        WHERE p.id = NEW.product_id;
    END IF;
END//

CREATE TRIGGER update_product_rating_after_feedback_update
AFTER UPDATE ON feedback
FOR EACH ROW
BEGIN
    IF NEW.type = 'product' THEN
        UPDATE products p
        SET p.rating = (
            SELECT AVG(rating)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        ),
        p.review_count = (
            SELECT COUNT(*)
            FROM feedback
            WHERE product_id = NEW.product_id
            AND type = 'product'
        )
        WHERE p.id = NEW.product_id;
    END IF;
END//

CREATE TRIGGER update_product_rating_after_feedback_delete
AFTER DELETE ON feedback
FOR EACH ROW
BEGIN
    IF OLD.type = 'product' THEN
        UPDATE products p
        SET p.rating = COALESCE((
            SELECT AVG(rating)
            FROM feedback
            WHERE product_id = OLD.product_id
            AND type = 'product'
        ), 0),
        p.review_count = (
            SELECT COUNT(*)
            FROM feedback
            WHERE product_id = OLD.product_id
            AND type = 'product'
        )
        WHERE p.id = OLD.product_id;
    END IF;
END//

DELIMITER ;