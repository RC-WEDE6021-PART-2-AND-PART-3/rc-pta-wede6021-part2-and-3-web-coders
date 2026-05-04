<?php
//Shop Page
session_start();
include 'DBConn.php';

// Build filter query
$where = "WHERE p.status = 'available' AND p.verified_by_admin = 1";
$params = [];
$types = "";

if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $where .= " AND p.brand LIKE ?";
    $params[] = "%" . $_GET['brand'] . "%";
    $types .= "s";
}
if (isset($_GET['size']) && !empty($_GET['size'])) {
    $where .= " AND p.size = ?";
    $params[] = $_GET['size'];
    $types .= "s";
}
if (isset($_GET['condition']) && !empty($_GET['condition'])) {
    $where .= " AND p.condition_rating = ?";
    $params[] = $_GET['condition'];
    $types .= "s";
}
if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
    $where .= " AND p.price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}
if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
    $where .= " AND p.price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

$order = " ORDER BY p.created_at DESC";
if (isset($_GET['sort'])) {
    switch($_GET['sort']) {
        case 'price_asc': $order = " ORDER BY p.price ASC"; break;
        case 'price_desc': $order = " ORDER BY p.price DESC"; break;
        case 'name_asc': $order = " ORDER BY p.title ASC"; break;
    }
}

$sql = "SELECT p.*, c.category_name FROM tblProducts p 
        LEFT JOIN tblCategories c ON p.category_id = c.category_id 
        $where $order";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

// Get unique brands for filter
$brands = $conn->query("SELECT DISTINCT brand FROM tblProducts WHERE brand IS NOT NULL AND brand != ''");
$sizes = $conn->query("SELECT DISTINCT size FROM tblProducts WHERE size IS NOT NULL AND size != ''");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Pastimes</title>
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
        
        .shop-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
        }
        
        /* Sidebar Filters */
        .filters-sidebar {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .filter-section {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .filter-section h3 {
            margin-bottom: 0.75rem;
            color: #333;
        }
        
        .filter-section input, .filter-section select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
        
        .price-range {
            display: flex;
            gap: 0.5rem;
        }
        
        .price-range input {
            width: 50%;
        }
        
        .filter-btn {
            width: 100%;
            padding: 0.5rem;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .reset-btn {
            background: #666;
            margin-top: 0.5rem;
        }
        
        /* Products Grid */
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .sort-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
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
            height: 200px;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .product-brand {
            font-size: 0.8rem;
            color: #666;
        }
        
        .product-price {
            color: #2c5f2d;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        
        .condition-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 5px;
            font-size: 0.7rem;
        }
        
        .condition-LikeNew { background: #4caf50; color: white; }
        .condition-Excellent { background: #8bc34a; color: #333; }
        .condition-Good { background: #ffc107; color: #333; }
        .condition-Fair { background: #ff9800; color: white; }
        
        .btn {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background: #2c5f2d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #ff6b35;
        }
        
        @media (max-width: 768px) {
            .shop-container {
                grid-template-columns: 1fr;
            }
            .filters-sidebar {
                position: static;
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
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="cart.php">Cart</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="shop-container">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <h2>Filters</h2>
            <form method="GET" action="">
                <div class="filter-section">
                    <h3>Search Brand</h3>
                    <input type="text" name="brand" placeholder="Search brand..." value="<?php echo isset($_GET['brand']) ? htmlspecialchars($_GET['brand']) : ''; ?>">
                </div>
                
                <div class="filter-section">
                    <h3>Size</h3>
                    <select name="size">
                        <option value="">All Sizes</option>
                        <?php while($size = $sizes->fetch_assoc()): ?>
                        <option value="<?php echo $size['size']; ?>" <?php echo (isset($_GET['size']) && $_GET['size'] == $size['size']) ? 'selected' : ''; ?>>
                            <?php echo $size['size']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-section">
                    <h3>Condition</h3>
                    <select name="condition">
                        <option value="">All Conditions</option>
                        <option value="Like New" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'Like New') ? 'selected' : ''; ?>>Like New</option>
                        <option value="Excellent" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'Excellent') ? 'selected' : ''; ?>>Excellent</option>
                        <option value="Good" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'Good') ? 'selected' : ''; ?>>Good</option>
                        <option value="Fair" <?php echo (isset($_GET['condition']) && $_GET['condition'] == 'Fair') ? 'selected' : ''; ?>>Fair</option>
                    </select>
                </div>
                
                <div class="filter-section">
                    <h3>Price Range</h3>
                    <div class="price-range">
                        <input type="number" name="min_price" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>">
                        <input type="number" name="max_price" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>">
                    </div>
                </div>
                
                <button type="submit" class="filter-btn">Apply Filters</button>
                <a href="shop.php" class="filter-btn reset-btn" style="display:block; text-align:center; text-decoration:none;">Reset</a>
            </form>
        </aside>
        
        <!-- Products Main Content -->
        <main>
            <div class="products-header">
                <p><?php echo $products->num_rows; ?> products found</p>
                <form method="GET" action="">
                    <?php foreach($_GET as $key => $value): ?>
                        <?php if($key != 'sort'): ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="">Sort by: Latest</option>
                        <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name: A to Z</option>
                    </select>
                </form>
            </div>
            
            <div class="products-grid">
                <?php if($products->num_rows > 0): ?>
                    <?php while($product = $products->fetch_assoc()): ?>
                        <?php $carbonSaved = round($product['price'] * 0.5, 2); ?>
                        <div class="product-card">
                            <div class="product-image">📷</div>
                            <div class="product-info">
                                <div class="product-title"><?php echo htmlspecialchars($product['title']); ?></div>
                                <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
                                <div class="product-price">R <?php echo number_format($product['price'], 2); ?></div>
                                <span class="condition-badge condition-<?php echo str_replace(' ', '', $product['condition_rating']); ?>">
                                    <?php echo $product['condition_rating']; ?>
                                </span>
                                <div style="font-size:0.7rem; color:#666; margin-top:0.25rem;">🌱 Save <?php echo $carbonSaved; ?> kg CO₂</div>
                                <div>
                                    <a href="product.php?id=<?php echo $product['product_id']; ?>" class="btn">View</a>
                                    <form action="add_to_cart.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found matching your criteria.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>