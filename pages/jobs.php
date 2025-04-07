<?php
$pageTitle = 'Browse Jobs';
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Get search parameters
// Get all filter parameters
$keywords = isset($_GET['keywords']) ? sanitizeInput($_GET['keywords']) : '';
$location = isset($_GET['location']) ? sanitizeInput($_GET['location']) : '';
$workClassifications = isset($_GET['work_classification']) ? $_GET['work_classification'] : [];
$workTypes = isset($_GET['work_type']) ? $_GET['work_type'] : [];
$locationIds = isset($_GET['location_id']) ? $_GET['location_id'] : [];
$skills = isset($_GET['skills']) ? $_GET['skills'] : [];
$datePosted = isset($_GET['date_posted']) ? sanitizeInput($_GET['date_posted']) : 'any';
$sort = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';

// 处理薪资范围
$salaryMin = null;
$salaryMax = null;
if (isset($_GET['salary_min']) && !empty($_GET['salary_min'])) {
    $salaryMin = (float)$_GET['salary_min'];
}
if (isset($_GET['salary_max']) && !empty($_GET['salary_max'])) {
    $salaryMax = (float)$_GET['salary_max'];
}

// Check if a specific job ID is requested
$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get job data
if ($job_id > 0) {
    // Get single job details
    $job = fetchRow(
        "SELECT j.*, c.company_name, c.description as company_description, 
                wc.name as classification_name, wt.name as work_type_name, l.name as location_name 
         FROM jobs j 
         JOIN companies c ON j.company_id = c.company_id 
         LEFT JOIN work_classifications wc ON j.work_classification_id = wc.classification_id
         LEFT JOIN work_types wt ON j.work_type_id = wt.type_id
         LEFT JOIN locations l ON j.location_id = l.location_id
         WHERE j.job_id = ? AND j.is_active = 1", 
        'i', 
        [$job_id]
    );
} else {
    // Search for jobs with filters
    $jobs = searchJobs($keywords, $location, $workClassifications, $workTypes, $salaryMin, $salaryMax, $skills, $datePosted, $sort);
}

