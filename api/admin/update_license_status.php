<?php
/**
 * API endpoint for admin to update company license status
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasUserType(USER_TYPE_ADMIN)) {
    $_SESSION['error'] = 'You must be logged in as an admin to update license status';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Get and validate input
$companyId = isset($_POST['company_id']) ? (int)$_POST['company_id'] : 0;
$licenseStatus = isset($_POST['license_status']) ? sanitizeInput($_POST['license_status']) : '';

// Validate company ID
if ($companyId <= 0) {
    $_SESSION['error'] = 'Invalid company ID';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Validate license status
$validStatuses = ['Pending', 'Approved', 'Not Approved'];
if (!in_array($licenseStatus, $validStatuses)) {
    $_SESSION['error'] = 'Invalid license status';
    redirectTo(SITE_URL . '/pages/admin/view_company.php?id=' . $companyId);
}

// Check if company exists
$company = fetchRow("SELECT * FROM companies WHERE company_id = ?", 'i', [$companyId]);
if (!$company) {
    $_SESSION['error'] = 'Company not found';
    redirectTo(SITE_URL . '/pages/admin/companies.php');
}

// Update license status
$sql = "UPDATE companies SET license_status = ? WHERE company_id = ?";
$result = executeQuery($sql, 'si', [$licenseStatus, $companyId]);

if ($result) {
    $_SESSION['success'] = 'License status updated successfully';
} else {
    $_SESSION['error'] = 'Failed to update license status';
}

// Redirect back to company view
redirectTo(SITE_URL . '/pages/admin/view_company.php?id=' . $companyId);
?> 