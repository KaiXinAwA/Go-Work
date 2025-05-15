<?php
/**
 * Run this script to update the jobs table by adding the categories field
 */
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    die('Access denied. You must be logged in as an admin to run this script.');
}

// Connect to the database
$conn = getConnection();

// SQL to add the categories column
$sql = "ALTER TABLE jobs ADD COLUMN categories TEXT AFTER job_type";

try {
    // Check if the column already exists
    $checkColumnSql = "SHOW COLUMNS FROM jobs LIKE 'categories'";
    $result = mysqli_query($conn, $checkColumnSql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "The categories column already exists.<br>";
    } else {
        // Execute the SQL
        if (mysqli_query($conn, $sql)) {
            echo "The categories column has been added successfully.<br>";
            
            // Update existing jobs to have an empty categories field
            $updateSql = "UPDATE jobs SET categories = '' WHERE categories IS NULL";
            if (mysqli_query($conn, $updateSql)) {
                echo "Existing jobs have been updated with empty categories.<br>";
            } else {
                echo "Error updating existing jobs: " . mysqli_error($conn) . "<br>";
            }
        } else {
            echo "Error adding categories column: " . mysqli_error($conn) . "<br>";
        }
    }
    
    echo "Database update complete.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
mysqli_close($conn); 