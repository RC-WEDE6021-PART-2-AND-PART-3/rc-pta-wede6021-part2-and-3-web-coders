<?php
//User Dashboard page
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user orders
$orders = $conn->query("SELECT * FROM tblOrders WHERE buyer_id = $user_id ORDER BY order_date DESC");

// Get wishlist
$wishlist = $conn->query("SELECT w.*, p.title, p.price, p.brand FROM tblWishlist w 
                          JOIN tblProducts p ON w.product_id = p.product_id 
                          WHERE w.user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pastimes</title>
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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #2c5f2d, #1a3b1a);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .dashboard-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .tab-btn {
            padding: 0.75rem 1.5rem;
            background: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .tab-btn.active {
            background: #2c5f2d;
            color: white;
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .tab-content.active {
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
        }
        
        .status-pending { background: #fff3e0; color: #ff9800; }
        .status-confirmed { background: #e3f2fd; color: #2196f3; }
        .status-shipped { background: #e8f5e9; color: #4caf50; }
        .status-delivered { background: #c8e6c9; color: #2e7d32; }
        
        .seller-hub {
            background: #f0f7f0;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
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
            }
            td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem;
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
            <a href="cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo $_SESSION['name']; ?>!</h1>
            <p>Manage your orders, wishlist, and more</p>
        </div>
        
        <div class="dashboard-tabs">
            <button class="tab-btn active" onclick="showTab('orders')">📦 Orders</button>
            <button class="tab-btn" onclick="showTab('wishlist')">❤️ Wishlist</button>
            <button class="tab-btn" onclick="showTab('messages')">💬 Messages</button>
            <?php 
            $is_seller = $conn->query("SELECT is_seller FROM tblUser WHERE user_id = $user_id")->fetch_assoc();
            if($is_seller && $is_seller['is_seller']): ?>
            <button class="tab-btn" onclick="showTab('seller')">🛍️ Seller Hub</button>
            <?php endif; ?>
        </div>
        
        <!-- Orders Tab -->
        <div id="orders" class="tab-content active">
            <h2>My Orders</h2>
            <?php if($orders->num_rows > 0): ?>
            <table>
                <thead>
                    <tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th>Tracking</th></tr>
                </thead>
                <tbody>
                    <?php while($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Order #">#<?php echo $order['order_id']; ?></td>
                        <td data-label="Date"><?php echo $order['order_date']; ?></td>
                        <td data-label="Total">R <?php echo number_format($order['total_amount'], 2); ?></td>
                        <td data-label="Status"><span class="status-badge status-<?php echo $order['order_status']; ?>"><?php echo ucfirst($order['order_status']); ?></span></td>
                        <td data-label="Tracking"><?php echo $order['tracking_number'] ?: 'Pending'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No orders yet. <a href="shop.php">Start shopping!</a></p>
            <?php endif; ?>
        </div>
        
        <!-- Wishlist Tab -->
        <div id="wishlist" class="tab-content">
            <h2>My Wishlist</h2>
            <?php if($wishlist->num_rows > 0): ?>
            <table>
                <thead><tr><th>Product</th><th>Brand</th><th>Price</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while($item = $wishlist->fetch_assoc()): ?>
                    <tr>
                        <td data-label="Product"><?php echo htmlspecialchars($item['title']); ?></td>
                        <td data-label="Brand"><?php echo $item['brand']; ?></td>
                        <td data-label="Price">R <?php echo number_format($item['price'], 2); ?></td>
                        <td data-label="Action"><a href="product.php?id=<?php echo $item['product_id']; ?>">View →</a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>Your wishlist is empty.</p>
            <?php endif; ?>
        </div>
        
        <!-- Messages Tab -->
        <div id="messages" class="tab-content">
            <h2>Messages</h2>
            <div class="seller-hub">
                <p>💬 Message system coming soon! Buyers and sellers will be able to communicate directly about products.</p>
            </div>
        </div>
        
        <!-- Seller Hub Tab -->
        <div id="seller" class="tab-content">
            <h2>Seller Hub</h2>
            <div class="seller-hub">
                <h3>📊 Listing Performance</h3>
                <p>Track your sales and manage your inventory here.</p>
                <p style="margin-top:1rem;">📈 Total Views: 0 | ❤️ Likes: 0 | 🛒 Offers: 0</p>
            </div>
            <div class="seller-hub">
                <h3>➕ Request to Add Product</h3>
                <p>Contact admin to list your items for sale.</p>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }
    </script>
</body>
</html>