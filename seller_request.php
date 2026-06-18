<?php
// File: seller_request.php - NEW for Part 3
session_start();
include 'DBConn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please login to sell items");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $brand = $_POST['brand'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $condition_rating = $_POST['condition_rating'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $category_id = $_POST['category_id'];
    
    $image_name = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $image_name = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $filename);
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            move_uploaded_file($_FILES['product_image']['tmp_name'], 'uploads/' . $image_name);
        } else {
            $error = "Invalid file type";
        }
    }
    
    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO tblProducts (seller_id, category_id, brand, title, description, condition_rating, price, size, main_image, verified_by_admin, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'pending')");
        $stmt->bind_param("iissssdss", $user_id, $category_id, $brand, $title, $description, $condition_rating, $price, $size, $image_name);
        if ($stmt->execute()) {
            $success = "Request submitted! Admin will review your item.";
        } else {
            $error = "Submission failed";
        }
    }
}

$categories = $conn->query("SELECT * FROM tblCategories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
        .navbar { background: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #2c5f2d; text-decoration: none; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 2rem; }
        .card { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 0.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px; }
        .btn { width: 100%; padding: 0.75rem; background: #2c5f2d; color: white; border: none; border-radius: 8px; cursor: pointer; }
        .success { background: #e8f5e9; color: #2c5f2d; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav class="navbar"><a href="index.php" class="logo">Pastimes♻️</a><div><a href="shop.php">Shop</a> <a href="cart.php">Cart</a> <a href="logout.php">Logout</a></div></nav>
    <div class="container"><div class="card"><h1>Sell Your Item</h1><p>Submit for admin approval</p>
    <?php if($success): ?><div class="success">✅ <?php echo $success; ?><br><a href="shop.php">Continue Shopping</a></div>
    <?php else: ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group"><label>Brand</label><input type="text" name="brand" required></div>
            <div class="form-group"><label>Title</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" required></textarea></div>
            <div class="form-group"><label>Category</label><select name="category_id"><?php while($cat=$categories->fetch_assoc()): ?><option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option><?php endwhile; ?></select></div>
            <div class="form-group"><label>Condition</label><select name="condition_rating"><option>Like New</option><option>Excellent</option><option>Good</option><option>Fair</option></select></div>
            <div class="form-group"><label>Price (R)</label><input type="number" step="0.01" name="price" required></div>
            <div class="form-group"><label>Size</label><input type="text" name="size"></div>
            <div class="form-group"><label>Image</label><input type="file" name="product_image" accept="image/*"></div>
            <button type="submit" class="btn">Submit Request</button>
        </form>
    <?php endif; ?></div></div>
</body>
</html>