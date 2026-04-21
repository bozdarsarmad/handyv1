-- =============================================
-- HANDY CERTIFICATE SERVICES - DATABASE SCHEMA
-- =============================================
CREATE DATABASE IF NOT EXISTS handy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE handy_db;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    profile_pic VARCHAR(255) DEFAULT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- IMAGE SLIDERS
CREATE TABLE IF NOT EXISTS sliders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    title VARCHAR(200),
    subtitle VARCHAR(300),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- BOTTOM STATS (fast delivery, total users, etc.)
CREATE TABLE IF NOT EXISTS stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    icon VARCHAR(100) DEFAULT '⭐',
    label VARCHAR(100) NOT NULL,
    value VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

-- CARD CATEGORIES (Matric, Intermediate, University)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

-- SERVICE CARDS
CREATE TABLE IF NOT EXISTS cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(200) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    prices JSON,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- DYNAMIC ORDER FORM FIELDS (from admin panel)
CREATE TABLE IF NOT EXISTS order_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section ENUM('academic','shipping') NOT NULL,
    field_type ENUM('text','number','email','tel','select','radio','checkbox','textarea','date') NOT NULL,
    label VARCHAR(200) NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    placeholder VARCHAR(200),
    options JSON,
    is_required TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

-- CITY SHIPPING RATES
CREATE TABLE IF NOT EXISTS shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city VARCHAR(100) NOT NULL,
    rate DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) DEFAULT 1
);

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    card_id INT,
    card_snapshot JSON,
    selected_certificates JSON,
    academic_info JSON,
    shipping_info JSON,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    subtotal DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) DEFAULT 0.00,
    order_status ENUM('pending','confirmed','processing','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','pending','paid','refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (card_id) REFERENCES cards(id) ON DELETE SET NULL
);

-- PAYMENT METHODS (admin managed)
CREATE TABLE IF NOT EXISTS payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('easypaisa','jazzcash','bank','other') DEFAULT 'other',
    account_number VARCHAR(200),
    account_title VARCHAR(200),
    instructions TEXT,
    logo_icon VARCHAR(100) DEFAULT '💳',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0
);

-- PAYMENTS
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    method_id INT,
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(200),
    proof_image VARCHAR(255),
    status ENUM('pending','verified','rejected') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (method_id) REFERENCES payment_methods(id) ON DELETE SET NULL
);

-- ========================
-- SEED DATA
-- ========================

-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@handy.pk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Sample sliders
INSERT INTO sliders (image, title, subtitle, sort_order) VALUES
('uploads/sliders/slide1.jpg', 'Fast Certificate Services', 'Get your documents delivered all across Sind', 1),
('uploads/sliders/slide2.jpg', 'Trusted by Thousands', 'Verified, authentic certificates with quick turnaround', 2),
('uploads/sliders/slide3.jpg', 'Affordable Prices', 'Starting from Rs. 500 only', 3);

-- Stats
INSERT INTO stats (icon, label, value, sort_order) VALUES
('🚚', 'Fast Delivery All Over Sind', '24-48 Hours', 1),
('⭐', 'Average User Rating', '4.9/5.0', 2),
('👥', 'Total Users Served', '15,000+', 3),
('📜', 'Certificates Delivered', '50,000+', 4);

-- Categories
INSERT INTO categories (name, sort_order) VALUES
('Matric', 1),
('Intermediate', 2),
('University', 3);

-- Sample Cards
INSERT INTO cards (category_id, title, image, description, prices, sort_order) VALUES
(1, 'Matric Certificates', 'uploads/sliders/matric.jpg', 'Official Matric board certificates and documents. Fast processing with board verification.', '{"Pass Certificate": 500, "Pacca Certificate": 800, "Migration Certificate": 1100}', 1),
(2, 'Intermediate Certificates', 'uploads/sliders/inter.jpg', 'Intermediate board certificates including detailed marks sheet and migration documents.', '{"Pass Certificate": 600, "Pacca Certificate": 900, "Migration Certificate": 1200}', 1),
(3, 'University Degrees', 'uploads/sliders/uni.jpg', 'University degree certificates with transcript and provisional certificate processing.', '{"Provisional Certificate": 800, "Degree Certificate": 1500, "Transcript": 700}', 1);

-- Dynamic order fields - Academic section
INSERT INTO order_fields (section, field_type, label, field_name, placeholder, is_required, sort_order) VALUES
('academic', 'text', 'Full Name (as on certificate)', 'full_name', 'Enter your full name', 1, 1),
('academic', 'text', 'Father Name', 'father_name', 'Enter father name', 1, 2),
('academic', 'text', 'Roll Number', 'roll_number', 'Enter your roll number', 1, 3),
('academic', 'number', 'Year of Passing', 'passing_year', 'e.g. 2022', 1, 4),
('academic', 'select', 'Board / University', 'board_name', NULL, 1, 5),
('academic', 'text', 'CNIC Number', 'cnic', '00000-0000000-0', 0, 6);

-- Board options
UPDATE order_fields SET options = '["Karachi Board","Hyderabad Board","Sukkur Board","Larkana Board","Mirpurkhas Board","University of Karachi","University of Sindh","Mehran University","Liaquat University"]' WHERE field_name = 'board_name';

-- Dynamic order fields - Shipping section
INSERT INTO order_fields (section, field_type, label, field_name, placeholder, is_required, sort_order) VALUES
('shipping', 'text', 'Recipient Full Name', 'recipient_name', 'Enter recipient name', 1, 1),
('shipping', 'tel', 'Phone Number', 'phone', '03XX-XXXXXXX', 1, 2),
('shipping', 'text', 'Full Address', 'address', 'House #, Street, Area', 1, 3),
('shipping', 'select', 'City', 'city', NULL, 1, 4),
('shipping', 'text', 'Nearest Landmark', 'landmark', 'Optional landmark', 0, 5);

UPDATE order_fields SET options = '["Karachi","Hyderabad","Sukkur","Larkana","Nawabshah","Mirpurkhas","Khairpur","Jacobabad","Shikarpur","Dadu"]' WHERE field_name = 'city';

-- Shipping rates
INSERT INTO shipping_rates (city, rate) VALUES
('Karachi', 150), ('Hyderabad', 200), ('Sukkur', 250),
('Larkana', 250), ('Nawabshah', 220), ('Mirpurkhas', 230),
('Khairpur', 260), ('Jacobabad', 280), ('Shikarpur', 270), ('Dadu', 240);

-- Payment methods
INSERT INTO payment_methods (name, type, account_number, account_title, instructions, logo_icon, sort_order) VALUES
('EasyPaisa', 'easypaisa', '0300-1234567', 'Handy Services', 'Send payment to EasyPaisa number and upload screenshot as proof.', '📱', 1),
('JazzCash', 'jazzcash', '0311-9876543', 'Handy Services', 'Transfer to JazzCash account and upload transaction screenshot.', '💰', 2),
('Bank Transfer', 'bank', 'PK36MEZN0001060100887001\nMeezan Bank - Branch Saddar Karachi', 'Handy Services Pvt Ltd', 'Transfer to the bank account and upload your payment receipt.', '🏦', 3);
