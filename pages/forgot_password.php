<?php
$pageTitle = 'Forgot Password';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Note: We're allowing logged-in users to access this page
// This is to support the "Forgot Password" links in account settings pages

// Get email from URL parameter if available
$prefillEmail = '';
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $prefillEmail = sanitizeInput($_GET['email']);
}

// Get redirect URL if provided
$redirectUrl = '';
if (isset($_GET['redirect_url']) && !empty($_GET['redirect_url'])) {
    $redirectUrl = sanitizeInput($_GET['redirect_url']);
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h3 class="mb-0">Reset Your Password</h3>
                </div>
                <div class="card-body">
                    <div id="alert-container">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <p class="mb-4">Enter your email address below and we'll send you instructions to reset your password.</p>
                    
                    <form action="<?php echo SITE_URL; ?>/api/auth/reset_password_update.php" method="POST" id="reset-password-form">
                        <?php if (!empty($redirectUrl)): ?>
                            <input type="hidden" name="redirect_url" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($prefillEmail); ?>" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark custom-btn">Reset Password</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        <a href="<?php echo SITE_URL; ?>/pages/login.php">Back to Login</a>
                    </div>
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

.form-control:focus {
    border-color: #212529;
    box-shadow: 0 0 0 0.25rem rgba(33, 37, 41, 0.25);
}
</style>

<?php
require_once '../includes/footer.php';
?>
