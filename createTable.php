<?php

//Creates and populates tblUser
include 'DBConn.php';

// Drop table if exists
$sql = "DROP TABLE IF EXISTS tblUser";
$conn->query($sql);

// Create tblUser table
$sql = "CREATE TABLE tblUser (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_verified TINYINT DEFAULT 0,
    is_admin TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table tblUser created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Load data from userData.txt
$sql = "LOAD DATA LOCAL INFILE 'userData.txt' 
        INTO TABLE tblUser 
        FIELDS TERMINATED BY ' ' 
        LINES TERMINATED BY '\n'
        (name, surname, email, username, password)";

//used method INSERT statements
$file = fopen("userData.txt", "r");
if ($file) {
    while (($line = fgets($file)) !== false) {
        $data = explode(' ', trim($line));
        if (count($data) >= 5) {
            $name = $conn->real_escape_string($data[0]);
            $surname = $conn->real_escape_string($data[1]);
            $email = $conn->real_escape_string($data[2]);
            $username = $conn->real_escape_string($data[3]);
            $password = $conn->real_escape_string($data[4]);
            
            $insert = "INSERT INTO tblUser (name, surname, email, username, password, is_verified) 
                       VALUES ('$name', '$surname', '$email', '$username', '$password', 1)";
            $conn->query($insert);
        }
    }
    fclose($file);
    echo "Data loaded from userData.txt successfully<br>";
}

$conn->close();
?>