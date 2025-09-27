<?php
require_once 'database.php';

// Check if website column exists
$check_column = "SHOW COLUMNS FROM users LIKE 'website'";
$result = mysqli_query($conn, $check_column);

if (mysqli_num_rows($result) == 0) {
    // Add website column if it doesn't exist
    $sql = "ALTER TABLE users ADD COLUMN website VARCHAR(255) AFTER email";
    
    if (mysqli_query($conn, $sql)) {
        echo "Website column added successfully to users table.";
    } else {
        echo "Error adding website column: " . mysqli_error($conn);
    }
} else {
    echo "Website column already exists in users table.";
}
?> 