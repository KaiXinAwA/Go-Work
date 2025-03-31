<?php
$pageTitle = 'Forgot Password';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectTo(SITE_URL);
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Reset Your Password</h3>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>
                    
                    <p class="mb-4">Enter your email address below and we'll send you instructions to reset your password.</p>
                    
                    <form action="<?php echo SITE_URL; ?>/api/auth/reset_password.php" method="POST" id="reset-password-form">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
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

<?php
require_once '../includes/footer.php';
?>
