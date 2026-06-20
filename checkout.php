<?php
// File: checkout.php - NEW for Part 3
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after'] = 'checkout';
    header("Location: login.php?message=Please login to checkout");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items
$cart_items = $conn->query("SELECT c.*, p.* FROM tblCart c 
                            JOIN tblProducts p ON c.product_id = p.product_id 
                            WHERE c.user_id = $user_id");

if ($cart_items->num_rows == 0) {
    header("Location: shop.php?message=Your cart is empty");
    exit();
}

$success = false;
$order_data = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['postal_code'];
    
    // Calculate total
    $subtotal = 0;
    $items = [];
    while ($item = $cart_items->fetch_assoc()) {
        $subtotal += $item['price'] * $item['quantity'];
        $items[] = $item;
    }
    $total = $subtotal + 50;
    
    // Generate tracking number
    $tracking_number = 'TRK-' . strtoupper(substr(md5(uniqid()), 0, 8));
    
    // CREATE ORDER in tblOrders
    $stmt = $conn->prepare("INSERT INTO tblOrders (buyer_id, delivery_address, total_amount, order_status, tracking_number) 
                            VALUES (?, ?, ?, 'pending', ?)");
    $stmt->bind_param("isds", $user_id, $address, $total, $tracking_number);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // WRITE ENTRIES TO orderLine (tblOrderItems) AND DECREMENT QUANTITY
    foreach ($items as $item) {
        // Insert into order items
        $order_item = $conn->prepare("INSERT INTO tblOrderItems (order_id, product_id, price_at_purchase, quantity) VALUES (?, ?, ?, ?)");
        $order_item->bind_param("iidi", $order_id, $item['product_id'], $item['price'], $item['quantity']);
        $order_item->execute();
    }
    
    // EMPTY CART after checkout
    $conn->query("DELETE FROM tblCart WHERE user_id = $user_id");
    
    $success = true;
    $order_data = [
        'tracking_number' => $tracking_number,
        'order_id' => $order_id,
        'total' => $total
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.8rem; font-weight: bold; color: #2c5f2d; text-decoration: none; }
        .checkout-container { max-width: 800px; margin: 2rem auto; padding: 0 2rem; }
        .card { background: white; border-radius: 10px; padding: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input, textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px; }
        .btn { width: 100%; padding: 0.75rem; background: #2c5f2d; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        .success-box { background: #e8f5e9; border: 2px solid #4caf50; border-radius: 10px; padding: 1.5rem; text-align: center; }
        .order-ref { background: #f0f7f0; padding: 1rem; border-radius: 5px; margin: 1rem 0; font-family: monospace; font-size: 1.1rem; }
    </style>
</head>
<body>
    <nav class="navbar"><a href="index.php" class="logo">Pastimes♻️</a></nav>
    
    <div class="checkout-container">
        <?php if($success): ?>
            <div class="success-box">
                <h1>✅ Order Confirmed!</h1>
                <div class="order-ref">
                    <strong>Order ID:</strong> <?php echo $order_data['order_id']; ?><br>
                    <strong>Tracking Number:</strong> <?php echo $order_data['tracking_number']; ?>
                </div>
                <p><strong>Total Paid:</strong> R <?php echo number_format($order_data['total'], 2); ?></p>
                <a href="order_history.php" class="btn" style="background:#ff6b35; text-decoration:none; display:inline-block; margin-top:1rem;">View My Orders →</a>
                <a href="shop.php" class="btn" style="background:#666; text-decoration:none; display:inline-block; margin-top:1rem;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="card">
                <h1>Delivery Details</h1>
                <form method="POST">
                    <div class="form-group"><label>Street Address</label><input type="text" name="street" required></div>
                    <div class="form-group"><label>City</label><input type="text" name="city" required></div>
                    <div class="form-group"><label>Postal Code</label><input type="text" name="postal_code" required></div>
                    <div class="form-group"><label>Phone</label><input type="tel" name="phone" required></div>
                    <button type="submit" class="btn">Place Order</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>