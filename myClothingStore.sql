-- File: myClothingStore.sql
-- Database export for Pastimes E-commerce Platform

CREATE DATABASE IF NOT EXISTS myClothingStore;
USE myClothingStore;

-- Table: tblUser
DROP TABLE IF EXISTS tblUser;
CREATE TABLE tblUser (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT DEFAULT 0,
    is_admin TINYINT DEFAULT 0,
    is_seller TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user (password: admin123)
INSERT INTO tblUser (name, surname, email, username, password, is_verified, is_admin, is_seller) VALUES 
('Admin', 'User', 'admin@pastimes.com', 'admin', '$2y$10$1K2rYIcXe90a2pOpf16XXu7samM7qRC7X/cBcTAHTYiDnbteOVDam', 1, 1, 0);

-- Insert regular verified users
INSERT INTO tblUser (name, surname, email, username, password, is_verified, is_seller) VALUES 
('Ditebogo', 'Nkwana', 'ditebogo@example.com', 'ditebogo', '$2y$10$examplehash1', 1, 0),
('Motswapo', 'Rakubu', 'motswapo@example.com', 'motswapo', '$2y$10$examplehash2', 1, 0),
('Molatelo', 'Dibetso', 'molatelo@example.com', 'molatelo', '$2y$10$examplehash3', 1, 0);

-- Insert pending users
INSERT INTO tblUser (name, surname, email, username, password, is_verified, is_seller) VALUES 
('Dineo', 'Nkuna', 'dineo@example.com', 'dineon', '$2y$10$examplehash4', 0, 0),
('Tumisho', 'Nkwana', 'tumisho@example.com', 'tumisho', '$2y$10$examplehash5', 0, 0);

-- Table: tblCategories
DROP TABLE IF EXISTS tblCategories;
CREATE TABLE tblCategories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL
);

INSERT INTO tblCategories (category_name) VALUES 
('Men\'s Clothing'), ('Women\'s Clothing'), ('Accessories'), ('Shoes');

-- Table: tblProducts
DROP TABLE IF EXISTS tblProducts;
CREATE TABLE tblProducts (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT,
    category_id INT,
    brand VARCHAR(100),
    title VARCHAR(200) NOT NULL,
    description TEXT,
    condition_rating ENUM('Like New', 'Excellent', 'Good', 'Fair'),
    price DECIMAL(10,2),
    size VARCHAR(20),
    main_image VARCHAR(255),
    status ENUM('available', 'sold', 'removed') DEFAULT 'available',
    verified_by_admin TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES tblUser(user_id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES tblCategories(category_id) ON DELETE SET NULL
);

INSERT INTO tblProducts (seller_id, category_id, brand, title, description, condition_rating, price, size, main_image, verified_by_admin, status) VALUES 
(1, 2, 'Nike', 'Nike Air Max 90', 'Classic sneakers, worn only twice', 'Excellent', 899.99, 'US 9', 'nike_airmax.jpg', 1, 'available'),
(1, 1, 'Levis', 'Levi\'s 501 Jeans', 'Original fit, like new condition', 'Like New', 450.00, '32x32', 'Gucci_belt.jpg', 1, 'available'),
(1, 2, 'Zara', 'Zara Wool Blazer', 'Perfect for formal occasions', 'Excellent', 550.00, 'L', 'Zara_blazer.png', 1, 'available'),
(2, 3, 'Gucci', 'Gucci GG Belt', 'Authentic Gucci belt', 'Like New', 1500.00, 'One Size', 'Gucci_belt.jpg', 1, 'available'),
(2, 4, 'Adidas', 'Adidas Ultraboost', 'Comfortable running shoes', 'Good', 1200.00, 'US 10', 'adidas_ultraboost.jpg', 1, 'available'),
(3, 2, 'H&M', 'H&M Denim Jacket', 'Trendy denim jacket', 'Excellent', 350.00, 'M', 'hm_jacket.jpg', 1, 'available'),
(3, 1, 'Ralph Lauren', 'Polo Shirt', 'Classic polo, good condition', 'Good', 250.00, 'L', 'polo_shirt.jpg', 1, 'available'),
(4, 2, 'Nike', 'Nike Sportswear Hoodie', 'Warm and comfortable', 'Excellent', 400.00, 'XL', 'nike_hoodie.jpg', 1, 'available'),
(4, 3, 'Michael Kors', 'MK Tote Bag', 'Designer handbag', 'Good', 850.00, 'One Size', 'mk_bag.jpg', 1, 'available'),
(5, 4, 'Vans', 'Vans Old Skool', 'Classic skate shoes', 'Like New', 550.00, 'US 8', 'vans_oldskool.jpg', 1, 'available');

-- Table: tblCart
DROP TABLE IF EXISTS tblCart;
CREATE TABLE tblCart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id) ON DELETE CASCADE
);

-- Table: tblOrders
DROP TABLE IF EXISTS tblOrders;
CREATE TABLE tblOrders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT,
    delivery_address TEXT,
    total_amount DECIMAL(10,2),
    order_status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES tblUser(user_id) ON DELETE SET NULL
);

INSERT INTO tblOrders (buyer_id, delivery_address, total_amount, order_status) VALUES 
(1, '123 Main St, Johannesburg, 2001', 1349.99, 'delivered'),
(2, '45 Oak Ave, Cape Town, 8001', 450.00, 'shipped');

-- Table: tblOrderItems
DROP TABLE IF EXISTS tblOrderItems;
CREATE TABLE tblOrderItems (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    price_at_purchase DECIMAL(10,2),
    quantity INT,
    FOREIGN KEY (order_id) REFERENCES tblOrders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id) ON DELETE SET NULL
);

INSERT INTO tblOrderItems (order_id, product_id, price_at_purchase, quantity) VALUES 
(1, 1, 899.99, 1),
(1, 3, 450.00, 1),
(2, 2, 450.00, 1);

-- Table: tblMessages
DROP TABLE IF EXISTS tblMessages;
CREATE TABLE tblMessages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    receiver_id INT,
    product_id INT,
    message_text TEXT,
    is_read TINYINT DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES tblUser(user_id),
    FOREIGN KEY (receiver_id) REFERENCES tblUser(user_id),
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id)
);

-- Table: tblWishlist
DROP TABLE IF EXISTS tblWishlist;
CREATE TABLE tblWishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id) ON DELETE CASCADE
);

INSERT INTO tblWishlist (user_id, product_id) VALUES 
(1, 5), (1, 7), (2, 4);