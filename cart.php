<?php
// File: cart.php - UPDATED with proper checkout that writes to orderLine
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after'] = 'cart';
    header("Location: login.php?message=Please login to view cart");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update quantity
if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    $conn->query("UPDATE tblCart SET quantity = $quantity WHERE cart_id = $cart_id");
}

// Remove item
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $conn->query("DELETE FROM tblCart WHERE cart_id = $cart_id");
}

// Empty cart
if (isset($_POST['empty_cart'])) {
    $conn->query("DELETE FROM tblCart WHERE user_id = $user_id");
    header("Location: cart.php");
    exit();
}

// Get cart items
$cart_items = $conn->query("SELECT c.cart_id, c.quantity, p.* FROM tblCart c 
                            JOIN tblProducts p ON c.product_id = p.product_id 
                            WHERE c.user_id = $user_id AND p.status = 'available'");

$subtotal = 0;
while($item = $cart_items->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
}
$cart_items->data_seek(0); // Reset pointer
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo { font-size: 1.5rem; font-weight: bold; color: #2c5f2d; text-decoration: none; }
        .nav-links a { text-decoration: none; color: #333; margin-left: 1.5rem; }
        
        .cart-container { max-width: 1200px; margin: 2rem auto; padding: 0 2rem; }
        h1 { margin-bottom: 1.5rem; color: #333; }
        
        table { width: 100%; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        
        .quantity-input { width: 60px; padding: 0.25rem; text-align: center; }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: #2c5f2d; color: white; }
        .btn-warning { background: #ff6b35; color: white; }
        .btn-danger { background: #f44336; color: white; }
        .btn-secondary { background: #666; color: white; }
        
        .cart-summary { background: white; padding: 1.5rem; border-radius: 10px; margin-top: 1.5rem; text-align: right; }
        .total { font-size: 1.5rem; font-weight: bold; color: #2c5f2d; }
        .action-buttons { display: flex; gap: 10px; margin-top: 1rem; justify-content: flex-end; }
        
        .empty-cart { text-align: center; padding: 3rem; background: white; border-radius: 10px; }
        
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            tr { margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 5px; }
            td { display: flex; justify-content: space-between; padding: 0.5rem; }
            td::before { content: attr(data-label); font-weight: bold; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Pastimes♻️</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="order_history.php">My Orders</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="cart-container">
        <h1>🛒 Shopping Cart</h1>
        
        <?php if($cart_items->num_rows > 0): ?>
            <form method="POST">
                <table>
                    <thead>
                        <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php while($item = $cart_items->fetch_assoc()): 
                            $item_total = $item['price'] * $item['quantity'];
                        ?>
                        <tr>
                            <td data-label="Product"><strong><?php echo htmlspecialchars($item['title']); ?></strong><br><small><?php echo $item['brand']; ?> | <?php echo $item['condition_rating']; ?></small></td>
                            <td data-label="Price">R <?php echo number_format($item['price'], 2); ?></td>
                            <td data-label="Quantity">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" onchange="this.form.submit()">
                                    <input type="hidden" name="update_qty" value="1">
                                </form>
                            </td>
                            <td data-label="Subtotal">R <?php echo number_format($item_total, 2); ?></td>
                            <td data-label="Action"><a href="?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger" style="padding:0.25rem 0.5rem;" onclick="return confirm('Remove item?')">Remove</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
            
            <div class="cart-summary">
                <p>Subtotal: R <?php echo number_format($subtotal, 2); ?></p>
                <p>Shipping: R 50.00</p>
                <p class="total">Total: R <?php echo number_format($subtotal + 50, 2); ?></p>
                
                <div class="action-buttons">
                    <form method="POST" style="display:inline;">
                        <button type="submit" name="empty_cart" class="btn btn-danger" onclick="return confirm('Empty cart?')">Empty Cart</button>
                    </form>
                    <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout →</a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p style="font-size: 3rem;">🛒</p>
                <p>Your cart is empty</p>
                <a href="shop.php" class="btn btn-primary" style="margin-top: 1rem;">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>