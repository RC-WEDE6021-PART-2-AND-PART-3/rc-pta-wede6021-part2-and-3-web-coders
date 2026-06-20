<?php
// File: admin_dashboard.php - UPDATED with full CRUD operations
session_start();
include 'DBConn.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: admin_login.php");
    exit();
}

// ============ USER MANAGEMENT CRUD ============
// Add User
if (isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $is_seller = isset($_POST['is_seller']) ? 1 : 0;
    
    $stmt = $conn->prepare("INSERT INTO tblUser (name, surname, email, username, password, is_seller, is_verified) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("sssssi", $name, $surname, $email, $username, $password, $is_seller);
    $stmt->execute();
    $success = "User added successfully!";
}

// Update User
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $is_seller = isset($_POST['is_seller']) ? 1 : 0;
    $is_verified = isset($_POST['is_verified']) ? 1 : 0;
    
    $stmt = $conn->prepare("UPDATE tblUser SET name=?, surname=?, email=?, username=?, is_seller=?, is_verified=? WHERE user_id=?");
    $stmt->bind_param("ssssiii", $name, $surname, $email, $username, $is_seller, $is_verified, $user_id);
    $stmt->execute();
    $success = "User updated successfully!";
}

// Delete User
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $conn->query("DELETE FROM tblUser WHERE user_id = $user_id AND is_admin = 0");
    $success = "User deleted successfully!";
}

// ============ PRODUCT MANAGEMENT CRUD ============
// Add Product
if (isset($_POST['add_product'])) {
    $seller_id = $_POST['seller_id'];
    $category_id = $_POST['category_id'];
    $brand = $_POST['brand'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $condition_rating = $_POST['condition_rating'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    
    $stmt = $conn->prepare("INSERT INTO tblProducts (seller_id, category_id, brand, title, description, condition_rating, price, size, verified_by_admin, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, 'available')");
    $stmt->bind_param("iissssds", $seller_id, $category_id, $brand, $title, $description, $condition_rating, $price, $size);
    $stmt->execute();
    $success = "Product added successfully!";
}

// Update Product
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $condition_rating = $_POST['condition_rating'];
    
    $stmt = $conn->prepare("UPDATE tblProducts SET price=?, status=?, condition_rating=? WHERE product_id=?");
    $stmt->bind_param("dssi", $price, $status, $condition_rating, $product_id);
    $stmt->execute();
    $success = "Product updated successfully!";
}

// Delete Product
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $conn->query("DELETE FROM tblProducts WHERE product_id = $product_id");
    $success = "Product deleted successfully!";
}

// Approve Product (from seller request)
if (isset($_POST['approve_product'])) {
    $product_id = $_POST['product_id'];
    $conn->query("UPDATE tblProducts SET verified_by_admin = 1, status = 'available' WHERE product_id = $product_id");
    $success = "Product approved and listed!";
}

if (isset($_POST['reject_product'])) {
    $product_id = $_POST['product_id'];
    $conn->query("DELETE FROM tblProducts WHERE product_id = $product_id");
    $success = "Product rejected and removed.";
}

