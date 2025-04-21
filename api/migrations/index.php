<?php
/**
 * Database Migrations Index
 */
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
$isAdmin = isLoggedIn() && hasUserType(USER_TYPE_ADMIN);

// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migrations</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Database Migrations</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!$isAdmin): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You must be logged in as an administrator to run database migrations.
                            </div>
                            <p>Please log in with an administrative account to access this page.</p>
                            <a href="<?php echo SITE_URL; ?>/pages/login.php" class="btn btn-primary">Login</a>
                        <?php else: ?>
                            <p>Welcome, Administrator. You can run the following migrations:</p>
                            
                            <div class="list-group mb-4">
                                <a href="update_status_column_length.php" class="list-group-item list-group-item-action">
                                    <strong>1. Update Status Column Length</strong>
                                    <p class="mb-0 small text-muted">Updates the applications table's status column to support longer status values like "Withdrawn"</p>
                                </a>
                                <a href="add_is_hidden_from_user_column.php" class="list-group-item list-group-item-action">
                                    <strong>2. Support Withdrawn Status</strong>
                                    <p class="mb-0 small text-muted">Updates applications to support the "Withdrawn" status</p>
                                </a>
                            </div>
                            
                            <div class="alert alert-info">
                                <h5>Alternative: Direct SQL Execution</h5>
                                <p>You can also download and run the SQL script directly:</p>
                                <a href="update_applications_table.sql" class="btn btn-sm btn-outline-primary" download>Download SQL Script</a>
                            </div>
                            
                            <p><strong>Important:</strong> Make sure to back up your database before running any migrations.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer text-muted">
                        <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 