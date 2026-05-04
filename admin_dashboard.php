<?php
//Admin Dashboard page
session_start();
include 'DBConn.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// Handle user verification
if (isset($_POST['verify_user'])) {
    $user_id = $_POST['user_id'];
    $conn->query("UPDATE tblUser SET is_verified = 1 WHERE user_id = $user_id");
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $conn->query("DELETE FROM tblUser WHERE user_id = $user_id");
}

// Handle product addition
if (isset($_POST['add_product'])) {
    $seller_id = $_POST['seller_id'];
    $category_id = $_POST['category_id'];
    $brand = $_POST['brand'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $condition_rating = $_POST['condition_rating'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    
    $sql = "INSERT INTO tblProducts (seller_id, category_id, brand, title, description, condition_rating, price, size, verified_by_admin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssds", $seller_id, $category_id, $brand, $title, $description, $condition_rating, $price, $size);
    $stmt->execute();
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    $conn->query("DELETE FROM tblProducts WHERE product_id = $product_id");
}

// Handle product update
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $conn->query("UPDATE tblProducts SET price = $price, status = '$status' WHERE product_id = $product_id");
}

// Get unverified users
$unverified_users = $conn->query("SELECT * FROM tblUser WHERE is_verified = 0 AND is_admin = 0");
$all_users = $conn->query("SELECT * FROM tblUser WHERE is_admin = 0");
$products = $conn->query("SELECT * FROM tblProducts");
$categories = $conn->query("SELECT * FROM tblCategories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
        }
        
        .admin-header {
            background: #1a1a2e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 1.5rem;
        }
        
        .logout-btn {
            background: #ff6b35;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            margin-bottom: 1rem;
            color: #333;
            border-bottom: 2px solid #ff6b35;
            display: inline-block;
            padding-bottom: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        
        .btn-verify {
            background: #4caf50;
            color: white;
        }
        
        .btn-delete {
            background: #f44336;
            color: white;
        }
        
        .btn-add {
            background: #2196f3;
            color: white;
            padding: 0.5rem 1rem;
            margin-top: 1rem;
        }
        
        .btn-update {
            background: #ff9800;
            color: white;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.7rem;
        }
        
        .status-available { background: #e8f5e9; color: #2e7d32; }
        .status-sold { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🛡️ Pastimes Admin Dashboard</h1>
        <div>
            <span>Welcome, <?php echo $_SESSION['name']; ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Pending Verifications -->
        <div class="section">
            <h2>👥 Pending User Verifications</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($user = $unverified_users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['name'] . ' ' . $user['surname']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="verify_user" class="btn btn-verify">Verify</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-delete" onclick="return confirm('Delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($unverified_users->num_rows == 0): ?>
                    <tr><td colspan="5">No pending verifications.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- All Users -->
        <div class="section">
            <h2>📋 All Users</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Verified</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $all_users = $conn->query("SELECT * FROM tblUser WHERE is_admin = 0");
                    while($user = $all_users->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['name'] . ' ' . $user['surname']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['is_verified'] ? '✅' : '❌'; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-delete" onclick="return confirm('Delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Add Product -->
        <div class="section">
            <h2>➕ Add New Product</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Seller ID</label>
                        <input type="number" name="seller_id" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id">
                            <?php 
                            $cats = $conn->query("SELECT * FROM tblCategories");
                            while($cat = $cats->fetch_assoc()):
                            ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Brand</label>
                        <input type="text" name="brand">
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Condition</label>
                        <select name="condition_rating">
                            <option>Like New</option><option>Excellent</option><option>Good</option><option>Fair</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Price (R)</label>
                        <input type="number" step="0.01" name="price" required>
                    </div>
                    <div class="form-group">
                        <label>Size</label>
                        <input type="text" name="size">
                    </div>
                </div>
                <button type="submit" name="add_product" class="btn btn-add">Add Product</button>
            </form>
        </div>
        
        <!-- Product Management -->
        <div class="section">
            <h2>📦 Product Management</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Brand</th><th>Price</th><th>Condition</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $products = $conn->query("SELECT * FROM tblProducts");
                    while($product = $products->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><?php echo $product['title']; ?></td>
                        <td><?php echo $product['brand']; ?></td>
                        <td>R <?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['condition_rating']; ?></td>
                        <td><span class="status-badge status-<?php echo $product['status']; ?>"><?php echo $product['status']; ?></span></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <input type="hidden" name="price" value="<?php echo $product['price']; ?>">
                                <select name="status" style="padding:0.25rem;">
                                    <option value="available">Available</option>
                                    <option value="sold">Sold</option>
                                    <option value="removed">Removed</option>
                                </select>
                                <button type="submit" name="update_product" class="btn btn-update">Update</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-delete" onclick="return confirm('Delete this product?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>