// Get data for display
$users = $conn->query("SELECT * FROM tblUser WHERE is_admin = 0");
$products = $conn->query("SELECT * FROM tblProducts");
$categories = $conn->query("SELECT * FROM tblCategories");
$pending_products = $conn->query("SELECT p.*, u.name, u.surname FROM tblProducts p JOIN tblUser u ON p.seller_id = u.user_id WHERE p.verified_by_admin = 0 AND p.status = 'pending'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; }
        
        .admin-header {
            background: #1a1a2e;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .logo { font-size: 1.5rem; font-weight: bold; color: #ff6b35; text-decoration: none; }
        .logout-btn { background: #ff6b35; padding: 0.5rem 1rem; border-radius: 5px; text-decoration: none; color: white; }
        
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
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
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        
        .btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit { background: #ff9800; color: white; }
        .btn-delete { background: #f44336; color: white; }
        .btn-add { background: #4caf50; color: white; padding: 0.5rem 1rem; }
        .btn-approve { background: #4caf50; color: white; }
        .btn-reject { background: #f44336; color: white; }
        
        .form-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; }
        
        .success { background: #e8f5e9; color: #2c5f2d; padding: 0.75rem; border-radius: 5px; margin-bottom: 1rem; }
        
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
    <div class="admin-header">
        <a href="admin_dashboard.php" class="logo">🛡️ Pastimes Admin</a>
        <div>
            <a href="admin_communications.php" style="color:white; margin-right:1rem;">💬 Communications</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($success)): ?>
            <div class="success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Pending Seller Requests -->
        <div class="section">
            <h2>📝 Pending Seller Requests</h2>
            <?php if($pending_products->num_rows > 0): ?>
                <table>
                    <thead><tr><th>Seller</th><th>Item</th><th>Brand</th><th>Price</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php while($product = $pending_products->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Seller"><?php echo $product['name'] . ' ' . $product['surname']; ?></td>
                            <td data-label="Item"><?php echo $product['title']; ?></td>
                            <td data-label="Brand"><?php echo $product['brand']; ?></td>
                            <td data-label="Price">R <?php echo number_format($product['price'], 2); ?></td>
                            <td data-label="Action">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" name="approve_product" class="btn btn-approve">✓ Approve</button>
                                    <button type="submit" name="reject_product" class="btn btn-reject">✗ Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending seller requests.</p>
            <?php endif; ?>
        </div>
        
        <!-- Add User Form -->
        <div class="section">
            <h2>➕ Add New User</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group"><label>First Name</label><input type="text" name="name" required></div>
                    <div class="form-group"><label>Last Name</label><input type="text" name="surname" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                    <div class="form-group"><label><input type="checkbox" name="is_seller"> Is Seller</label></div>
                </div>
                <button type="submit" name="add_user" class="btn btn-add">Add User</button>
            </form>
        </div>
        
        <!-- Users Table with Edit/Delete -->
        <div class="section">
            <h2>👥 User Management</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Username</th><th>Seller</th><th>Verified</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?php echo $user['user_id']; ?></td>
                        <td data-label="Name"><?php echo $user['name'] . ' ' . $user['surname']; ?></td>
                        <td data-label="Email"><?php echo $user['email']; ?></td>
                        <td data-label="Username"><?php echo $user['username']; ?></td>
                        <td data-label="Seller"><?php echo $user['is_seller'] ? 'Yes' : 'No'; ?></td>
                        <td data-label="Verified"><?php echo $user['is_verified'] ? '✅' : '❌'; ?></td>
                        <td data-label="Actions">
                            <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="?delete_user=<?php echo $user['user_id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this user?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Add Product Form -->
        <div class="section">
            <h2>➕ Add New Product</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group"><label>Seller ID</label><input type="number" name="seller_id" required></div>
                    <div class="form-group"><label>Category</label>
                        <select name="category_id"><?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                        <?php endwhile; ?></select>
                    </div>
                    <div class="form-group"><label>Brand</label><input type="text" name="brand"></div>
                    <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
                    <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
                    <div class="form-group"><label>Condition</label>
                        <select name="condition_rating"><option>Like New</option><option>Excellent</option><option>Good</option><option>Fair</option></select>
                    </div>
                    <div class="form-group"><label>Price (R)</label><input type="number" step="0.01" name="price" required></div>
                    <div class="form-group"><label>Size</label><input type="text" name="size"></div>
                </div>
                <button type="submit" name="add_product" class="btn btn-add">Add Product</button>
            </form>
        </div>
        
        <!-- Products Table with Edit/Delete -->
        <div class="section">
            <h2>📦 Product Management</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Title</th><th>Brand</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($product = $products->fetch_assoc()): ?>
                    <tr>
                        <td data-label="ID"><?php echo $product['product_id']; ?></td>
                        <td data-label="Title"><?php echo $product['title']; ?></td>
                        <td data-label="Brand"><?php echo $product['brand']; ?></td>
                        <td data-label="Price">R <?php echo number_format($product['price'], 2); ?></td>
                        <td data-label="Status"><?php echo $product['status']; ?></td>
                        <td data-label="Actions">
                            <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-edit">Edit</a>
                            <a href="?delete_product=<?php echo $product['product_id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>