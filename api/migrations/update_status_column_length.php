<?php
/**
 * Migration: Update status column length in applications table
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to run migrations';
    redirectTo(SITE_URL . '/pages/login.php');
    exit;
}

// Get the current column type
$conn = getConnection();
$columnTypeQuery = "SHOW COLUMNS FROM applications LIKE 'status'";
$result = $conn->query($columnTypeQuery);

if ($result && $result->num_rows > 0) {
    $column = $result->fetch_assoc();
    $currentType = $column['Type'];
    
    echo "<p>Current status column type: " . $currentType . "</p>";
    
    // Check if we need to update the column type
    if (strpos(strtolower($currentType), 'varchar') === false || 
        (preg_match('/varchar\((\d+)\)/i', $currentType, $matches) && $matches[1] < 20)) {
        
        // We need to modify the column to be at least VARCHAR(20)
        $alterQuery = "ALTER TABLE applications MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Pending'";
        
        if ($conn->query($alterQuery)) {
            echo "<p>Successfully updated the status column to VARCHAR(20).</p>";
            echo "<p>Migration completed successfully.</p>";
        } else {
            echo "<p>Error updating the status column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p>The status column already has an appropriate type that can handle 'Withdrawn' status.</p>";
    }
} else {
    echo "<p>Error finding the status column in the applications table: " . $conn->error . "</p>";
}
?> 