<?php

//load clothing store page
//Creates complete database structure
include 'DBConn.php';

// Drop all tables if they exist
$tables = ['tblOrderItems', 'tblOrders', 'tblCart', 'tblMessages', 'tblWishlist', 'tblProducts', 'tblCategories', 'tblUser'];
foreach ($tables as $table) {
    $conn->query("DROP TABLE IF EXISTS $table");
}

// Create tblUser
$conn->query("CREATE TABLE tblUser (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT DEFAULT 0,
    is_admin TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create tblCategories
$conn->query("CREATE TABLE tblCategories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    parent_id INT DEFAULT NULL
)");

// Create tblProducts
$conn->query("CREATE TABLE tblProducts (
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
)");

// Create tblCart
$conn->query("CREATE TABLE tblCart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id) ON DELETE CASCADE
)");

// Create tblOrders
$conn->query("CREATE TABLE tblOrders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT,
    delivery_address TEXT,
    total_amount DECIMAL(10,2),
    order_status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    tracking_number VARCHAR(100),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES tblUser(user_id) ON DELETE SET NULL
)");

// Create tblOrderItems
$conn->query("CREATE TABLE tblOrderItems (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    price_at_purchase DECIMAL(10,2),
    quantity INT,
    FOREIGN KEY (order_id) REFERENCES tblOrders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id) ON DELETE SET NULL
)");

// Create tblMessages
$conn->query("CREATE TABLE tblMessages (
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
)");

// Create tblWishlist
$conn->query("CREATE TABLE tblWishlist (
    wishlist_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    product_id INT,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tblUser(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES tblProducts(product_id) ON DELETE CASCADE
)");

// Insert sample categories
$conn->query("INSERT INTO tblCategories (category_name) VALUES 
    ('Men\'s Clothing'), ('Women\'s Clothing'), ('Accessories'), ('Shoes')");

// Insert sample products
$conn->query("INSERT INTO tblProducts (seller_id, category_id, brand, title, description, condition_rating, price, size, main_image, verified_by_admin, status) VALUES 
    (1, 2, 'Nike', 'Nike Air Max Sneakers', 'Great condition, worn only twice', 'Excellent', 89.99, 'US 9', 'nike_airmax.jpg', 1, 'available'),
    (1, 2, 'Adidas', 'Adidas Originals Hoodie', 'Like new condition', 'Like New', 45.00, 'M', 'adidas_hoodie.jpg', 1, 'available'),
    (1, 1, 'Levi\'s', 'Levi\'s 501 Jeans', 'Classic fit, good condition', 'Good', 35.00, '32x32', 'levis_501.jpg', 1, 'available'),
    (1, 2, 'Zara', 'Zara Blazer', 'Perfect for formal occasions', 'Excellent', 55.00, 'L', 'zara_blazer.jpg', 1, 'available'),
    (1, 3, 'Gucci', 'Gucci Belt', 'Authentic Gucci belt', 'Like New', 150.00, 'One Size', 'gucci_belt.jpg', 1, 'available')");

echo "Database structure created successfully!";
$conn->close();
?>