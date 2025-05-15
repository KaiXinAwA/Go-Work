<?php
$pageTitle = 'Register';
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
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Create an Account</h3>
                </div>
                <div class="card-body">
                    <div id="alert-container"></div>
                    
                    <ul class="nav nav-tabs mb-4" id="registerTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="job-seeker-tab" data-bs-toggle="tab" data-bs-target="#job-seeker" type="button" role="tab" aria-controls="job-seeker" aria-selected="true">Job Seeker</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab" aria-controls="company" aria-selected="false">Company</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="registerTabContent">
                        <!-- Job Seeker Registration Form -->
                        <div class="tab-pane fade show active" id="job-seeker" role="tabpanel" aria-labelledby="job-seeker-tab">
                            <form action="<?php echo SITE_URL; ?>/api/auth/register.php" method="POST" id="registration-form-jobseeker">
                                <input type="hidden" name="user_type" value="<?php echo USER_TYPE_JOBSEEKER; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Register as Job Seeker</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Company Registration Form -->
                        <div class="tab-pane fade" id="company" role="tabpanel" aria-labelledby="company-tab">
                            <form action="<?php echo SITE_URL; ?>/api/auth/register.php" method="POST" id="registration-form-company">
                                <input type="hidden" name="user_type" value="<?php echo USER_TYPE_COMPANY; ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="company_username" name="username" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="company_email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="company_email" name="email" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Company Name</label>
                                    <input type="text" class="form-control" id="company_name" name="company_name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="company_description" class="form-label">Brief Company Description</label>
                                    <textarea class="form-control" id="company_description" name="company_description" rows="3" required></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company_password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="company_password" name="password" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="company_confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="company_confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Register as Company</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="text-center">
                        Already have an account? <a href="<?php echo SITE_URL; ?>/pages/login.php">Login here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>
