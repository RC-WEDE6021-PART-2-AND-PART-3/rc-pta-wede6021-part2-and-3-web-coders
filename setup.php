<?php
include 'DBConn.php';

// Read the SQL file
$sql = file_get_contents('myClothingStore.sql');

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

// Execute each statement
foreach ($statements as $statement) {
    if (!empty($statement)) {
        if ($conn->query($statement) === TRUE) {
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        } else {
            echo "Error: " . $conn->error . "<br>";
        }
    }
}

echo "Database setup complete.";
?>