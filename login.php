<?php
// File: login.php - UPDATED with cart redirect
include 'DBConn.php';

$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM tblUser WHERE username = ? AND email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 1 || $user['is_admin'] == 1) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // NEW: Redirect to cart or previous page after login
                if (isset($_SESSION['redirect_after'])) {
                    $redirect = $_SESSION['redirect_after'];
                    unset($_SESSION['redirect_after']);
                    header("Location: $redirect.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Your account is pending verification by an administrator.";
            }
        } else {
            $error = "Invalid password. Please try again.";
            // STICKY FORM - Keep entered values
            $sticky = true;
        }
    } else {
        $error = "User not found. Please register first.";
    }
}
?>
<!-- Rest of HTML same as before, but with sticky form values -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pastimes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c5f2d 0%, #1a3b1a 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .logo { text-align: center; font-size: 2rem; font-weight: bold; color: #2c5f2d; margin-bottom: 2rem; }
        .logo span { color: #ff6b35; }
        h2 { text-align: center; margin-bottom: 1.5rem; color: #333; }
        .error { background: #ffebee; color: #c62828; padding: 0.75rem; border-radius: 5px; margin-bottom: 1rem; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 500; }
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #2c5f2d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
        }
        .links { text-align: center; margin-top: 1rem; }
        .links a { color: #2c5f2d; text-decoration: none; }
        hr { margin: 1rem 0; }
        .admin-btn { margin-top: 1rem; background: #ff6b35; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">Pastimes<span>♻️</span></div>
        <h2>Welcome Back</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- STICKY FORM - values persist on error -->
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
        <div class="links">
            <a href="register.php">Don't have an account? Register here</a>
        </div>
        
        <hr>
        
        <form action="admin_login.php" method="GET">
            <button type="submit" class="admin-btn">Admin Login</button>
        </form>
    </div>
</body>
</html>