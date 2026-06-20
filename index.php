<?php
// File: index.php - UPDATED with clear eShop type and goals
include 'DBConn.php';

// Get cart count for display
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $result = $conn->query("SELECT SUM(quantity) as total FROM tblCart WHERE user_id = {$_SESSION['user_id']}");
    if ($row = $result->fetch_assoc()) {
        $cart_count = $row['total'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Second-Hand Branded Clothing</title>
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
            flex-wrap: wrap;
        }
        .logo { font-size: 1.8rem; font-weight: bold; color: #2c5f2d; text-decoration: none; }
        .logo span { color: #ff6b35; }
        .nav-links a { text-decoration: none; color: #333; margin-left: 1.5rem; }
        .cart-count { background: #ff6b35; color: white; border-radius: 50%; padding: 2px 8px; font-size: 0.7rem; margin-left: 5px; }
        
        .hero {
            background: linear-gradient(135deg, #2c5f2d 0%, #1a3b1a 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        .hero h1 { font-size: 3rem; margin-bottom: 1rem; }
        .hero p { font-size: 1.2rem; margin-bottom: 1rem; }
        
        .goals-section {
            background: white;
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .goals-section h2 { color: #2c5f2d; margin-bottom: 1rem; }
        .goals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 1rem;
        }
        .goal-card {
            text-align: center;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .goal-card .icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .goal-card h3 { color: #2c5f2d; margin-bottom: 0.5rem; }
        
        .featured {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        .section-title { font-size: 2rem; margin-bottom: 2rem; color: #333; }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .product-card:hover { transform: translateY(-5px); }
        .product-image {
            width: 100%;
            height: 200px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        .product-info { padding: 1rem; }
        .product-title { font-weight: 600; margin-bottom: 0.25rem; }
        .product-price { color: #2c5f2d; font-size: 1.2rem; font-weight: bold; margin: 0.5rem 0; }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #2c5f2d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
        .btn-primary { background: #ff6b35; }
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .nav-links a { margin-left: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">Pastimes<span>♻️</span></a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="shop.php">Shop</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="order_history.php">My Orders</a>
                <a href="seller_request.php">Sell</a>
                <a href="cart.php">Cart <?php if($cart_count > 0) echo "<span class='cart-count'>$cart_count</span>"; ?></a>
                <a href="logout.php">Logout (<?php echo $_SESSION['username']; ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
                <a href="cart.php">Cart</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <section class="hero">
        <h1>Pre-Loved Fashion,<br>Second Chance</h1>
        <p>Sustainable second-hand branded clothing for South Africa</p>
        <div style="background: rgba(255,255,255,0.2); display: inline-block; padding: 0.5rem 1rem; border-radius: 30px;">
            🌍 Together we saved 1,245 kg CO₂ through second-hand shopping!
        </div>
    </section>
    
    <!-- GOALS STATEMENT SECTION - Required for rubric -->
    <div class="goals-section">
        <h2>🎯 Our Mission</h2>
        <div class="goals-grid">
            <div class="goal-card">
                <div class="icon">♻️</div>
                <h3>Sustainable Fashion</h3>
                <p>Reducing textile waste by giving pre-loved clothes a second life</p>
            </div>
            <div class="goal-card">
                <div class="icon">💰</div>
                <h3>Affordable Quality</h3>
                <p>Making branded fashion accessible to all South Africans</p>
            </div>
            <div class="goal-card">
                <div class="icon">🤝</div>
                <h3>Trusted Community</h3>
                <p>Verified sellers and buyer protection for safe transactions</p>
            </div>
            <div class="goal-card">
                <div class="icon">📦</div>
                <h3>Easy Selling</h3>
                <p>Simple process to sell your pre-loved items</p>
            </div>
        </div>
    </div>
    
    <section class="featured">
        <h2 class="section-title">Featured Items</h2>
        <div class="products-grid">
            <?php
            $sql = "SELECT p.*, c.category_name FROM tblProducts p 
                    LEFT JOIN tblCategories c ON p.category_id = c.category_id 
                    WHERE p.status = 'available' AND p.verified_by_admin = 1 
                    LIMIT 4";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<div class="product-card">
                            <div class="product-image">📷 ' . htmlspecialchars($row['title']) . '</div>
                            <div class="product-info">
                                <div class="product-title">' . htmlspecialchars($row['brand']) . ' - ' . htmlspecialchars($row['title']) . '</div>
                                <div class="product-price">R ' . number_format($row['price'], 2) . '</div>
                                <a href="shop.php" class="btn">Shop Now →</a>
                            </div>
                        </div>';
                }
            } else {
                echo '<p>No products available yet. Check back soon!</p>';
            }
            ?>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="shop.php" class="btn btn-primary" style="padding: 0.75rem 2rem;">View All Products →</a>
        </div>
    </section>
    
    <footer class="footer">
        <p>Pastimes - Sustainable Second-Hand Fashion | © 2026</p>
        <p>♻️ Every purchase saves clothing from landfills | Trusted since 2026</p>
    </footer>
</body>
</html>