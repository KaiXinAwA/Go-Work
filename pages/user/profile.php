<?php
$pageTitle = 'My Profile';
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';

// Check if user is logged in and is a job seeker
if (!isLoggedIn() || !hasUserType(USER_TYPE_JOBSEEKER)) {
    $_SESSION['error'] = 'You must be logged in as a job seeker to access this page';
    redirectTo(SITE_URL . '/pages/login.php');
}

// Get user data
$user = getCurrentUser();
$userProfile = getUserProfile($user['user_id']);

// Include header
require_once '../../includes/header.php';
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/user/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                </ol>
            </nav>
            <h2>My Profile</h2>
            <p class="text-muted">Manage your personal information and resume</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3 mb-4">
            <!-- Profile Photo -->
            <div class="card">
                <div class="card-body text-center">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo SITE_URL; ?>/uploads/profile_pictures/<?php echo $user['profile_picture']; ?>" alt="Profile" class="rounded-circle mb-3" width="150" height="150">
                    <?php else: ?>
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px">
                            <i class="fas fa-user fa-4x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h5><?php echo isset($userProfile['full_name']) && !empty($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : htmlspecialchars($user['username']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <form action="<?php echo SITE_URL; ?>/api/users/update_photo.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Change Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Upload Photo</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Profile Menu -->
            <div class="list-group mt-4">
                <a href="#personal-info" class="list-group-item list-group-item-action">Personal Information</a>
                <a href="#contact-info" class="list-group-item list-group-item-action">Contact Information</a>
                <a href="#education" class="list-group-item list-group-item-action">Education</a>
                <a href="#resume" class="list-group-item list-group-item-action">Resume</a>
                <a href="#password" class="list-group-item list-group-item-action">Change Password</a>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Personal Information -->
            <div class="card mb-4" id="personal-info">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/users/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="personal">
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($userProfile['full_name']) ? htmlspecialchars($userProfile['full_name']) : ''; ?>">
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
                    <form action="<?php echo SITE_URL; ?>/api/users/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="contact">
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($userProfile['phone']) ? htmlspecialchars($userProfile['phone']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo isset($userProfile['address']) ? htmlspecialchars($userProfile['address']) : ''; ?>">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($userProfile['city']) ? htmlspecialchars($userProfile['city']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?php echo isset($userProfile['state']) ? htmlspecialchars($userProfile['state']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country" value="<?php echo isset($userProfile['country']) ? htmlspecialchars($userProfile['country']) : ''; ?>">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Education -->
            <div class="card mb-4" id="education">
                <div class="card-header">
                    <h5 class="mb-0">Education</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/users/update_profile.php" method="POST">
                        <input type="hidden" name="section" value="education">
                        
                        <div class="mb-3">
                            <label for="education" class="form-label">Education Background</label>
                            <textarea class="form-control" id="education" name="education" rows="4"><?php echo isset($userProfile['education']) ? htmlspecialchars($userProfile['education']) : ''; ?></textarea>
                            <small class="text-muted">Include your educational history, degrees, certifications, etc.</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Resume -->
            <div class="card mb-4" id="resume">
                <div class="card-header">
                    <h5 class="mb-0">Resume</h5>
                </div>
                <div class="card-body">
                    <?php if ($userProfile && isset($userProfile['resume_path']) && $userProfile['resume_path']): ?>
                        <div class="alert alert-success mb-3">
                            <div class="d-flex align-items-center">
                                <div>
                                    <i class="fas fa-file-pdf fa-2x me-3"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Resume Uploaded</h6>
                                    <p class="mb-0 small"><?php echo basename($userProfile['resume_path']); ?></p>
                                </div>
                                <div class="ms-auto">
                                    <a href="<?php echo SITE_URL; ?>/uploads/resumes/<?php echo $userProfile['resume_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <p>Upload a new resume to replace the current one.</p>
                    <?php else: ?>
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            You haven't uploaded a resume yet. Upload your resume to apply for jobs.
                        </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo SITE_URL; ?>/api/users/update_resume.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="resume" class="form-label">Upload Resume (PDF)</label>
                            <input type="file" class="form-control" id="resume" name="resume" accept=".pdf">
                            <small class="text-muted">Maximum file size: 5MB</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Upload Resume</button>
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
                    <form action="<?php echo SITE_URL; ?>/api/users/change_password.php" method="POST">
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
