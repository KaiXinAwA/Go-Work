<?php
/**
 * API endpoint for approving or rejecting company license
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin or GoWork worker
if (!isLoggedIn() || (!hasUserType(USER_TYPE_ADMIN) && !hasUserType(USER_TYPE_GOWORK))) {
    $_SESSION['error'] = 'You must be logged in as an admin or GoWork worker to approve licenses';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/review_license.php');
}

// Get and sanitize input
$companyId = isset($_POST['company_id']) ? (int)$_POST['company_id'] : 0;
$status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';

// Validate input
if ($companyId === 0) {
    $_SESSION['error'] = 'Invalid company ID';
    redirectTo(SITE_URL . '/pages/admin/review_license.php');
}

if ($status !== 'Approved' && $status !== 'Not Approved') {
    $_SESSION['error'] = 'Invalid status. Must be "Approved" or "Not Approved"';
    redirectTo(SITE_URL . '/pages/admin/review_license.php');
}

// Update license status
$result = updateLicenseStatus($companyId, $status);

if (!$result) {
    $_SESSION['error'] = 'Failed to update license status';
    redirectTo(SITE_URL . '/pages/admin/review_license.php');
}

// Get company details for notification
$company = fetchRow(
    "SELECT c.*, u.email FROM companies c JOIN users u ON c.user_id = u.user_id WHERE c.company_id = ?", 
    'i', 
    [$companyId]
);

if ($company) {
    // Send notification email about license status
    // Placeholder for email notification - would use Resend API
    $subject = "Your license has been " . strtolower($status);
    $message = "Dear " . $company['company_name'] . ",\n\n";
    $message .= "Your business license has been reviewed and " . strtolower($status) . ".\n\n";
    
    if ($status === 'Approved') {
        $message .= "You can now post jobs on our platform. Welcome to GoWork!\n\n";
    } else {
        $message .= "Unfortunately, we couldn't verify your business license. Please submit a valid license to post jobs.\n\n";
    }
    
    $message .= "If you have any questions, please contact our support team.\n\n";
    $message .= "Best regards,\n";
    $message .= SITE_NAME . " Team";
    
    // Log email for debugging since we're not sending real emails
    error_log("License notification email would be sent to: " . $company['email']);
    error_log("Subject: " . $subject);
    error_log("Message: " . $message);
}

// Set success message
$_SESSION['success'] = 'Company license has been ' . strtolower($status);

// Redirect back
redirectTo(SITE_URL . '/pages/admin/review_license.php');
