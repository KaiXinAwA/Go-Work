<?php
$pageTitle = 'Home';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/database.php';
require_once $root_path . 'includes/functions.php';
?>

<style>
    body.home-page {
        padding-top: 0;
    }

    /* Transparent navbar */
    .navbar {
        background-color: transparent !important;
        position: fixed;
        width: 100%;
        z-index: 1000;
        padding: 1rem 0;
        transition: all 0.3s ease;
        box-shadow: none;
        height: 70px;
        display: flex;
        align-items: center;
    }

    .navbar.scrolled {
        background-color: #000000 !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .navbar .nav-link {
        position: relative;
        padding: 8px 15px;
        transition: transform 0.3s ease, color 0.3s ease;
        display: inline-block;
        font-weight: 500;
        color: #fff !important;
    }
    
    .navbar .nav-link::after {
        content: '';
        width: 0%;
        height: 2px;
        background-color: #fff;
        display: block;
        margin: auto;
        transition: 0.5s;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }
    
    .navbar .nav-link:hover {
        transform: scale(1.1);
        color: #ffffff !important;
    }
    
    .navbar .nav-link:hover::after {
        width: 100%;
    }

    .navbar-logo {
        width: auto;
        height: 45px;
        background-color: white;
        border-radius: 4px;
        padding: 2px;
    }

    .hero-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: flex-start;
    }

    .hero-content-wrapper {
        width: 100%;
        padding: 0;
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
    }

    .hero-content {
        max-width: 800px;
        color: #fff;
        text-align: left;
        padding-left: 10%;
        margin-top: -5%;
    }

    .typing-text {
        display: inline;
        min-height: 1.5em;
        color: #fff;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        white-space: nowrap;
    }

    .cursor {
        display: inline-block;
        width: 3px;
        background-color: #fff;
        margin-left: 0;
        animation: blink 1s infinite;
        height: 1em;
        position: relative;
        top: -5px;
    }

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

.hero-title {
    position: relative;
        display: block;
    margin-bottom: 1rem;
        font-size: 3rem;
        white-space: nowrap;
        overflow: visible;
}

.hero-subtitle {
    opacity: 0; 
    visibility: hidden;
    animation: fadeInSubtitle 1s ease-in 1.8s forwards;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
}

@keyframes fadeInSubtitle {
    0% { 
        opacity: 0; 
        visibility: hidden;
        transform: translateY(10px); 
    }
    100% { 
        opacity: 1; 
        visibility: visible;
        transform: translateY(0); 
    }
}

.hero-buttons {
    opacity: 0;
    visibility: hidden;
    animation: fadeInButtons 1s ease-in 2.5s forwards;
}

@keyframes fadeInButtons {
    0% { 
        opacity: 0; 
        visibility: hidden;
        transform: translateY(10px); 
    }
    100% { 
        opacity: 1; 
        visibility: visible;
        transform: translateY(0); 
    }
}


    /* Quiz section styles */
