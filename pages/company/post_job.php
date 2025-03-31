<?php
$pageTitle = 'Post a Job';
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

// Check if license is approved
if (!$company || $company['license_status'] !== 'Approved') {
    $_SESSION['error'] = 'You need an approved license to post jobs';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get job types for dropdown
$jobTypes = getJobTypes();

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
                    <li class="breadcrumb-item active" aria-current="page">Post a Job</li>
                </ol>
            </nav>
            <h2>Post a New Job</h2>
            <p class="text-muted">Create a new job listing to find the perfect candidate</p>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/jobs/create.php" method="POST">
                        <div class="mb-4">
                            <h5>Job Details</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="job_title" name="job_title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="job_type" name="job_type" required>
                                    <option value="">Select Job Type</option>
                                    <?php foreach ($jobTypes as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required>
                                <small class="text-muted">City, State, or Remote</small>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="salary_min" name="salary_min" min="0" step="1000">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="salary_max" name="salary_max" min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Job Description</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="6" required></textarea>
                                <small class="text-muted">Provide a detailed description of the job, responsibilities, and company benefits.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="6" required></textarea>
                                <small class="text-muted">List qualifications, skills, experience, education, and any other requirements.</small>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">
                                Publish this job immediately
                            </label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">Post Job</button>
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
