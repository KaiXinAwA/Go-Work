<?php
$pageTitle = 'Edit Job';
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
    $_SESSION['error'] = 'You need an approved license to edit jobs';
    redirectTo(SITE_URL . '/pages/company/profile.php#license');
}

// Get job types for dropdown
$jobTypes = getJobTypes();

// Check if a specific job ID is requested
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($job_id <= 0) {
    $_SESSION['error'] = 'Invalid job ID';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

// Get job data
$job = fetchRow(
    "SELECT j.* 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.company_id 
    WHERE j.job_id = ? AND c.company_id = ?", 
    'ii', 
    [$job_id, $company['company_id']]
);

if (!$job) {
    $_SESSION['error'] = 'Job not found or you do not have permission to edit it';
    redirectTo(SITE_URL . '/pages/company/jobs.php');
}

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
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/company/jobs.php">Manage Jobs</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Job</li>
                </ol>
            </nav>
            <h2>Edit Job</h2>
            <p class="text-muted">Update your job listing</p>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-body">
                    <form action="<?php echo SITE_URL; ?>/api/jobs/update.php" method="POST">
                        <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                        
                        <div class="mb-4">
                            <h5>Job Details</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="job_title" class="form-label">Job Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo htmlspecialchars($job['job_title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="job_type" class="form-label">Job Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="job_type" name="job_type" required>
                                    <option value="">Select Job Type</option>
                                    <?php foreach ($jobTypes as $type): ?>
                                        <option value="<?php echo $type; ?>" <?php echo ($job['job_type'] == $type) ? 'selected' : ''; ?>><?php echo $type; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required>
                                <small class="text-muted">State</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Categories</label>
                                <!-- Categories selection would go here, similar to post_job.php -->
                                <div class="form-control" style="height: auto;">
                                    <p class="mb-2">Current categories:</p>
                                    <?php 
                                    $currentCategories = explode(', ', $job['categories']);
                                    foreach($currentCategories as $category): 
                                        if (empty($category)) continue;
                                    ?>
                                        <span class="badge bg-primary me-2 mb-2"><?php echo htmlspecialchars($category); ?></span>
                                    <?php endforeach; ?>
                                    <p class="mt-2 text-muted">To change categories, please use the full job creation form</p>
                                </div>
                            </div>
                            
                            <!-- Hidden field to preserve category string -->
                            <input type="hidden" name="category_string" id="category-string" value="<?php echo htmlspecialchars($job['categories']); ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="salary_min" class="form-label">Minimum Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" class="form-control" id="salary_min" name="salary_min" min="0" step="100" value="<?php echo $job['salary_min']; ?>">
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="salary_max" class="form-label">Maximum Salary</label>
                                    <div class="input-group">
                                        <span class="input-group-text">RM</span>
                                        <input type="number" class="form-control" id="salary_max" name="salary_max" min="0" step="100" value="<?php echo $job['salary_max']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h5>Job Description</h5>
                            <hr>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="6" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                                <small class="text-muted">Provide a detailed description of the job, responsibilities, and company benefits.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requirements <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="6" required><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                                <small class="text-muted">List qualifications, skills, experience, education, and any other requirements.</small>
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?php echo $job['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                Job is active and visible to job seekers
                            </label>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo SITE_URL; ?>/pages/company/jobs.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
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