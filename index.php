<?php
//Home Page
session_start();
include 'DBConn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pastimes - Second-Hand Branded Clothing</title>
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
        
        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c5f2d;
            text-decoration: none;
        }
        
        .logo span {
            color: #ff6b35;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #2c5f2d;
        }
        
        .cart-icon {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -12px;
            background: #ff6b35;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
        }
        
        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #2c5f2d 0%, #1a3b1a 100%);
            color: white;
            padding: 4rem 2rem;
            text-align: center;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .search-bar {
            max-width: 500px;
            margin: 0 auto;
            display: flex;
            gap: 0.5rem;
        }
        
        .search-bar input {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
        }
        
        .search-bar button {
            padding: 1rem 2rem;
            background: #ff6b35;
            border: none;
            border-radius: 30px;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }
        
        /* Sustainability Badge */
        .sustainability-badge {
            background: #e8f5e9;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
            margin-top: 1rem;
            color: #2c5f2d;
        }
        
        /* Featured Products */
        .featured {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 0 2rem;
        }
        
        .section-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            color: #333;
        }
        
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
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .product-price {
            color: #2c5f2d;
            font-size: 1.3rem;
            font-weight: bold;
        }
        
        .condition-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 5px;
            font-size: 0.7rem;
            margin-top: 0.5rem;
        }
        
        .condition-LikeNew { background: #4caf50; color: white; }
        .condition-Excellent { background: #8bc34a; color: #333; }
        .condition-Good { background: #ffc107; color: #333; }
        .condition-Fair { background: #ff9800; color: white; }
        
        .carbon-saved {
            font-size: 0.7rem;
            color: #666;
            margin-top: 0.5rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #2c5f2d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 0.5rem;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #ff6b35;
        }
        
        /* Footer */
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            .hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Pastimes<span>♻️</span></a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="shop.php">Shop</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="logout.php">Logout (<?php echo $_SESSION['name']; ?>)</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
                <a href="cart.php" class="cart-icon">🛒 <span class="cart-count">0</span></a>
            </div>
        </div>
    </nav>
    
    <section class="hero">
        <h1>Pre-Loved Fashion,<br>Second Chance</h1>
        <p>Shop sustainable second-hand branded clothing in South Africa</p>
        <div class="search-bar">
            <input type="text" placeholder="Search for brands, items...">
            <button>Search</button>
        </div>
        <div class="sustainability-badge">
            🌍 Together we saved 1,245 kg CO2 through second-hand shopping!
        </div>
    </section>
    
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
                    $carbonSaved = round($row['price'] * 0.5, 2);
                    echo '<div class="product-card">
                            <div class="product-image"><img src="images/' . htmlspecialchars($row['main_image']) . '" alt="' . htmlspecialchars($row['title']) . '"></div>
                            <div class="product-info">
                                <div class="product-title">' . htmlspecialchars($row['brand']) . ' - ' . htmlspecialchars($row['title']) . '</div>
                                <div class="product-price">R ' . number_format($row['price'], 2) . '</div>
                                <span class="condition-badge condition-' . str_replace(' ', '', $row['condition_rating']) . '">' . $row['condition_rating'] . '</span>
                                <div class="carbon-saved">🌱 Estimated ' . $carbonSaved . ' kg CO₂ saved</div>
                                <a href="product.php?id=' . $row['product_id'] . '" class="btn">View Details</a>
                                <form action="add_to_cart.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="product_id" value="' . $row['product_id'] . '">
                                    <button type="submit" class="btn btn-primary">Add to Cart 🛒</button>
                                </form>
                            </div>
                        </div>';
                }
            } else {
                echo '<p>No products available yet.</p>';
            }
            ?>
        </div>
    </section>
    
    <footer class="footer">
        <p>Pastimes - Sustainable Second-Hand Fashion | © 2026</p>
        <p>♻️ Every purchase saves clothing from landfills</p>
    </footer>
</body>
</html>
<?php $conn->close(); ?>