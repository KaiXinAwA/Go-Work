<?php
$pageTitle = 'Company Profile';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a company
if (!isLoggedIn() || !hasUserType(USER_TYPE_COMPANY)) {
    $_SESSION['error'] = 'You must be logged in as a company to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();
$company = getCompanyProfile($user['user_id']);

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Company Profile</li>
                </ol>
            </nav>
            <h2>Company Profile</h2>
            <p class="text-muted">Manage your company information and license</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Company Logo -->
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Company Logo" class="rounded-circle mb-3" width="150" height="150">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px">
                            <i class="fas fa-building fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h5><?php echo $company && isset($company['company_name']) ? htmlspecialchars($company['company_name']) : htmlspecialchars($user['username']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <form action="<?php echo SITE_URL; ?>/api/companies/update_logo.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Change Company Logo</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Upload Logo</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Profile Menu -->
            <div class="list-group mt-4">
                <a href="#company-info" class="list-group-item list-group-item-action">Company Information</a>
                <a href="#contact-info" class="list-group-item list-group-item-action">Contact Information</a>
                <a href="#license" class="list-group-item list-group-item-action">License</a>
                <a href="#password" class="list-group-item list-group-item-action">Change Password</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Company Information -->
            <div class="card mb-4" id="company-info">
                <div class="card-header">
                    <h5 class="mb-0">Company Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/companies/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="company">
                        
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo $company && isset($company['company_name']) ? htmlspecialchars($company['company_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Company Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo $company && isset($company['description']) ? htmlspecialchars($company['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                <small class="text-muted">Username cannot be changed</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="card mb-4" id="contact-info">
                <div class="card-header">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/companies/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="contact">
                        
                        <div class="mb-3">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" value="<?php echo $company && isset($company['contact_number']) ? htmlspecialchars($company['contact_number']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo $company && isset($company['address']) ? htmlspecialchars($company['address']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo $company && isset($company['city']) ? htmlspecialchars($company['city']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo $company && isset($company['state']) ? htmlspecialchars($company['state']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?php echo $company && isset($company['country']) ? htmlspecialchars($company['country']) : ''; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- License -->
            <div class="card mb-4" id="license">
                <div class="card-header">
                    <h5 class="mb-0">Company License</h5>
                </div>
                <div class="card-body">
                    <?php if ($company && isset($company['license_path']) && $company['license_path']): ?>
                        <div class="alert alert-info mb-3">
                            <div class="d-flex align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf fa-2x me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">License Uploaded</h6>
                                    <p class="mb-0 small"><?php echo basename($company['license_path']); ?></p>
                                </div>
                                <div class="ms-auto">
                                    <a href="<?php echo SITE_URL; ?>/uploads/licenses/<?php echo $company['license_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert <?php echo $company['license_status'] === 'Approved' ? 'alert-success' : ($company['license_status'] === 'Not Approved' ? 'alert-danger' : 'alert-warning'); ?> mb-3">
                            <div class="d-flex align-items-center">
                                <div>
                                    <?php if ($company['license_status'] === 'Approved'): ?>
                                        <i class="fas fa-check-circle fa-2x me-3"></i>
                                    <?php elseif ($company['license_status'] === 'Not Approved'): ?>
                                        <i class="fas fa-times-circle fa-2x me-3"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock fa-2x me-3"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h6 class="mb-0">License Status: <?php echo $company['license_status']; ?></h6>
                                    <?php if ($company['license_status'] === 'Pending'): ?>
                                        <p class="mb-0 small">Your license is being reviewed. This process typically takes 1-2 business days.</p>
                                    <?php elseif ($company['license_status'] === 'Not Approved'): ?>
                                        <p class="mb-0 small">Your license was not approved. Please submit a valid license.</p>
                                    <?php else: ?>
                                        <p class="mb-0 small">Your license has been approved. You can now post jobs.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($company['license_status'] === 'Not Approved'): ?>
                            <p>Please upload a new license:</p>
                        <?php else: ?>
                            <p>Upload a new license to replace the current one:</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You haven't uploaded a license yet. A valid license is required to post jobs.
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo SITE_URL; ?>/api/companies/update_license.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="license" class="form-label">Upload License (PDF)</label>
                            <input type="file" class="form-control" id="license" name="license" accept=".pdf" required>
                            <small class="text-muted">Maximum file size: 5MB</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Upload License</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card mb-4" id="password">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/companies/change_password.php" method="POST">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Password must be at least 8 characters long</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
