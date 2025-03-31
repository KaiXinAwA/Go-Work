<?php
/**
 * Configuration file
 * Contains database credentials and other global settings
 */

// Error reporting for development (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password (empty by default for XAMPP)
define('DB_NAME', 'gowork_db');      // Database name

// Site configuration
define('SITE_NAME', 'GoWork');
define('SITE_URL', 'http://localhost'); // Adjust if needed

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
// Comment out or replace with your actual API key if you plan to use email functionality
 define('RESEND_API_KEY', 're_2xBMehZD_6odVJUhbf6L2iPdcbVSd1nXQ');
define('EMAIL_FROM', 'noreply@gowork.com');
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
