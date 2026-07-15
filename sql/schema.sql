-- ============================================
-- Restaurant Full-Stack Project - Database Schema
-- Import this on your RDS / MySQL instance:
--   mysql -h <RDS_ENDPOINT> -u admin -p < schema.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS restaurant_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE restaurant_db;

-- Admin users (for the admin dashboard)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0
);

-- Menu items (images stored on S3, this table only stores the S3 URL/key)
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_key VARCHAR(255),         -- S3 object key, e.g. menu/uuid.jpg
    image_url VARCHAR(500),         -- full public/CloudFront URL, cached for fast reads
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Table reservations
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time TIME NOT NULL,
    guests INT NOT NULL DEFAULT 2,
    notes TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact form messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed data
INSERT INTO categories (name, sort_order) VALUES
('Starters', 1), ('Main Course', 2), ('Grills', 3), ('Desserts', 4), ('Drinks', 5);

-- Default admin: username = admin / password = Admin@123
-- (hash generated with password_hash('Admin@123', PASSWORD_BCRYPT) -- CHANGE THIS AFTER FIRST LOGIN)
INSERT INTO admins (username, password_hash) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO menu_items (category_id, name, description, price, image_key, image_url, is_available) VALUES
(2, 'Grilled Salmon', 'Fresh salmon fillet grilled with lemon butter sauce', 320.00, NULL, NULL, 1),
(3, 'Mixed Grill Platter', 'Chicken, kofta and lamb chops served with rice', 280.00, NULL, NULL, 1),
(1, 'Caesar Salad', 'Romaine lettuce, parmesan, croutons, caesar dressing', 95.00, NULL, NULL, 1),
(4, 'Chocolate Lava Cake', 'Warm chocolate cake with a molten center', 85.00, NULL, NULL, 1);
