<?php
$pageTitle = 'Account Settings';
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['error'] = 'You must be logged in to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    // Validate input
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Check if current password is correct
    if (!password_verify($currentPassword, $user['password'])) {
        $_SESSION['error'] = 'Current password is incorrect';
    } 
    // Check if new password matches confirmation
    elseif ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'New passwords do not match';
    }
    // Check password length
    elseif (strlen($newPassword) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long';
    }
    // Update password
    else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ? WHERE user_id = ?";
        $result = execute($query, 'si', [$hashedPassword, $user['user_id']]);
        
        if ($result) {
            $_SESSION['success'] = 'Password updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update password. Please try again.';
        }
    }
    
    // Refresh page to show messages
    redirectTo(SITE_URL . '/pages/account_settings.php');
}

// Include header
require_once '../includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h3 class="mb-0">Account Settings</h3>
                </div>
                <div class="card-body">
                    <!-- Status messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Account Information -->
                    <h5 class="card-title mb-4 text-dark">Account Information</h5>
                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Username:</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control-plaintext" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Email:</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control-plaintext" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-3 col-form-label">Account Type:</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control-plaintext" value="<?php echo getUserTypeName($user['user_type']); ?>" readonly>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <!-- Change Password Form -->
                    <h5 class="card-title mb-4 text-dark">Change Password</h5>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="d-grid gap-2 mb-2">
                            <button type="submit" name="change_password" class="btn btn-dark custom-btn">Change Password</button>
                        </div>
                        <div class="text-center">
                            <a href="<?php echo SITE_URL; ?>/pages/forgot_password.php?email=<?php echo urlencode($user['email']); ?>">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add CSS for button transitions and hover effects -->
<style>
.custom-btn {
    transition: all 0.3s ease;
}

.btn-dark.custom-btn:hover {
    background-color: #333;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-dark.custom-btn:hover {
    background-color: #333;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-outline-secondary.custom-btn:hover {
    background-color: #6c757d;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.form-control:focus {
    border-color: #212529;
    box-shadow: 0 0 0 0.25rem rgba(33, 37, 41, 0.25);
}
</style>

<?php require_once '../includes/footer.php'; ?> 