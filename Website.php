<?php
session_start();

// Connecting a database
$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Code for creating a database if it doesnt exist 
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS test");

// selecting a Database to use
mysqli_select_db($conn, "clothingstore");

// Creating a table
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tblusers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'user',
    is_approved INT NOT NULL DEFAULT 0
)");

// Creating a admin
$admin_email = "adminAproval@gmail.com";
$admin_password = password_hash("PassCode33", PASSWORD_DEFAULT);

$check_admin = mysqli_query($conn, "SELECT * FROM tblusers WHERE email='$admin_email'");

if (mysqli_num_rows($check_admin) == 0) {
    mysqli_query($conn, "INSERT INTO tblusers(fullname, email, password, role, is_approved)
    VALUES('Admin', '$admin_email', '$admin_password', 'admin', 1)");
}

$message = "";

// Code to register a new user
if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_user = mysqli_query($conn, "SELECT * FROM tblusers WHERE email='$email'");

    if (mysqli_num_rows($check_user) > 0) {
        $message = "Email already exists.";
    } else {
        $insert = mysqli_query($conn, "INSERT INTO tblusers(fullname, email, password, role, is_approved)
        VALUES('$fullname', '$email', '$password', 'user', 0)");

        if ($insert) {
            $message = "Registration successful. Wait for admin approval.";
        } else {
            $message = "Registration failed.";
        }
    }
}

// Loging in users
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM tblusers WHERE email='$email'");

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            if ($user['role'] == 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                $message = "Admin login successful.";
            } else {
                if ($user['is_approved'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['role'] = $user['role'];
                    $message = "User login successful.";
                } else {
                    $message = "Your account is not approved yet.";
                }
            }

        } else {
            $message = "Wrong password.";
        }
    } else {
        $message = "User not found.";
    }
}

// Aprroving a new user
if (isset($_GET['approve']) && isset($_SESSION['role']) && $_SESSION['role'] == 'admin')
{
    $id = $_GET['approve'];
    mysqli_query($conn, "UPDATE tblusers SET is_approved=1 WHERE id='$id'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// The code for logging out of admin
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clothing Store</title>
    <style>
        :root {
            --bg: #f1f1f0;
            --panel: #e4e2dd;
            --text: #2f3832;
            --accent: #b29700;
            --accent-strong: #8a6b00;
            --button: #4c7a4b;
            --button-hover: #3f663f;
            --border: #c3b88b;
            --link: #476f46;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: radial-gradient(circle at top, #ffffff, var(--bg));
            color: var(--text);
        }

        header {
            background: linear-gradient(135deg, #4a4a47, #31322f);
            color: #f7e6a0;
            padding: 24px 20px;
            text-align: center;
            border-bottom: 4px solid var(--accent);
        }

        header h6 {
            margin: 0;
            font-size: 1.1rem;
            letter-spacing: 0.06em;
        }

        h1, h2 {
            margin: 1.25rem 0 0.75rem;
            text-align: center;
            color: #2f3832;
            text-shadow: 0 1px 2px rgba(0,0,0,0.08);
        }

        h1 {
            font-size: 2.7rem;
            color: var(--accent-strong);
        }

        h2 {
            font-size: 1.6rem;
            color: #4c5a46;
        }

        .page-wrapper {
            max-width: 860px;
            margin: 0 auto;
            padding: 24px 18px 44px;
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15, 19, 14, 0.08);
            padding: 28px;
            margin: 0 auto 24px;
        }

        p, label {
            line-height: 1.6;
        }

        .message {
            display: inline-block;
            background: #fff7d6;
            color: #5d4a13;
            border: 1px solid #e1d29a;
            padding: 12px 16px;
            border-radius: 12px;
            margin: 0 auto 18px;
            max-width: 780px;
        }

        form {
            background: rgba(255,255,255,0.86);
            border: 1px solid rgba(165,142,44,0.15);
            border-radius: 16px;
            padding: 22px;
            margin-bottom: 26px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 16px;
            border: 1px solid #c7c1aa;
            border-radius: 12px;
            background: #faf8f2;
            color: var(--text);
        }

        button {
            appearance: none;
            border: none;
            background: var(--button);
            color: #fff;
            padding: 12px 20px;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s ease;
        }

        button:hover {
            background: var(--button-hover);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.94);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #d8d3be;
            text-align: left;
        }

        th {
            background: #e1d59c;
            color: #2f3832;
            font-weight: 700;
        }

        tr:last-child td {
            border-bottom: none;
        }

        a {
            color: var(--link);
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        .admin-link {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 16px;
            background: #d4e5d2;
            border-radius: 10px;
            color: var(--text);
        }

        @media (max-width: 600px) {
            body {
                padding: 0 12px;
            }

            .page-wrapper {
                padding: 18px 0 32px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">

  



        <header>
            <h6>Welcome to the Store of <span class="w3-tag">PastTimes</span></h6>
        </header>


    <h1>PastTimes</h1>

    <?php if ($message != "") { ?>
        <p class="message"><strong><?php echo $message; ?></strong></p>
    <?php } ?>

    <?php if (!isset($_SESSION['user_id'])) { ?>

        <h2>Register</h2>
        <form method="post">
            <input type="text" name="fullname" placeholder="Full Name" required><br><br>
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit" name="register">Register</button>
        </form>

        <h2>Login</h2>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <button type="submit" name="login">Login</button>
        </form>

        <p><strong>Admin login:</strong> adminAproval@gmail.com / PassCode33</p>

    <?php } else { ?>

        <p>Welcome, <strong><?php echo $_SESSION['fullname']; ?></strong></p>
        <p>Role: <strong><?php echo $_SESSION['role']; ?></strong></p>
        <p><a href="?logout=1">Logout</a></p>

        <?php if ($_SESSION['role'] == 'admin') { ?>
            <h2>Pending Users</h2>
            <table  cellpadding="10">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>

                <?php
                $pending = mysqli_query($conn, "SELECT * FROM tblusers WHERE role='user' AND is_approved=0");
                while ($row = mysqli_fetch_assoc($pending)) {
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['fullname']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><a href="?approve=<?php echo $row['id']; ?>">Approve</a></td>
                </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <h2>User Page</h2>
            <p>You can log in because the admin approved your account.</p>
        <?php } ?>

    <?php } ?>

    </div>
</body>
</html>