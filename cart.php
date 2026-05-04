<?php
//Shopping Cart page
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update quantity
if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];
    $conn->query("UPDATE tblCart SET quantity = $quantity WHERE cart_id = $cart_id");
}

// Remove item
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    $conn->query("DELETE FROM tblCart WHERE cart_id = $cart_id");
}

// Get cart items
$sql = "SELECT c.cart_id, c.quantity, p.* FROM tblCart c 
        JOIN tblProducts p ON c.product_id = p.product_id 
        WHERE c.user_id = $user_id";
$cart_items = $conn->query($sql);

$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pastimes</title>
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
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            margin-left: 1.5rem;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        h1 {
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
        }
        
        .quantity-input {
            width: 60px;
            padding: 0.25rem;
        }
        
        .remove-link {
            color: #f44336;
            text-decoration: none;
        }
        
        .cart-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1.5rem;
            text-align: right;
        }
        
        .cart-summary p {
            margin: 0.5rem 0;
        }
        
        .total {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c5f2d;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #ff6b35;
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead {
                display: none;
            }
            tr {
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border: none;
            }
            td::before {
                content: attr(data-label);
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Pastimes♻️</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="cart-container">
        <h1>Shopping Cart</h1>
        
        <?php if($cart_items->num_rows > 0): ?>
            <table>
                <thead>
                    <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
                </thead>
                <tbody>
                    <?php while($item = $cart_items->fetch_assoc()): 
                        $item_subtotal = $item['price'] * $item['quantity'];
                        $subtotal += $item_subtotal;
                    ?>
                    <tr>
                        <td data-label="Product">
                            <strong><?php echo htmlspecialchars($item['title']); ?></strong><br>
                            <small><?php echo htmlspecialchars($item['brand']); ?> | <?php echo $item['condition_rating']; ?></small>
                        </td>
                        <td data-label="Price">R <?php echo number_format($item['price'], 2); ?></td>
                        <td data-label="Quantity">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input">
                                <button type="submit" name="update_qty" class="btn" style="padding:0.25rem 0.5rem; font-size:0.7rem;">Update</button>
                            </form>
                        </td>
                        <td data-label="Subtotal">R <?php echo number_format($item_subtotal, 2); ?></td>
                        <td data-label="Action">
                            <a href="?remove=<?php echo $item['cart_id']; ?>" class="remove-link" onclick="return confirm('Remove item?')">Remove</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div class="cart-summary">
                <p>Subtotal: R <?php echo number_format($subtotal, 2); ?></p>
                <p>Shipping: R <?php echo number_format(50, 2); ?></p>
                <p class="total">Total: R <?php echo number_format($subtotal + 50, 2); ?></p>
                <div style="margin-top:1rem;">
                    <input type="text" placeholder="Coupon Code" style="padding:0.5rem; border:1px solid #ddd; border-radius:5px; width:200px;">
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout →</a>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <p>🛒 Your cart is empty</p>
                <a href="shop.php" class="btn" style="margin-top:1rem;">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>