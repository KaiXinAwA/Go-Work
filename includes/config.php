<?php
/**
 * Configuration file
 * Contains database credentials and other global settings
 */

// Error reporting for development (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'gowork.mysql.database.azure.com');      // Database host
define('DB_USER', 'jiaying');           // Database username
define('DB_PASS', getenv('DB_PASS') ?: 'Mickey1928@');               // Database password (empty by default for XAMPP)
define('DB_NAME', 'gowork_db');      // Database name

// SSL for database connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3306);
$mysqli->ssl_set(NULL, NULL, '/Users/jiayingsong/Documents/GitHub/GoWork/includes/DigiCertGlobalRootCA.crt.pem', NULL, NULL);
$mysqli->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, 3306, NULL, MYSQLI_CLIENT_SSL);
if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Site configuration
define('SITE_NAME', 'GoWork');
define('SITE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']);

// File upload paths
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('RESUME_DIR', UPLOAD_DIR . 'resumes/');
define('LICENSE_DIR', UPLOAD_DIR . 'licenses/');
define('PROFILE_PIC_DIR', UPLOAD_DIR . 'profile_pictures/');

// File upload size limits (in bytes)
define('MAX_RESUME_SIZE', 5 * 1024 * 1024);    // 5MB
define('MAX_LICENSE_SIZE', 5 * 1024 * 1024);   // 5MB
define('MAX_PROFILE_PIC_SIZE', 2 * 1024 * 1024); // 2MB

// Email configuration for Resend
// Update with your actual API key from https://resend.com/api-keys
define('RESEND_API_KEY', 're_669yudYo_7WWDo9JFNMG3nPbwa5ykwBY6'); // Replace with your actual API key
define('EMAIL_FROM', 'onboarding@resend.dev');  // Use Resend's verified domain or your own verified domain
define('EMAIL_FROM_NAME', 'GoWork');

// User types
define('USER_TYPE_JOBSEEKER', 1);
define('USER_TYPE_COMPANY', 2);
define('USER_TYPE_GOWORK', 3);
define('USER_TYPE_ADMIN', 4);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
