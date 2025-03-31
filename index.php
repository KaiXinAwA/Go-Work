<?php
$pageTitle = 'Home';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';

// Get latest jobs
$latestJobs = getLatestJobs(6);

// Include header
require_once $root_path . 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 hero-content">
                <h1 class="hero-title">Find Your Dream Job Today</h1>
                <p class="hero-subtitle">Connect with top employers and discover career opportunities that match your skills and aspirations.</p>
                <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="btn btn-light btn-lg">Browse Jobs</a>
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-outline-light btn-lg ms-2">Sign Up</a>
                <?php endif; ?>
            </div>
            <div class="col-md-6 d-none d-md-block">
                <img src="<?php echo SITE_URL; ?>/assets/img/hero-image.svg" alt="Job Search" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<!-- Search Box -->
<div class="container">
    <div class="search-box">
        <form action="<?php echo SITE_URL; ?>/pages/jobs.php" method="GET" id="job-search-form">
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="keywords" id="keywords" placeholder="Job title, keywords, or company">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" class="form-control" name="location" id="location" placeholder="City, state, or zip code">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Latest Jobs Section -->
<section class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Latest Job Opportunities</h2>
            <p class="text-muted">Explore our most recent job listings from top employers</p>
        </div>
    </div>
    
    <div class="row">
        <?php if (!empty($latestJobs)): ?>
            <?php foreach ($latestJobs as $job): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card job-card h-100">
                        <div class="card-body">
                            <h5 class="job-title"><?php echo htmlspecialchars($job['job_title']); ?></h5>
                            <h6 class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                            
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
                                <div class="job-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo timeElapsed($job['posted_date']); ?></span>
                                </div>
                            </div>
                            
                            <p class="mt-3"><?php echo substr(htmlspecialchars($job['description']), 0, 100) . '...'; ?></p>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>" class="btn btn-outline-primary">View Details</a>
                            <?php if (isLoggedIn() && hasUserType(USER_TYPE_JOBSEEKER)): ?>
                                <a href="<?php echo SITE_URL; ?>/pages/jobs.php?id=<?php echo $job['job_id']; ?>#apply" class="btn btn-primary float-end">Apply Now</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No job listings available at the moment. Please check back later.
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="btn btn-lg btn-outline-primary">View All Jobs</a>
    </div>
</section>

<!-- How It Works Section -->
<section class="container mt-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2>How GoWork Works</h2>
            <p class="text-muted">Simple steps to find your next job or hire qualified candidates</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h4>Create an Account</h4>
                    <p>Sign up as a job seeker or employer to access all features.</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h4>Upload Your Resume</h4>
                        <p>Complete your profile and upload your resume to stand out to employers.</p>
                    <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                        <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                        <h4>Post a Job</h4>
                        <p>Create a detailed job listing to attract qualified candidates.</p>
                    <?php else: ?>
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                        <h4>Search Jobs or Talent</h4>
                        <p>Find the perfect job or candidate using our powerful search tools.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                        <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                        <h4>Apply with One Click</h4>
                        <p>Easily apply to jobs and track your application status.</p>
                    <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h4>Review Applications</h4>
                        <p>Access resumes and contact qualified candidates directly.</p>
                    <?php else: ?>
                        <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                        <h4>Connect and Succeed</h4>
                        <p>Start interviews, get hired, or find your ideal employee.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?>