.quiz-card {
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.quiz-card:hover {
    transform: translateY(-5px);
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.6s ease-out, transform 0.6s ease-out;
}

.animate-on-scroll.visible {
    opacity: 1;
    transform: translateY(0);
}

.object-fit-cover {
    object-fit: cover;
}

    /* Add flip card styles */
    .flip-card {
        perspective: 1000px;
        height: 250px;
        cursor: pointer;
    }

    .flip-card-inner {
        position: relative;
        width: 100%;
        height: 100%;
        text-align: center;
        transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        transform-style: preserve-3d;
        box-shadow: none;
    }

    .flip-card:hover .flip-card-inner {
        transform: rotateY(180deg);
    }

    .flip-card-front, .flip-card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .flip-card-front {
        background: linear-gradient(145deg,rgb(41, 56, 81), #334155);
        color: white;
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .flip-card-back {
        background: linear-gradient(145deg,rgb(197, 217, 248),rgb(152, 175, 226));
        color: black;
        transform: rotateY(180deg);
        border-radius: 12px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .flip-card-back p {
        margin-top: 15px;
        font-size: 1.1rem;
    }

    .flip-card-front i {
        color: #3b82f6 !important;
    }

    /* How it works section styles */
    .how-it-works-section {
    /* Modern Dark Mode */
        background-color: #111827;
        color: #fff;
        padding: 4rem 0;
        margin-top: 4rem;
        margin-bottom: 4rem;
        width: 100%;
        overflow: hidden;
    }

    .how-it-works-section h2,
    .how-it-works-section .text-muted {
        color: #fff !important;
    }

    .how-it-works-section .text-muted {
        color: #9ca3af !important;
    }

    .how-it-works-container {
        max-width: 100%;
        padding: 0 30px;
    }

    .how-it-works-row {
        display: flex;
        justify-content: space-between;
        margin: 0 -15px;
    }

    .card-column {
        padding: 0 15px;
    }

    @media (min-width: 992px) {
    .how-it-works-container {
        padding: 0 50px;
    }

    .card-column:first-child .flip-card {
        margin-left: 0;
    }

    .card-column:last-child .flip-card {
        margin-right: 0;
    }
    }

    @media (min-width: 1200px) {
    .how-it-works-container {
        padding: 0 80px;
    }
    }
    /* Dropdown menu styles - Now defined in main.css with !important rules */
    /* Position dropdown menus correctly - Now defined in main.css */
    /* Keep dropdown-menu-end class working properly - Now defined in main.css */
    /* Dropdown item hover effects - Now defined in main.css */

    /* Fullscreen hero styles */
    .fullscreen-hero {
        width: 100%;
        height: 100vh;
        position: relative;
        background: url('<?php echo SITE_URL; ?>/assets/img/city1.jpeg') no-repeat center center;
        background-size: cover;
}

    /* Button styles matching main.css */
    .btn {
        border-radius: 4px;
        padding: 0.5rem 1.5rem;
        transition: all 0.3s ease;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .btn-primary {
        background-color: #000000;
        border-color: #000000;
    }

    .btn-primary:hover {
        background-color: #000000;
        border-color: #000000;
    }

    .btn-outline-primary {
        color: #000000;
        border-color: #000000;
    }

    .btn-outline-primary:hover {
        background-color: #000000;
        color: white;
    }

    /* Navbar button alignment */
    .navbar-nav {
        display: flex;
        align-items: center;
    }
    
    /* Right-aligned nav items (login, register, username) */
    .navbar-nav:last-child {
        margin-left: auto;
    }
    
    /* Consistent padding for nav items */
    .navbar .nav-item {
        padding: 0 5px;
    }

    /* Navbar collapse container */
    .navbar-collapse {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    /* Ensure consistent spacing between nav items */
    .navbar-nav .nav-link {
        margin: 0 2px;
    }
    
    /* Login/Register button spacing */
    .navbar-nav:last-child .nav-item {
        margin-left: 5px;
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .navbar-nav {
            width: 100%;
        }
        
        .navbar-nav:last-child {
            margin-left: 0;
            margin-top: 10px;
        }
        
        .navbar {
            height: auto;
            min-height: 70px;
        }
}
</style>

<?php
// Get latest jobs
$latestJobs = getLatestJobs(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Google Fonts - Roboto Condensed -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/dropdown-fix.css">
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Change navbar background on scroll
        function checkScroll() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
        
        // Listen for scroll events
        window.addEventListener('scroll', checkScroll);
        
        // Force initial check for scrolling position
        checkScroll();
        
        // Animation on scroll
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all elements with animate-on-scroll class
        document.querySelectorAll('.animate-on-scroll').forEach(element => {
            observer.observe(element);
        });
        
        // Typing effect for hero title
        const typingTextEl = document.querySelector('.typing-text');
        if (typingTextEl) {
            const text = "Find Your Dream Job Today";
            const cursor = document.querySelector('.cursor');
            let charIndex = 0;
            
            function type() {
                if (charIndex < text.length) {
                    typingTextEl.textContent += text.charAt(charIndex);
                    charIndex++;
                    setTimeout(type, 100);
                } else {
                    setTimeout(() => {
                        cursor.style.display = 'none';
                    }, 1500);
                }
            }
            
            setTimeout(type, 500);
        }
        
        // Handle flip card animation
        const flipCards = document.querySelectorAll('.flip-card');
        
        flipCards.forEach(card => {
            card.addEventListener('click', () => {
                const inner = card.querySelector('.flip-card-inner');
                inner.style.transform = inner.style.transform === 'rotateY(180deg)' ? '' : 'rotateY(180deg)';
            });
        });
    });
    </script>
    
    <!-- Dropdown Fix JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/dropdown-fix.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL; ?>/assets/img/logo.jpg" alt="<?php echo SITE_NAME; ?>" class="navbar-logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/jobs.php">Browse Jobs</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/culture_quiz.php">Cultural Fit Quiz</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/user/applications.php">My Applications</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/user/dashboard.php">Dashboard</a>
                            </li>
                        <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/company/post_job.php">Post Job</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/company/dashboard.php">Company Dashboard</a>
                            </li>
                        <?php elseif (hasUserType(USER_TYPE_GOWORK)): ?>
                            <?php 
                            // Check if this is a GoWork worker
                            $workerId = getWorkerId($_SESSION['user_id']);
                            if ($workerId): 
                            ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/worker/review_license.php">License Review</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/worker/companies.php">Companies</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/worker/dashboard.php">Worker Dashboard</a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/gowork/license_review.php">License Review</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/gowork/dashboard.php">Dashboard</a>
                                </li>
                            <?php endif; ?>
                        <?php elseif (hasUserType(USER_TYPE_ADMIN)): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php?tab=users">Manage Users and Staff</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/companies.php">Manage Licenses</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Mobile-only user action buttons -->
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item d-lg-none mobile-user-actions">
                        <div class="border-top border-secondary pt-3 mt-3 mb-2"></div>
                        <div class="mobile-user-actions-container">
                            <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/user/profile.php">
                                <i class="fas fa-user-circle"></i> My Profile
                            </a>
                            <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/company/profile.php">
                                <i class="fas fa-building"></i> Company Profile
                            </a>
                            <?php endif; ?>
                            
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/account_settings.php">
                                <i class="fas fa-cog"></i> Account Settings
                            </a>
                            
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/api/auth/logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown d-none d-lg-block">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php 
                                $user = getCurrentUser();
                                echo htmlspecialchars($user['username']);
                                ?> <i class="fas fa-chevron-down ms-1"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/user/profile.php">My Profile</a></li>
                                <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/company/profile.php">Company Profile</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/pages/account_settings.php">Account Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/api/auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container-fluid px-0 fullscreen-hero">
            <div class="hero-image-overlay">
                <div class="hero-content-wrapper">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <span style="white-space: nowrap;">
                                <span class="typing-text"></span><span class="cursor">|</span>
                            </span>
                        </h1>
                        <p class="hero-subtitle">Connect with top employers and discover career opportunities that match your skills and aspirations.</p>
                        <div class="hero-buttons">
                            <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="btn btn-light">Browse Jobs</a>
                            <?php if (!isLoggedIn()): ?>
                                <a href="<?php echo SITE_URL; ?>/pages/register.php" class="btn btn-outline-light ms-2">Sign Up</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cultural Fit Quiz Section -->
    <section class="container-fluid py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card quiz-card animate-on-scroll">
                        <div class="row g-0">
                            <div class="col-md-6">
                                <img src="<?php echo SITE_URL; ?>/assets/img/company.jpg" alt="Cultural Fit" class="img-fluid rounded-start h-100 object-fit-cover" onerror="this.src='<?php echo SITE_URL; ?>/assets/img/city1.jpeg'">
                            </div>
                            <div class="col-md-6">
                                <div class="card-body p-4 p-lg-5">
                                    <h2 class="display-6 mb-3">Find Your Perfect Cultural Fit</h2>
                                    <p class="lead">Take our short cultural fit quiz to discover companies where you'll thrive and feel at home.</p>
                                    <ul class="list-unstyled mb-4">
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Match with companies that share your work style</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Understand your ideal work environment</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Make more informed career decisions</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Find workplaces where you'll belong</li>
                                    </ul>
                                    <a href="<?php echo SITE_URL; ?>/pages/culture_quiz.php" class="btn btn-primary btn-lg">Take the Quiz</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
<section class="how-it-works-section">
    <div class="container">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2>How GoWork Works</h2>
            <p class="text-muted">Simple steps to find your next job or hire qualified candidates</p>
            </div>
        </div>
    </div>
    
    <div class="container-fluid how-it-works-container">
        <div class="row how-it-works-row">
            <div class="col-md-4 card-column">
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h4>Create an Account</h4>
                        </div>
                        <div class="flip-card-back">
                    <h4>Create an Account</h4>
                    <p>Sign up as a job seeker or employer to access all features.</p>
                        </div>
                </div>
            </div>
        </div>
        
            <div class="col-md-4 card-column">
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                    <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h4>Upload Your Resume</h4>
                    <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                        <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                        <h4>Post a Job</h4>
                    <?php else: ?>
                        <i class="fas fa-search fa-3x text-primary mb-3"></i>
                                <h4>Search Jobs or Talent</h4>
                            <?php endif; ?>
                        </div>
                        <div class="flip-card-back">
                            <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                                <h4>Upload Your Resume</h4>
                                <p>Complete your profile and upload your resume to stand out to employers.</p>
                            <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                                <h4>Post a Job</h4>
                                <p>Create a detailed job listing to attract qualified candidates.</p>
                            <?php else: ?>
                        <h4>Search Jobs or Talent</h4>
                        <p>Find the perfect job or candidate using our powerful search tools.</p>
                    <?php endif; ?>
                        </div>
                </div>
            </div>
        </div>
        
            <div class="col-md-4 card-column">
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                    <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                        <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                        <h4>Apply with One Click</h4>
                    <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h4>Review Applications</h4>
                    <?php else: ?>
                        <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                                <h4>Connect and Succeed</h4>
                            <?php endif; ?>
                        </div>
                        <div class="flip-card-back">
                            <?php if (hasUserType(USER_TYPE_JOBSEEKER)): ?>
                                <h4>Apply with One Click</h4>
                                <p>Easily apply to jobs and track your application status.</p>
                            <?php elseif (hasUserType(USER_TYPE_COMPANY)): ?>
                                <h4>Review Applications</h4>
                                <p>Access resumes and contact qualified candidates directly.</p>
                            <?php else: ?>
                        <h4>Connect and Succeed</h4>
                        <p>Start interviews, get hired, or find your ideal employee.</p>
                    <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Bootstrap and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?>
</body>
</html> 