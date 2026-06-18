<?php
// File: order_history.php - NEW for Part 3
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$orders = $conn->query("SELECT * FROM tblOrders WHERE buyer_id = $user_id ORDER BY order_date DESC");
$grand_total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .navbar { background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #2c5f2d; text-decoration: none; }
        .nav-links a { text-decoration: none; color: #333; margin-left: 1.5rem; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        h1 { margin-bottom: 1.5rem; }
        .order-card { background: white; border-radius: 10px; margin-bottom: 1.5rem; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .order-header { background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; flex-wrap: wrap; }
        .order-body { padding: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .grand-total { background: #2c5f2d; color: white; padding: 1rem; border-radius: 10px; text-align: right; font-size: 1.2rem; margin-top: 1rem; }
        .status-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; }
        .status-pending { background: #fff3e0; color: #ff9800; }
        .status-delivered { background: #c8e6c9; color: #2e7d32; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Pastimes♻️</a>
        <div class="nav-links"><a href="index.php">Home</a><a href="shop.php">Shop</a><a href="cart.php">Cart</a><a href="logout.php">Logout</a></div>
    </nav>
    
    <div class="container">
        <h1>📦 My Order History</h1>
        
        <?php if($orders->num_rows > 0): ?>
            <?php while($order = $orders->fetch_assoc()): 
                $grand_total += $order['total_amount'];
                $items = $conn->query("SELECT oi.*, p.title, p.brand FROM tblOrderItems oi JOIN tblProducts p ON oi.product_id = p.product_id WHERE oi.order_id = {$order['order_id']}");
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div><strong>Order #:</strong> <?php echo $order['order_number'] ?? $order['order_id']; ?><br><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['order_date'])); ?></div>
                    <div><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span><br><strong>Tracking:</strong> <?php echo $order['tracking_number'] ?? 'Pending'; ?></div>
                    <div><strong>Total:</strong> R <?php echo number_format($order['total_amount'], 2); ?></div>
                </div>
                <div class="order-body">
                    <table><thead><tr><th>Product</th><th>Brand</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody><?php while($item = $items->fetch_assoc()): ?>
                        <tr><td><?php echo htmlspecialchars($item['title']); ?></td><td><?php echo $item['brand']; ?></td><td>R <?php echo number_format($item['price_at_purchase'], 2); ?></td><td><?php echo $item['quantity']; ?></td><td>R <?php echo number_format($item['price_at_purchase'] * $item['quantity'], 2); ?></td></tr>
                    <?php endwhile; ?></tbody>
                    </table>
                </div>
            </div>
            <?php endwhile; ?>
            
            <div class="grand-total">
                🛍️ GRAND TOTAL OF ALL PURCHASES: R <?php echo number_format($grand_total, 2); ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:3rem; background:white; border-radius:10px;"><p>No orders yet.</p><a href="shop.php" style="display:inline-block; margin-top:1rem; padding:0.5rem 1rem; background:#2c5f2d; color:white; text-decoration:none; border-radius:5px;">Start Shopping →</a></div>
        <?php endif; ?>
    </div>
</body>
</html>