<?php
$pageTitle = 'Browse Jobs';
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Add custom CSS for nested accordion
$customCSS = '<style>
    /* Accordion styles */
    .nested-accordion .accordion-item {
        border-radius: 0;
        border-left: none;
        border-right: none;
        border-top: 1px solid rgba(0,0,0,.125);
    }
    
    .nested-accordion .accordion-item:first-child {
        border-top: none;
    }
    
    .nested-accordion .accordion-button {
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
    }
    
    .nested-accordion .accordion-body {
        padding: 0.5rem 1rem;
        max-height: 220px;
        overflow-y: auto;
    }
    
    .nested-accordion .form-check {
        margin-bottom: 0.3rem;
    }
    
    .nested-accordion .form-check-label {
        font-size: 0.9rem;
    }
    
    /* Container layout */
    .jobs-container {
        position: relative;
        z-index: 50;
        min-height: 500px; /* Reduced from 800px */
    }
    
    section.container {
        padding-top: 10px;
    }
    
    /* Filter dropdown styles - minimal version */
    .filter-dropdown .dropdown-menu {
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(0, 0, 0, 0.1);
        margin-top: 0.5rem;
    }
    
    .filter-dropdown .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #0d6efd;
        box-shadow: none;
    }
    
    .filter-dropdown h6 {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .filter-dropdown hr {
        margin: 0.75rem 0;
        opacity: 0.1;
    }
    
    .filter-dropdown .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    /* Ensure space for dropdown */
    .col-md-8 {
        min-height: 400px; /* Reduced from 600px */
    }
    
    /* Force spacer to be visible */
    .dropdown-spacer {
        display: block !important;
        min-height: 150px; /* Reduced from 300px */
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
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <!-- Filter section - simplified dropdown -->
            <div class="col-md-4 mb-4">
                <!-- Job Filters -->
                <div class="dropdown filter-dropdown">
                    <button class="btn btn-primary w-100 d-flex justify-content-between align-items-center" 
                            type="button" id="filterDropdown" 
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span>
                                <i class="fas fa-filter me-2"></i> Filter Jobs
                            </span>
                            <span>
                                <span class="filter-count badge bg-light text-primary" style="display: none;">0</span>
                                <i class="fas fa-chevron-down ms-2"></i>
                            </span>
                    </button>
                    <div class="dropdown-menu p-3 filter-menu w-100" 
                         aria-labelledby="filterDropdown">
                        <form action="<?php echo SITE_URL; ?>/pages/jobs.php" method="GET" id="advanced-filter-form">
                            <!-- Preserve main search parameters -->
                            <input type="hidden" name="keywords" value="<?php echo htmlspecialchars($keywords); ?>">
                            <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                            
                            <h6>Job Type</h6>
                            <div class="mb-3">
                                <div class="accordion" id="jobTypeAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#jobTypesCollapse">
                                                Select Job Types
                                                <span class="badge bg-primary ms-2 job-type-count" style="display: none;">0</span>
                                            </button>
                                        </h2>
                                        <div id="jobTypesCollapse" class="accordion-collapse collapse" data-bs-parent="#jobTypeAccordion">
                                            <div class="accordion-body" style="max-height: 300px; overflow-y: auto;">
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-fulltime" value="Full-time" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Full-time', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-fulltime">
                                                        Full-time
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-parttime" value="Part-time" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Part-time', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-parttime">
                                                        Part-time
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-contract" value="Contract" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Contract', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-contract">
                                                        Contract
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-temporary" value="Temporary" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Temporary', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-temporary">
                                                        Temporary
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-internship" value="Internship" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Internship', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-internship">
                                                        Internship
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-freelance" value="Freelance" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Freelance', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-freelance">
                                                        Freelance
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-volunteer" value="Volunteer" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Volunteer', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-volunteer">
                                                        Volunteer
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-remote" value="Remote" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Remote', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-remote">
                                                        Remote
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-hybrid" value="Hybrid" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Hybrid', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-hybrid">
                                                        Hybrid
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-onsite" value="On-site" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('On-site', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-onsite">
                                                        On-site
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-seasonal" value="Seasonal" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Seasonal', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-seasonal">
                                                        Seasonal
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-apprenticeship" value="Apprenticeship" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Apprenticeship', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-apprenticeship">
                                                        Apprenticeship
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-consultant" value="Consultant" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Consultant', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-consultant">
                                                        Consultant
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-perdiem" value="Per Diem" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Per Diem', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-perdiem">
                                                        Per Diem
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-wfh" value="Work From Home" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Work From Home', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-wfh">
                                                        Work From Home
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-gig" value="Gig" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Gig', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-gig">
                                                        Gig
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-entrylevel" value="Entry-level" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Entry-level', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-entrylevel">
                                                        Entry-level
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-executive" value="Executive" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Executive', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-executive">
                                                        Executive
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-commission" value="Commission-based" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Commission-based', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-commission">
                                                        Commission-based
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-nightshift" value="Night Shift" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Night Shift', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-nightshift">
                                                        Night Shift
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-travelnursing" value="Travel Nursing" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Travel Nursing', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-travelnursing">
                                                        Travel Nursing
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-tenuretrack" value="Tenure-track" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Tenure-track', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-tenuretrack">
                                                        Tenure-track
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-locumtenens" value="Locum Tenens" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Locum Tenens', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-locumtenens">
                                                        Locum Tenens
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-performingartist" value="Performing Artist" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Performing Artist', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-performingartist">
                                                        Performing Artist
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input job-type-checkbox" type="checkbox" name="job_type[]" 
                                                           id="type-adjunct" value="Adjunct" 
                                                           <?php echo (isset($_GET['job_type']) && in_array('Adjunct', (array)$_GET['job_type'])) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="type-adjunct">
                                                        Adjunct
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h6>Categories</h6>
                            <div class="mb-3">
                                <div class="accordion" id="categoryAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#categoriesCollapse">
                                                Select Categories
                                                <span class="badge bg-primary ms-2 category-count" style="display: none;">0</span>
                                            </button>
                                        </h2>
                                        <div id="categoriesCollapse" class="accordion-collapse collapse" data-bs-parent="#categoryAccordion">
                                            <div class="accordion-body">
                                                <div class="nested-accordion">
                                                    <!-- Technology & IT -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#techCategory" aria-expanded="false">
                                                                Technology & IT
                                                            </button>
                                                        </h2>
                                                        <div id="techCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $techCategories = ['Programming', 'Web Development', 'Mobile Development', 'Database Management', 
                                                                                  'Cloud Computing', 'Cybersecurity', 'DevOps', 'AI & Machine Learning', 
                                                                                  'Data Science', 'Blockchain'];
                                                                foreach ($techCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Technology & IT - <?php echo $category; ?>" 
                                                                           id="tech-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Technology & IT - " . $category, (array)$_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="tech-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Business & Administration -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#businessCategory" aria-expanded="false">
                                                                Business & Administration
                                                            </button>
                                                        </h2>
                                                        <div id="businessCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $businessCategories = ['Project Management', 'Business Analysis', 'Office Administration', 
                                                                                      'Human Resources', 'Customer Service', 'Supply Chain & Logistics', 
                                                                                      'Sales & Marketing', 'Accounting & Finance'];
                                                                foreach ($businessCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Business & Administration - <?php echo $category; ?>" 
                                                                           id="business-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Business & Administration - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="business-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Healthcare & Medical -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#healthcareCategory" aria-expanded="false">
                                                                Healthcare & Medical
                                                            </button>
                                                        </h2>
                                                        <div id="healthcareCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $healthcareCategories = ['Nursing', 'Medicine', 'Pharmacy', 'Medical Laboratory', 
                                                                                        'Physiotherapy', 'Dental Care', 'Mental Health'];
                                                                foreach ($healthcareCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Healthcare & Medical - <?php echo $category; ?>" 
                                                                           id="healthcare-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Healthcare & Medical - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="healthcare-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Engineering -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#engineeringCategory" aria-expanded="false">
                                                                Engineering
                                                            </button>
                                                        </h2>
                                                        <div id="engineeringCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $engineeringCategories = ['Civil Engineering', 'Mechanical Engineering', 'Electrical Engineering', 
                                                                                        'Electronics Engineering', 'Chemical Engineering', 'Software Engineering', 
                                                                                        'Aerospace Engineering'];
                                                                foreach ($engineeringCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Engineering - <?php echo $category; ?>" 
                                                                           id="engineering-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Engineering - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="engineering-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Creative & Design -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#creativeCategory" aria-expanded="false">
                                                                Creative & Design
                                                            </button>
                                                        </h2>
                                                        <div id="creativeCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $creativeCategories = ['Graphic Design', 'UI/UX Design', 'Video Editing', 'Animation', 
                                                                                      'Photography', 'Content Writing', 'Social Media Management'];
                                                                foreach ($creativeCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Creative & Design - <?php echo $category; ?>" 
                                                                           id="creative-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Creative & Design - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="creative-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Education & Training -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#educationCategory" aria-expanded="false">
                                                                Education & Training
                                                            </button>
                                                        </h2>
                                                        <div id="educationCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $educationCategories = ['Teaching', 'Tutoring', 'E-Learning', 'Corporate Training', 
                                                                                       'Language Proficiency'];
                                                                foreach ($educationCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Education & Training - <?php echo $category; ?>" 
                                                                           id="education-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Education & Training - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="education-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Hospitality & Tourism -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hospitalityCategory" aria-expanded="false">
                                                                Hospitality & Tourism
                                                            </button>
                                                        </h2>
                                                        <div id="hospitalityCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $hospitalityCategories = ['Hotel Management', 'Culinary Arts', 'Event Planning', 
                                                                                         'Travel Agency', 'Customer Relations'];
                                                                foreach ($hospitalityCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Hospitality & Tourism - <?php echo $category; ?>" 
                                                                           id="hospitality-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Hospitality & Tourism - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="hospitality-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Trades & Manual Work -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tradesCategory" aria-expanded="false">
                                                                Trades & Manual Work
                                                            </button>
                                                        </h2>
                                                        <div id="tradesCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $tradesCategories = ['Plumbing', 'Electrical Work', 'Carpentry', 'Welding', 
                                                                                    'Automotive Repair'];
                                                                foreach ($tradesCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Trades & Manual Work - <?php echo $category; ?>" 
                                                                           id="trades-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Trades & Manual Work - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="trades-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Legal & Compliance -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#legalCategory" aria-expanded="false">
                                                                Legal & Compliance
                                                            </button>
                                                        </h2>
                                                        <div id="legalCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $legalCategories = ['Corporate Law', 'Criminal Law', 'Intellectual Property', 'Compliance'];
                                                                foreach ($legalCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Legal & Compliance - <?php echo $category; ?>" 
                                                                           id="legal-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Legal & Compliance - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="legal-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Science & Research -->
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#scienceCategory" aria-expanded="false">
                                                                Science & Research
                                                            </button>
                                                        </h2>
                                                        <div id="scienceCategory" class="accordion-collapse collapse" data-bs-parent="">
                                                            <div class="accordion-body">
                                                                <?php
                                                                $scienceCategories = ['Biotechnology', 'Environmental Science', 'Chemistry', 'Physics'];
                                                                foreach ($scienceCategories as $category):
                                                                ?>
                                                                <div class="form-check">
                                                                    <input class="form-check-input category-checkbox" type="checkbox" name="categories[]" 
                                                                           value="Science & Research - <?php echo $category; ?>" 
                                                                           id="science-<?php echo strtolower(str_replace(' ', '-', $category)); ?>"
                                                                           <?php echo (isset($_GET['categories']) && in_array("Science & Research - " . $category, $_GET['categories'])) ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="science-<?php echo strtolower(str_replace(' ', '-', $category)); ?>">
                                                                        <?php echo $category; ?>
                                                                    </label>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6>Salary Range</h6>
                            <div class="mb-3">
                                    <div class="row g-3">
                                    <div class="col-6">
                                        <label for="min_salary" class="form-label small">From (RM)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        <input type="number" class="form-control" id="min_salary" name="min_salary" 
                                               placeholder="Min" min="0" step="100" 
                                               value="<?php echo isset($_GET['min_salary']) ? htmlspecialchars($_GET['min_salary']) : ''; ?>">
                                            </div>
                                    </div>
                                    <div class="col-6">
                                        <label for="max_salary" class="form-label small">To (RM)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                        <input type="number" class="form-control" id="max_salary" name="max_salary" 
                                               placeholder="Max" min="0" step="100" 
                                               value="<?php echo isset($_GET['max_salary']) ? htmlspecialchars($_GET['max_salary']) : ''; ?>">
                                            </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h6>Date Posted</h6>
                            <div class="mb-3">
                                <?php 
                                $datePosted = isset($_GET['date_posted']) ? $_GET['date_posted'] : 'any';
                                ?>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                <select class="form-select" name="date_posted" id="date_posted">
                                    <option value="any" <?php echo ($datePosted == 'any' || empty($datePosted)) ? 'selected' : ''; ?>>Any time</option>
                                    <option value="today" <?php echo ($datePosted == 'today') ? 'selected' : ''; ?>>Today</option>
                                    <option value="week" <?php echo ($datePosted == 'week') ? 'selected' : ''; ?>>This week</option>
                                    <option value="month" <?php echo ($datePosted == 'month') ? 'selected' : ''; ?>>This month</option>
                                    <option value="three_months" <?php echo ($datePosted == 'three_months') ? 'selected' : ''; ?>>Last 3 months</option>
                                    <option value="six_months" <?php echo ($datePosted == 'six_months') ? 'selected' : ''; ?>>Last 6 months</option>
                                </select>
                                    </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Results section - shows jobs or no results message -->
            <div class="col-md-8">
                <!-- Active search filters section for live filtering -->
                <div class="alert alert-info mb-3 active-search-filters" style="display: none;">
                    <h6 class="mb-2">Active Filters:</h6>
                    <span class="badge bg-primary me-2 keyword-filter" style="display: none;">Keywords: </span>
                    <span class="badge bg-primary me-2 location-filter" style="display: none;">Location: </span>
                </div>
                
                <?php if (!empty($categories) || !empty($jobTypes) || !empty($keywords) || !empty($location)): ?>
                <!-- Server-side filters display - hidden when JS filters active -->
                <div class="alert alert-info mb-3 server-side-filters">
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
    </section>
    </div>
<?php endif; ?>

<?php
// Set extra JavaScript for jobs page 
$extraJS = '<script src="' . SITE_URL . '/assets/js/jobs-dropdown.js"></script>
<script src="' . SITE_URL . '/assets/js/jobs.js"></script>';

// Include footer
require_once '../includes/footer.php';
?>
