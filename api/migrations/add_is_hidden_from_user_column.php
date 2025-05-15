<?php
/**
 * Migration: Update applications table to support withdrawn status
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

// Update existing status values in the database
$conn = getConnection();

// Check if there are any applications with 'Withdrawn' status already
$checkWithdrawnStatusQuery = "SELECT COUNT(*) as count FROM applications WHERE status = 'Withdrawn'";
$result = $conn->query($checkWithdrawnStatusQuery);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // No withdrawn applications, it's safe to run the migration
    echo "<p>Updating the applications table schema to support the 'Withdrawn' status...</p>";
    
    // Update any applications that might be hidden from user to have 'Withdrawn' status
    $updateQuery = "UPDATE applications SET status = 'Withdrawn' WHERE is_hidden_from_user = 1";
    if ($conn->query($updateQuery)) {
        echo "<p>Successfully updated " . $conn->affected_rows . " application(s) to 'Withdrawn' status.</p>";
    } else {
        echo "<p>Error updating applications: " . $conn->error . "</p>";
    }
    
    echo "<p>Migration completed successfully.</p>";
} else {
    echo "<p>Migration has already been applied. There are already " . $row['count'] . " applications with 'Withdrawn' status.</p>";
}
?> 