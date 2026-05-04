<?php
// create_admin.php – Run this once to add an admin user
include 'DBConn.php';

$email = 'admin@pastimes.com';
$username = 'admin';
$password = 'admin123';   // the password you will type on login
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->query("SELECT * FROM tblUser WHERE email = '$email' OR username = '$username'");

if ($check->num_rows > 0) {
    // Update existing admin with correct hash
    $conn->query("UPDATE tblUser SET password = '$hashed_password', is_admin = 1, is_verified = 1 WHERE email = '$email'");
    echo "✅ Admin user UPDATED. You can now log in with:<br>";
} else {
    // Insert new admin
    $sql = "INSERT INTO tblUser (name, surname, email, username, password, is_admin, is_verified) 
            VALUES ('Admin', 'User', '$email', '$username', '$hashed_password', 1, 1)";
    if ($conn->query($sql)) {
        echo "✅ Admin user CREATED successfully.<br>";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}

echo "<br>📧 Email: $email<br>";
echo "🔑 Password: $password<br>";
echo "<a href='admin_login.php'>Go to Admin Login →</a>";
?>