// Include header
require_once '../includes/header.php';
?>

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
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg" <?php echo (!$userProfile || !$userProfile['resume_path']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-paper-plane"></i> Apply Now
                                        </button>
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
                        <h5><?php echo htmlspecialchars($job['company_name']); ?></h5>
                        <p><?php echo nl2br(htmlspecialchars($job['company_description'])); ?></p>
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
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="keywords" id="keywords" placeholder="Job title, keywords, or company" value="<?php echo htmlspecialchars($keywords); ?>">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" name="location" id="location" placeholder="City, state, or zip code" value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
                
                <!-- Job Query Toggle Button -->
                <div class="text-end mt-2">
                    <button type="button" class="btn btn-link" id="toggleJobQuery">
                        <i class="fas fa-chevron-down"></i> Job Query
                    </button>
                </div>
                
                <!-- Job Query -->
                <div class="job-query mt-3" id="jobQuery" style="display: none;">
                    <div class="row g-3">
                        <!-- Work Classification -->
                        <div class="col-md-6">
                            <label class="form-label">Work Classification</label>
                            <select class="form-select" name="work_classification[]" multiple>
                                <?php
                                $classifications = fetchAll("SELECT * FROM work_classifications ORDER BY name");
                                $selectedClassifications = isset($_GET['work_classification']) ? $_GET['work_classification'] : [];
                                foreach ($classifications as $classification):
                                    $selected = in_array($classification['classification_id'], $selectedClassifications) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $classification['classification_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($classification['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Work Type -->
                        <div class="col-md-6">
                            <label class="form-label">Work Type</label>
                            <select class="form-select" name="work_type[]" multiple>
                                <?php
                                $workTypes = fetchAll("SELECT * FROM work_types ORDER BY name");
                                $selectedTypes = isset($_GET['work_type']) ? $_GET['work_type'] : [];
                                foreach ($workTypes as $type):
                                    $selected = in_array($type['type_id'], $selectedTypes) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $type['type_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Salary Range -->
                        <div class="col-md-6">
                            <label class="form-label">Salary Range</label>
                            <select class="form-select" name="salary_range[]" multiple>
                                <option value="1700-2700" <?php echo (isset($_GET['salary_range']) && in_array('1700-2700', $_GET['salary_range'])) ? 'selected' : ''; ?>>1700-2700</option>
                                <option value="2700-3700" <?php echo (isset($_GET['salary_range']) && in_array('2700-3700', $_GET['salary_range'])) ? 'selected' : ''; ?>>2700-3700</option>
                                <option value="3700-5000" <?php echo (isset($_GET['salary_range']) && in_array('3700-5000', $_GET['salary_range'])) ? 'selected' : ''; ?>>3700-5000</option>
                                <option value="5000-7000" <?php echo (isset($_GET['salary_range']) && in_array('5000-7000', $_GET['salary_range'])) ? 'selected' : ''; ?>>5000-7000</option>
                                <option value="7000-9000" <?php echo (isset($_GET['salary_range']) && in_array('7000-9000', $_GET['salary_range'])) ? 'selected' : ''; ?>>7000-9000</option>
                                <option value="9000-above" <?php echo (isset($_GET['salary_range']) && in_array('9000-above', $_GET['salary_range'])) ? 'selected' : ''; ?>>9000 above</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select class="form-select" name="location_id[]" multiple>
                                <?php
                                $locations = fetchAll("SELECT * FROM locations ORDER BY name");
                                $selectedLocations = isset($_GET['location_id']) ? $_GET['location_id'] : [];
                                foreach ($locations as $loc):
                                    $selected = in_array($loc['location_id'], $selectedLocations) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $loc['location_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($loc['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Skills -->
                        <div class="col-md-12">
                            <label class="form-label">Required Skills</label>
                            <select class="form-select" name="skills[]" multiple>
                                <?php
                                $skills = fetchAll("SELECT * FROM skills ORDER BY category, name");
                                $selectedSkills = isset($_GET['skills']) ? $_GET['skills'] : [];
                                $currentCategory = '';
                                foreach ($skills as $skill):
                                    if ($currentCategory != $skill['category']):
                                        if ($currentCategory != '') echo '</optgroup>';
                                        echo '<optgroup label="' . htmlspecialchars($skill['category']) . '">';
                                        $currentCategory = $skill['category'];
                                    endif;
                                    $selected = in_array($skill['skill_id'], $selectedSkills) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $skill['skill_id']; ?>" <?php echo $selected; ?>>
                                        <?php echo htmlspecialchars($skill['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="row">
            <!-- Filter section - always visible -->
            <div class="col-md-4 mb-4">
                <!-- Job Filters -->
                <div class="card">
                    <div class="card-header">
                        Filter Jobs
                    </div>
                    <div class="card-body">
                        <form action="<?php echo SITE_URL; ?>/pages/jobs.php" method="GET" id="advanced-filter-form">
                            <!-- Preserve main search parameters -->
                            <input type="hidden" name="keywords" value="<?php echo htmlspecialchars($keywords); ?>">
                            <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                            
                            <h6>Job Type</h6>
                            <div class="mb-3">
                                <?php 
                                $selectedTypes = isset($_GET['work_type']) ? $_GET['work_type'] : []; 
                                $workTypes = fetchAll("SELECT * FROM work_types ORDER BY name");
                                foreach ($workTypes as $type):
                                    $typeId = strtolower(str_replace(' ', '-', $type['name']));
                                    $checked = in_array($type['type_id'], $selectedTypes) ? 'checked' : '';
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="work_type[]" 
                                           value="<?php echo $type['type_id']; ?>" 
                                           id="type-<?php echo $typeId; ?>" <?php echo $checked; ?>>
                                    <label class="form-check-label" for="type-<?php echo $typeId; ?>">
                                        <?php echo htmlspecialchars($type['name']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <h6>Salary Range</h6>
                            <div class="mb-3">
                                <select class="form-select" name="salary_min">
                                    <option value="">Any Salary</option>
                                    <option value="30000" <?php echo (isset($_GET['salary_min']) && $_GET['salary_min'] == '30000') ? 'selected' : ''; ?>>$30,000+</option>
                                    <option value="50000" <?php echo (isset($_GET['salary_min']) && $_GET['salary_min'] == '50000') ? 'selected' : ''; ?>>$50,000+</option>
                                    <option value="70000" <?php echo (isset($_GET['salary_min']) && $_GET['salary_min'] == '70000') ? 'selected' : ''; ?>>$70,000+</option>
                                    <option value="100000" <?php echo (isset($_GET['salary_min']) && $_GET['salary_min'] == '100000') ? 'selected' : ''; ?>>$100,000+</option>
                                </select>
                            </div>
                            
                            <h6>Date Posted</h6>
                            <div class="mb-3">
                                <?php 
                                $datePosted = isset($_GET['date_posted']) ? $_GET['date_posted'] : 'any';
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="date_posted" 
                                           id="date-any" value="any" 
                                           <?php echo ($datePosted == 'any' || empty($datePosted)) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="date-any">
                                        Any time
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="date_posted" 
                                           id="date-today" value="today" 
                                           <?php echo ($datePosted == 'today') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="date-today">
                                        Today
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="date_posted" 
                                           id="date-week" value="week" 
                                           <?php echo ($datePosted == 'week') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="date-week">
                                        This week
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="date_posted" 
                                           id="date-month" value="month" 
                                           <?php echo ($datePosted == 'month') ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="date-month">
                                        This month
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-outline-primary">Apply Filters</button>
                                <?php if(!empty($workClassifications) || !empty($workTypes) || !empty($salaryMin) || !empty($salaryMax) || !empty($skills) || $datePosted != 'any'): ?>
                                    <button type="button" class="btn btn-outline-secondary mt-2" id="clear-filters">Clear Filters</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Results section - shows jobs or no results message -->
            <div class="col-md-8">
                <?php if (isset($jobs) && !empty($jobs)): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0"><?php echo count($jobs); ?> jobs found</p>
                        <div class="dropdown">
                            <?php 
                            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
                            $sortText = 'Newest';
                            if ($sort == 'salary_high') $sortText = 'Salary (high to low)';
                            if ($sort == 'salary_low') $sortText = 'Salary (low to high)';
                            if ($sort == 'company') $sortText = 'Company Name';
                            ?>
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
                                        <h6 class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                        
                                        <div class="job-details">
                                            <div class="job-detail">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($job['location']); ?></span>
                                            </div>
                                            <div class="job-detail">
                                                <i class="fas fa-briefcase"></i>
                                                <span><?php echo htmlspecialchars($job['work_type_name']); ?></span>
                                            </div>
                                            <div class="job-detail">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span><?php echo formatSalaryRange($job['salary_min'], $job['salary_max']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <p class="mt-2"><?php echo substr(htmlspecialchars($job['description']), 0, 150) . '...'; ?></p>
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No jobs found matching your search criteria. Please try different keywords or location.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php
// Set extra JavaScript for jobs page
$extraJS = '<script src="' . SITE_URL . '/assets/js/jobs.js"></script>';

// Include footer
require_once '../includes/footer.php';
?>
