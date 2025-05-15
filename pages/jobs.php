<?php
$pageTitle = 'Browse Jobs';
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Add custom CSS for job filtering
$customCSS = '<style>
    /* Container layout */
    .jobs-container {
        position: relative;
        z-index: 50;
        min-height: 500px; /* Reduced from 800px */
    }
    
    section.container {
        padding-top: 10px;
    }
    
    /* Filter dropdown styles */
    .filter-dropdown .dropdown-menu {
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        margin-top: 0.5rem;
    }
    
    .filter-dropdown .form-check {
        margin-bottom: 0.3rem;
    }
    
    .filter-dropdown .form-check-label {
        font-size: 0.9rem;
    }
    
    /* Ensure space for dropdown */
    .col-md-8 {
        min-height: 200px; 
    }
    
    /* Force spacer to be visible */
    .dropdown-spacer {
        display: block !important;
        min-height: 150px !important;
        margin-top: 50px;
        width: 100%;
        clear: both;
    }
    
    /* Job Filter Styles */
    .filter-dropdown .dropdown-menu {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        min-width: 100%;
    }
    
    .filter-dropdown .dropdown-toggle {
        text-align: left;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .filter-dropdown .form-check {
        margin-bottom: 8px;
    }
    
    .filter-dropdown h6 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        padding-bottom: 4px;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .filter-dropdown .form-check-label {
        font-size: 14px;
    }
    
    /* Job card styles */
    .job-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .job-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    }
    
    /* Active filters styling */
    .active-search-filters {
        background-color:rgb(220, 237, 255);
        border-radius: 8px;
        border-left: 4px solid #0d6efd;
    }
    
    .active-search-filters .badge {
        font-size: 0.85rem;
        padding: 0.5em 0.8em;
    }
    
    /* Clear filters button styling */
    .clear-filters-btn {
        font-size: 0.85rem;
        padding: 0.4em 0.8em;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .clear-filters-btn:hover {
        background-color: #dc3545;
        color: #fff;
        border-color: #dc3545;
    }
</style>';

// Get search parameters
$keywords = isset($_GET['keywords']) ? sanitizeInput($_GET['keywords']) : '';
$jobTypes = isset($_GET['job_type']) ? $_GET['job_type'] : [];
$categories = isset($_GET['categories']) ? $_GET['categories'] : [];
$minSalary = isset($_GET['min_salary']) ? (int)$_GET['min_salary'] : 0;
$maxSalary = isset($_GET['max_salary']) ? (int)$_GET['max_salary'] : 0;
$datePosted = isset($_GET['date_posted']) ? sanitizeInput($_GET['date_posted']) : 'any';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';

// Set up error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if a specific job ID is requested
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job data
if ($job_id > 0) {
    // Get single job details
    $job = fetchRow(
        "SELECT j.*, c.company_name, c.description as company_description 
        FROM jobs j 
        JOIN companies c ON j.company_id = c.company_id 
        WHERE j.job_id = ? AND j.is_active = 1", 
        'i', 
        [$job_id]
    );
} else {
    // Search for jobs with filters
    $jobs = searchJobs($keywords, $location, $jobTypes, $categories, $minSalary, $maxSalary, $datePosted, $sort);
    
    // Store all filter parameters in session for "Back" button functionality
    $_SESSION['last_job_filters'] = [];
    // Always store search bar parameters, even if empty
    $_SESSION['last_job_filters']['keywords'] = $keywords;
    $_SESSION['last_job_filters']['location'] = $location;
    // Store the search form submission method to help with debugging
    $_SESSION['last_search_method'] = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'unknown';
    $_SESSION['last_search_time'] = time();
    // Only store non-empty advanced filter parameters
    if (!empty($jobTypes)) $_SESSION['last_job_filters']['job_type'] = $jobTypes;
    if (!empty($categories)) $_SESSION['last_job_filters']['categories'] = $categories;
    if (!empty($minSalary)) $_SESSION['last_job_filters']['min_salary'] = $minSalary;
    if (!empty($maxSalary)) $_SESSION['last_job_filters']['max_salary'] = $maxSalary;
    if (!empty($datePosted) && $datePosted != 'any') $_SESSION['last_job_filters']['date_posted'] = $datePosted;
    if (!empty($sort) && $sort != 'newest') $_SESSION['last_job_filters']['sort'] = $sort;
}

// Include header
require_once '../includes/header.php';
?>

<?php echo $customCSS; ?>

<?php if ($job_id > 0 && $job): ?>
    <!-- Single Job View -->
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/pages/jobs.php">Jobs</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($job['job_title']); ?></li>
                    </ol>
                </nav>
                <?php
                // Get the referer URL or build a URL with existing filter parameters
                $backUrl = SITE_URL . '/pages/jobs.php';
                
                // Check HTTP_REFERER first
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                
                // If referer contains jobs.php and is not the current job detail page, use it
                if (strpos($referer, '/pages/jobs.php') !== false && strpos($referer, 'id=' . $job_id) === false) {
                    $backUrl = $referer;
                } else {
                    // Otherwise, rebuild the URL from stored session parameters
                    $filterParams = [];
                    
                    // Check if we have stored filter parameters in the session
                    if (isset($_SESSION['last_job_filters']) && is_array($_SESSION['last_job_filters'])) {
                        foreach ($_SESSION['last_job_filters'] as $key => $value) {
                            // Skip empty values except for keywords and location which should be preserved
                            if (empty($value) && $key !== 'keywords' && $key !== 'location') {
                                continue;
                            }
                            $filterParams[$key] = $value;
                        }
                    }
                    
                    // If we have filter parameters, add them to the URL
                    if (!empty($filterParams)) {
                        $backUrl .= '?' . http_build_query($filterParams);
                    }
                }
                ?>
                <a href="<?php echo $backUrl; ?>" class="btn btn-dark mb-3">
                    <i class="fas fa-arrow-left"></i> Back to Browse Jobs
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h2>
                        <h4 class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></h4>
                        
                        <div class="job-details my-3">
                            <div class="job-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo htmlspecialchars($job['location']); ?></span>
                            </div>
                            <div class="job-detail">
                                <i class="fas fa-briefcase"></i>
                                <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                            </div>
                            <div class="job-detail">
                                <i class="fas fa-money-bill-wave"></i>
                                <span><?php echo formatSalaryRange($job['salary_min'], $job['salary_max']); ?></span>
                            </div>
                            <div class="job-detail">
                                <i class="fas fa-clock"></i>
                                <span><?php echo timeElapsed($job['posted_date']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($job['categories'])): ?>
                        <div class="my-3">
                            <div class="mb-2"><i class="fas fa-tags"></i> <strong>Categories:</strong></div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach(explode(', ', html_entity_decode($job['categories'])) as $category): ?>
                                    <span class="badge bg-light text-dark p-2"><?php echo $category; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h5>Job Description</h5>
                        <div class="mb-4">
                            <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                        </div>
                        
                        <h5>Requirements</h5>
                        <div class="mb-4">
                            <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                        </div>
                        
                        <?php if (isLoggedIn() && hasUserType(USER_TYPE_JOBSEEKER)): ?>
                            <div class="mt-4" id="apply">
                                <h5>Apply for this Position</h5>
                                <form action="<?php echo SITE_URL; ?>/api/jobs/apply.php" method="POST" class="apply-form" data-job-id="<?php echo $job['job_id']; ?>" data-job-title="<?php echo htmlspecialchars($job['job_title']); ?>">
                                    <input type="hidden" name="job_id" value="<?php echo $job['job_id']; ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Resume</label>
                                        <?php
                                        $userProfile = getUserProfile($_SESSION['user_id']);
                                        if ($userProfile && $userProfile['resume_path']): 
                                        ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-file-pdf"></i> We'll use your uploaded resume for this application. 
                                                <a href="<?php echo SITE_URL; ?>/pages/user/profile.php" class="alert-link">Update resume</a>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i> You haven't uploaded a resume yet. 
                                                <a href="<?php echo SITE_URL; ?>/pages/user/profile.php" class="alert-link">Upload resume</a> before applying.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php
                                    // Check if user has already applied for this job
                                    $hasApplied = false;
                                    $hasWithdrawn = false;
                                    $applicationStatus = null;
                                    
                                    if (isLoggedIn() && hasUserType(USER_TYPE_JOBSEEKER)) {
                                        $existingApplication = fetchRow(
                                            "SELECT * FROM applications WHERE job_id = ? AND user_id = ?", 
                                            'ii', 
                                            [$job['job_id'], $_SESSION['user_id']]
                                        );
                                        
                                        if ($existingApplication) {
                                            $hasApplied = true;
                                            $applicationStatus = $existingApplication['status'];
                                            $hasWithdrawn = ($applicationStatus === 'Withdrawn');
                                        }
                                    }
                                    
                                    // Check for reapply request
                                    $allowReapply = (isset($_GET['reapply']) && $_GET['reapply'] == 1 && $hasWithdrawn);
                                    ?>
                                    
                                    <div class="d-grid gap-2">
                                        <?php if ($hasApplied && !$hasWithdrawn): ?>
                                            <button type="button" class="btn btn-success btn-lg" disabled>
                                                <i class="fas fa-check"></i> Applied
                                            </button>
                                        <?php elseif ($hasWithdrawn && !$allowReapply): ?>
                                            <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>&reapply=1" class="btn btn-outline-primary btn-lg">
                                                <i class="fas fa-redo-alt"></i> Reapply
                                            </a>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-primary btn-lg apply-button" <?php echo (!$userProfile || !$userProfile['resume_path']) ? 'disabled' : ''; ?>>
                                                <i class="fas fa-paper-plane"></i> Apply Now
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        <?php elseif (!isLoggedIn()): ?>
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle"></i> Please <a href="<?php echo SITE_URL; ?>/pages/login.php" class="alert-link">login</a> or <a href="<?php echo SITE_URL; ?>/pages/register.php" class="alert-link">register</a> as a job seeker to apply for this job.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        Company Information
                    </div>
                    <div class="card-body">
                        <h5>
                            <a href="<?php echo SITE_URL; ?>/pages/view_company_profile.php?id=<?php echo $job['company_id']; ?>" class="text-decoration-none">
                                <?php echo htmlspecialchars($job['company_name']); ?>
                            </a>
                        </h5>
                        <p><?php echo nl2br(htmlspecialchars($job['company_description'])); ?></p>
                        <a href="<?php echo SITE_URL; ?>/pages/view_company_profile.php?id=<?php echo $job['company_id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-building"></i> View Company Profile
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        Share This Job
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(SITE_URL . '/pages/jobs.php?id=' . $job['job_id']); ?>" target="_blank" class="btn btn-outline-primary">
                                <i class="fab fa-linkedin"></i> Share on LinkedIn
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/pages/jobs.php?id=' . $job['job_id']); ?>&text=<?php echo urlencode('Check out this job: ' . $job['job_title']); ?>" target="_blank" class="btn btn-outline-info">
                                <i class="fab fa-twitter"></i> Share on Twitter
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode('Job Opportunity: ' . $job['job_title']); ?>&body=<?php echo urlencode('Check out this job posting: ' . SITE_URL . '/pages/jobs.php?id=' . $job['job_id']); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope"></i> Share via Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Job Search and Listing -->
    <div class="jobs-container">
    <section class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Browse Jobs</h2>
                <p class="text-muted">Find your dream job from our listings</p>
            </div>
        </div>
        
        <div class="search-box mb-4">
            <form action="<?php echo SITE_URL; ?>/pages/jobs.php" method="GET" id="job-search-form">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="keywords" id="keywords" placeholder="Job title, keywords, or company" value="<?php echo htmlspecialchars($keywords); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" name="location" id="location" placeholder="State" value="<?php echo htmlspecialchars($location); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- New filter dropdowns -->
                <div class="row mt-3">
                    <!-- Job Types Dropdown -->
                    <div class="col-md-3 mb-2">
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center" 
                                    type="button" id="jobTypeDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <span>Job Types</span>
                                <?php if (!empty($jobTypes)): ?>
                                <span class="badge bg-primary"><?php echo count($jobTypes); ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu p-3 w-100">
                                <div class="job-type-options" style="max-height: 300px; overflow-y: auto;">
                                    <?php $allJobTypes = getJobTypes(); ?>
                                    <?php foreach($allJobTypes as $type): ?>
                                    <div class="form-check">
                                        <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                               id="type-<?php echo strtolower(str_replace([' ', '-'], '-', $type)); ?>" 
                                               value="<?php echo $type; ?>" 
                                               <?php echo (isset($_GET['job_type']) && in_array($type, (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="type-<?php echo strtolower(str_replace([' ', '-'], '-', $type)); ?>">
                                            <?php echo $type; ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Categories Dropdown -->
                    <div class="col-md-3 mb-2">
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center" 
                                    type="button" id="categoryDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <span>Categories</span>
                                <?php if (!empty($categories)): ?>
                                <span class="badge bg-primary"><?php echo count($categories); ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu p-3 w-100">
                                <div class="category-options" style="max-height: 300px; overflow-y: auto;">
                                    <!-- Technology & IT -->
                                    <div class="mb-2">
                                        <h6>Technology & IT</h6>
                                        <?php
                                        $techCategories = ['Programming', 'Web Development', 'Mobile Development', 'Database Management', 
                                                          'Cloud Computing', 'Cybersecurity', 'DevOps', 'AI & Machine Learning', 
                                                          'Data Science', 'Blockchain'];
                                        foreach ($techCategories as $category):
                                            $fullCategory = "Technology & IT - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Business & Administration -->
                                    <div class="mb-2">
                                        <h6>Business & Administration</h6>
                                        <?php
                                        $businessCategories = ['Project Management', 'Business Analysis', 'Office Administration', 
                                                              'Human Resources', 'Customer Service', 'Supply Chain & Logistics', 
                                                              'Sales & Marketing', 'Accounting & Finance'];
                                        foreach ($businessCategories as $category):
                                            $fullCategory = "Business & Administration - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Healthcare & Medical -->
                                    <div class="mb-2">
                                        <h6>Healthcare & Medical</h6>
                                        <?php
                                        $healthcareCategories = ['Nursing', 'Medicine', 'Pharmacy', 'Medical Laboratory', 
                                                                  'Physiotherapy', 'Dental Care', 'Mental Health'];
                                        foreach ($healthcareCategories as $category):
                                            $fullCategory = "Healthcare & Medical - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Engineering -->
                                    <div class="mb-2">
                                        <h6>Engineering</h6>
                                        <?php
                                        $engineeringCategories = ['Civil Engineering', 'Mechanical Engineering', 'Electrical Engineering', 
                                                                  'Electronics Engineering', 'Chemical Engineering', 'Software Engineering', 
                                                                  'Aerospace Engineering'];
                                        foreach ($engineeringCategories as $category):
                                            $fullCategory = "Engineering - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Creative & Design -->
                                    <div class="mb-2">
                                        <h6>Creative & Design</h6>
                                        <?php
                                        $creativeCategories = ['Graphic Design', 'UI/UX Design', 'Video Editing', 'Animation', 
                                                                  'Photography', 'Content Writing', 'Social Media Management'];
                                        foreach ($creativeCategories as $category):
                                            $fullCategory = "Creative & Design - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Education & Training -->
                                    <div class="mb-2">
                                        <h6>Education & Training</h6>
                                        <?php
                                        $educationCategories = ['Teaching', 'Tutoring', 'E-Learning', 'Corporate Training', 
                                                                 'Language Proficiency'];
                                        foreach ($educationCategories as $category):
                                            $fullCategory = "Education & Training - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Hospitality & Tourism -->
                                    <div class="mb-2">
                                        <h6>Hospitality & Tourism</h6>
                                        <?php
                                        $hospitalityCategories = ['Hotel Management', 'Culinary Arts', 'Event Planning', 
                                                                  'Travel Agency', 'Customer Relations'];
                                        foreach ($hospitalityCategories as $category):
                                            $fullCategory = "Hospitality & Tourism - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Trades & Manual Work -->
                                    <div class="mb-2">
                                        <h6>Trades & Manual Work</h6>
                                        <?php
                                        $tradesCategories = ['Plumbing', 'Electrical Work', 'Carpentry', 'Welding', 
                                                              'Automotive Repair'];
                                        foreach ($tradesCategories as $category):
                                            $fullCategory = "Trades & Manual Work - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Legal & Compliance -->
                                    <div class="mb-2">
                                        <h6>Legal & Compliance</h6>
                                        <?php
                                        $legalCategories = ['Corporate Law', 'Criminal Law', 'Intellectual Property', 'Compliance'];
                                        foreach ($legalCategories as $category):
                                            $fullCategory = "Legal & Compliance - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <!-- Science & Research -->
                                    <div class="mb-2">
                                        <h6>Science & Research</h6>
                                        <?php
                                        $scienceCategories = ['Biotechnology', 'Environmental Science', 'Chemistry', 'Physics'];
                                        foreach ($scienceCategories as $category):
                                            $fullCategory = "Science & Research - $category";
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                   value="<?php echo $fullCategory; ?>" 
                                                   id="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>"
                                                   <?php echo (isset($_GET['categories']) && in_array($fullCategory, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="<?php echo strtolower(str_replace([' ', '-', '&'], '-', $fullCategory)); ?>">
                                                <?php echo $category; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Salary Range Dropdown -->
                    <div class="col-md-3 mb-2">
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center" 
                                    type="button" id="salaryDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <span>Salary Range</span>
                                <?php if (!empty($minSalary) || !empty($maxSalary)): ?>
                                <span class="badge bg-primary">Set</span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu p-3 w-100">
                                <div class="mb-3">
                                    <label for="dropdown-min-salary" class="form-label">Minimum Salary (RM)</label>
                                    <select class="form-select" id="dropdown-min-salary" name="min_salary">
                                        <option value="">Any</option>
                                        <option value="1000" <?php echo $minSalary == 1000 ? 'selected' : ''; ?>>RM 1,000</option>
                                        <option value="2000" <?php echo $minSalary == 2000 ? 'selected' : ''; ?>>RM 2,000</option>
                                        <option value="3000" <?php echo $minSalary == 3000 ? 'selected' : ''; ?>>RM 3,000</option>
                                        <option value="5000" <?php echo $minSalary == 5000 ? 'selected' : ''; ?>>RM 5,000</option>
                                        <option value="8000" <?php echo $minSalary == 8000 ? 'selected' : ''; ?>>RM 8,000</option>
                                        <option value="10000" <?php echo $minSalary == 10000 ? 'selected' : ''; ?>>RM 10,000</option>
                                        <option value="15000" <?php echo $minSalary == 15000 ? 'selected' : ''; ?>>RM 15,000</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="dropdown-max-salary" class="form-label">Maximum Salary (RM)</label>
                                    <select class="form-select" id="dropdown-max-salary" name="max_salary">
                                        <option value="">Any</option>
                                        <option value="3000" <?php echo $maxSalary == 3000 ? 'selected' : ''; ?>>RM 3,000</option>
                                        <option value="5000" <?php echo $maxSalary == 5000 ? 'selected' : ''; ?>>RM 5,000</option>
                                        <option value="8000" <?php echo $maxSalary == 8000 ? 'selected' : ''; ?>>RM 8,000</option>
                                        <option value="10000" <?php echo $maxSalary == 10000 ? 'selected' : ''; ?>>RM 10,000</option>
                                        <option value="15000" <?php echo $maxSalary == 15000 ? 'selected' : ''; ?>>RM 15,000</option>
                                        <option value="20000" <?php echo $maxSalary == 20000 ? 'selected' : ''; ?>>RM 20,000</option>
                                        <option value="30000" <?php echo $maxSalary == 30000 ? 'selected' : ''; ?>>RM 30,000</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Date Posted Dropdown -->
                    <div class="col-md-3 mb-2">
                        <div class="dropdown filter-dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle w-100 d-flex justify-content-between align-items-center" 
                                    type="button" id="datePostedDropdown" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false">
                                <span>Date Posted</span>
                                <?php if (!empty($datePosted) && $datePosted != 'any'): ?>
                                <span class="badge bg-primary">Set</span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu p-3 w-100">
                                <div class="date-posted-options">
                                    <div class="form-check">
                                        <input class="form-check-input date-posted-radio" type="radio" name="date_posted" 
                                               id="date-any" value="any" <?php echo ($datePosted == 'any' || empty($datePosted)) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="date-any">Any time</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input date-posted-radio" type="radio" name="date_posted" 
                                               id="date-today" value="today" <?php echo $datePosted == 'today' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="date-today">Today</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input date-posted-radio" type="radio" name="date_posted" 
                                               id="date-week" value="week" <?php echo $datePosted == 'week' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="date-week">This week</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input date-posted-radio" type="radio" name="date_posted" 
                                               id="date-month" value="month" <?php echo $datePosted == 'month' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="date-month">This month</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input date-posted-radio" type="radio" name="date_posted" 
                                               id="date-three-months" value="three_months" <?php echo $datePosted == 'three_months' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="date-three-months">Last 3 months</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input date-posted-radio" type="radio" name="date_posted" 
                                               id="date-six-months" value="six_months" <?php echo $datePosted == 'six_months' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="date-six-months">Last 6 months</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <!-- Remove old filter section and expand results column to full width -->
            <div class="col-md-12">
                <!-- Active search filters section for live filtering -->
                <div class="alert alert-info mb-3 active-search-filters" style="<?php echo (!empty($jobTypes) || !empty($categories) || !empty($keywords) || !empty($location)) ? 'display:block;' : 'display:none;'; ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-2">Active Filters:</h6>
                            <span class="badge bg-info me-2 keyword-filter" style="<?php echo !empty($keywords) ? 'display:inline-block;' : 'display:none;'; ?>">Keywords: <?php echo htmlspecialchars($keywords); ?></span>
                            <span class="badge bg-primary me-2 location-filter" style="<?php echo !empty($location) ? 'display:inline-block;' : 'display:none;'; ?>">Location: <?php echo htmlspecialchars($location); ?></span>
                            
                            <?php if (!empty($jobTypes)): ?>
                            <span class="badge bg-secondary me-2 job-type-badge">Job Types: <?php echo count($jobTypes); ?> selected</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($categories)): ?>
                            <span class="badge bg-info me-2 category-badge">Categories: <?php echo count($categories); ?> selected</span>
                            <div class="mt-2">
                                <?php foreach($categories as $cat): ?>
                                    <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($cat); ?></span>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="btn btn-sm btn-outline-secondary clear-filters-btn">
                            <i class="fas fa-times"></i> Clear All Filters
                        </a>
                    </div>
                </div>
                
                <?php if (!empty($categories) || !empty($jobTypes) || !empty($keywords) || !empty($location)): ?>
                <!-- Server-side filters display - hidden when JS filters active -->
                <div class="alert alert-info mb-3 server-side-filters" style="display:none;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-2">Active Filters:</h6>
                            <?php if (!empty($keywords)): ?>
                                <span class="badge bg-primary me-2">Keywords: <?php echo htmlspecialchars($keywords); ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($location)): ?>
                                <span class="badge bg-primary me-2">Location: <?php echo htmlspecialchars($location); ?></span>
                            <?php endif; ?>
                            
                            <?php if (!empty($jobTypes)): ?>
                                <span class="badge bg-secondary me-2">Job Types: <?php echo count($jobTypes); ?> selected</span>
                            <?php endif; ?>
                            
                            <?php if (!empty($categories)): ?>
                                <span class="badge bg-info me-2">Categories: <?php echo count($categories); ?> selected</span>
                                <div class="mt-2">
                                    <?php foreach($categories as $cat): ?>
                                        <span class="badge bg-light text-dark me-1"><?php echo htmlspecialchars($cat); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="btn btn-sm btn-outline-secondary clear-filters-btn">
                            <i class="fas fa-times"></i> Clear All Filters
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (isset($jobs) && !empty($jobs)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0 job-count"><?php echo count($jobs); ?> jobs found</p>
                        <div class="dropdown">
                            <?php 
                            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
                            $sortText = 'Newest';
                            if ($sort == 'salary_high') $sortText = 'Salary (high to low)';
                            if ($sort == 'salary_low') $sortText = 'Salary (low to high)';
                            if ($sort == 'company') $sortText = 'Company Name';
                            ?>
                            <button class="btn btn-outline-dark dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Sort by: <?php echo $sortText; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="sortDropdown">
                                <li>
                                    <a class="dropdown-item <?php echo ($sort == 'newest') ? 'active' : ''; ?>" 
                                       href="<?php echo SITE_URL; ?>/pages/jobs.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>">
                                       Newest
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo ($sort == 'salary_high') ? 'active' : ''; ?>" 
                                       href="<?php echo SITE_URL; ?>/pages/jobs.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'salary_high'])); ?>">
                                       Salary (high to low)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo ($sort == 'salary_low') ? 'active' : ''; ?>" 
                                       href="<?php echo SITE_URL; ?>/pages/jobs.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'salary_low'])); ?>">
                                       Salary (low to high)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item <?php echo ($sort == 'company') ? 'active' : ''; ?>" 
                                       href="<?php echo SITE_URL; ?>/pages/jobs.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'company'])); ?>">
                                       Company Name
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <?php foreach ($jobs as $job): ?>
                        <div class="card job-card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <h5 class="job-title">
                                            <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($job['job_title']); ?>
                                            </a>
                                        </h5>
                                        <h6 class="company-name">
                                            <a href="<?php echo SITE_URL; ?>/pages/view_company_profile.php?id=<?php echo $job['company_id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($job['company_name']); ?>
                                            </a>
                                        </h6>
                                        
                                        <div class="job-details">
                                            <div class="job-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($job['location']); ?></span>
                                            </div>
                                            <div class="job-detail">
                                                <i class="fas fa-briefcase"></i>
                                                <span><?php echo htmlspecialchars($job['job_type']); ?></span>
                                            </div>
                                            <div class="job-detail">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span><?php echo formatSalaryRange($job['salary_min'], $job['salary_max']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <p class="mt-2"><?php echo substr(htmlspecialchars($job['description']), 0, 150) . '...'; ?></p>
                                        
                                        <?php if (!empty($job['categories'])): ?>
                                        <div class="mt-2">
                                            <?php 
                                            $categoryArray = explode(', ', html_entity_decode($job['categories']));
                                            $displayCategories = array_slice($categoryArray, 0, 3); // Show only first 3 categories
                                            foreach($displayCategories as $category): 
                                            ?>
                                                <span class="badge bg-light text-dark me-1"><?php echo $category; ?></span>
                                            <?php endforeach; ?>
                                            
                                            <?php if (count($categoryArray) > 3): ?>
                                                <span class="badge bg-secondary">+<?php echo count($categoryArray) - 3; ?> more</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                        <div class="job-detail mb-2">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo timeElapsed($job['posted_date']); ?></span>
                                        </div>
                                        <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="btn btn-outline-primary btn-sm">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info no-results-message">
                        <i class="fas fa-info-circle"></i> No jobs found matching your search criteria. Please try different keywords or location.
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Static spacer to ensure enough room for dropdown -->
        <div class="dropdown-spacer"></div>
    </section>
    </div>
<?php endif; ?>

<?php
// Set extra JavaScript for jobs page 
$extraJS = '<script src="' . SITE_URL . '/assets/js/jobs.js"></script>
<script>
// Initialize filter count badges when page loads
document.addEventListener("DOMContentLoaded", function() {
    // Update the active filters on page load
    updateActiveFilters();

    // Handle immediate filtering for job type checkboxes
    document.querySelectorAll(".job-type-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            // Submit the form immediately when a checkbox is checked/unchecked
            document.getElementById("job-search-form").submit();
        });
    });

    // Handle immediate filtering for category checkboxes
    document.querySelectorAll(".category-checkbox").forEach(checkbox => {
        checkbox.addEventListener("change", function() {
            // Submit the form immediately when a checkbox is checked/unchecked
            document.getElementById("job-search-form").submit();
        });
    });

    // Handle immediate filtering for salary range dropdowns
    document.getElementById("dropdown-min-salary").addEventListener("change", function() {
        document.getElementById("job-search-form").submit();
    });
    document.getElementById("dropdown-max-salary").addEventListener("change", function() {
        document.getElementById("job-search-form").submit();
    });

    // Handle immediate filtering for date posted radio buttons
    document.querySelectorAll(".date-posted-radio").forEach(radio => {
        radio.addEventListener("change", function() {
            document.getElementById("job-search-form").submit();
        });
    });
    
    // Function to update active filters display
    function updateActiveFilters() {
        const activeFiltersSection = document.querySelector(".active-search-filters");
        
        // Check for active filters
        const hasJobTypes = ' . (!empty($jobTypes) ? 'true' : 'false') . ';
        const hasCategories = ' . (!empty($categories) ? 'true' : 'false') . ';
        const hasKeywords = ' . (!empty($keywords) ? 'true' : 'false') . ';
        const hasLocation = ' . (!empty($location) ? 'true' : 'false') . ';
        const hasMinSalary = ' . (!empty($minSalary) ? 'true' : 'false') . ';
        const hasMaxSalary = ' . (!empty($maxSalary) ? 'true' : 'false') . ';
        const hasDatePosted = ' . ((!empty($datePosted) && $datePosted != "any") ? 'true' : 'false') . ';
        
        // Check if any filters are active
        const hasAnyFilter = hasJobTypes || hasCategories || hasKeywords || hasLocation || 
                             hasMinSalary || hasMaxSalary || hasDatePosted;
        
        // Show/hide active filters section
        if (activeFiltersSection) {
            activeFiltersSection.style.display = hasAnyFilter ? "block" : "none";
        }
        
        // Display appropriate badges
        if (hasKeywords) {
            document.querySelector(".keyword-filter").style.display = "inline-block";
        }
        
        if (hasLocation) {
            document.querySelector(".location-filter").style.display = "inline-block";
        }
    }
    
    // Ensure search bar submits via GET method
    const jobSearchForm = document.getElementById("job-search-form");
    if (jobSearchForm) {
        jobSearchForm.setAttribute("method", "GET");
        
        // Submit form on Enter key in search inputs
        ["keywords", "location"].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener("keydown", function(e) {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        jobSearchForm.submit();
                    }
                });
            }
        });
    }
    
    // Ensure dropdown spacer is visible
    const dropdownSpacer = document.querySelector(".dropdown-spacer");
    if (dropdownSpacer) {
        dropdownSpacer.style.display = "block";
        dropdownSpacer.style.minHeight = "300px";
    }
    
    // Add event listeners for clear filters buttons
    document.querySelectorAll(".clear-filters-btn").forEach(button => {
        button.addEventListener("click", function(e) {
            // Prevent default anchor behavior
            e.preventDefault();
            
            // Redirect to the jobs page without any filters
            window.location.href = button.getAttribute("href");
        });
    });
});
</script>';


// Include footer
require_once '../includes/footer.php';
?>
