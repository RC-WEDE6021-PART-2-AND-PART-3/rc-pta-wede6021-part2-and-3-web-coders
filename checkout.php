<?php
//Delivery Details page
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address_type = $_POST['address_type'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $phone = $_POST['phone'];
    
    // Get cart total
    $cart_total = $conn->query("SELECT SUM(p.price * c.quantity) as total FROM tblCart c 
                                JOIN tblProducts p ON c.product_id = p.product_id 
                                WHERE c.user_id = $user_id")->fetch_assoc();
    $total_amount = $cart_total['total'] + 50; // + shipping
    
    // Create order
    $order_sql = "INSERT INTO tblOrders (buyer_id, delivery_address, total_amount, order_status) 
                  VALUES (?, ?, ?, 'pending')";
    $stmt = $conn->prepare($order_sql);
    $address = "$street, $city, $postal_code";
    $stmt->bind_param("isd", $user_id, $address, $total_amount);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Move cart items to order_items
    $cart_items = $conn->query("SELECT c.product_id, c.quantity, p.price FROM tblCart c 
                                JOIN tblProducts p ON c.product_id = p.product_id 
                                WHERE c.user_id = $user_id");
    
    while($item = $cart_items->fetch_assoc()) {
        $order_item_sql = "INSERT INTO tblOrderItems (order_id, product_id, price_at_purchase, quantity) 
                           VALUES (?, ?, ?, ?)";
        $stmt2 = $conn->prepare($order_item_sql);
        $stmt2->bind_param("iidi", $order_id, $item['product_id'], $item['price'], $item['quantity']);
        $stmt2->execute();
    }
    
    // Clear cart
    $conn->query("DELETE FROM tblCart WHERE user_id = $user_id");
    
    $success = "Order placed successfully! Order #$order_id";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pastimes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c5f2d;
            text-decoration: none;
        }
        
        .checkout-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .checkout-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }
        
        .success {
            background: #e8f5e9;
            color: #2c5f2d;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Pastimes♻️</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="checkout-container">
        <div class="checkout-card">
            <h1>Delivery Details</h1>
            
            <?php if($success): ?>
                <div class="success">✅ <?php echo $success; ?></div>
                <a href="shop.php" class="btn">Continue Shopping</a>
            <?php else: ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Address Type</label>
                        <select name="address_type">
                            <option value="Home">Home</option>
                            <option value="Work">Work</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" name="street" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" required>
                    </div>
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" required>
                    </div>
                    <button type="submit" class="btn">Place Order</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>