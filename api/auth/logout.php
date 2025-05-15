<?php
/**
 * Logout API Endpoint
 */
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    // Remove token from database (if you implemented the remember me feature)
    // You would need to create and use a removeRememberToken function
    // removeRememberToken($_COOKIE['remember_token']);
    
    // Delete the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home page
redirectTo(SITE_URL);
