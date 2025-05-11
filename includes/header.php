<?php
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/functions.php';
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
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
    
    <style>
    .navbar {
        background-color: transparent !important;
        position: fixed;
        width: 100%;
        z-index: 1000;
        padding: 1rem 0;
        transition: background-color 0.3s ease;
        height: 70px;
        display: flex;
        align-items: center;
    }
    
    .navbar.scrolled {
        background-color: rgb(0, 0, 0) !important;
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
    
    /* Fix for mobile */
    @media (max-width: 991.98px) {
        .navbar-nav .dropdown-menu {
            position: static !important;
            float: none;
            width: auto;
            max-width: 100%;
            margin-top: 0;
            background-color: #212529;
            border: 0;
        }
    }
    
    .navbar-logo {
        width: auto;
        height: 45px;
        background-color: white;
        border-radius: 4px;
        padding: 2px;
    }
    
    /* Adjust content container for fixed navbar */
    .content-container {
        padding-top: 50px;
    }
    
    /* Only add padding when not on homepage */
    body:not(.is-homepage) .content-container {
        padding-top: 40px;
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

    /* Mobile fixes for various elements - dropdown fixes now in main.css */
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
    
    /* Application status styles */
    .status-withdrawn {
        background-color:#6c757d;
        color:rgb(255, 255, 255); /* Gray color for withdrawn status */
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if current page is homepage
        const isHomepage = window.location.pathname === '/' || 
                          window.location.pathname === '/index.php';
        
        if (isHomepage) {
            document.body.classList.add('is-homepage');
            
            // Change navbar background on scroll
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        } else {
            // Add scrolled class permanently for non-homepage
            document.querySelector('.navbar').classList.add('scrolled');
        }
    });
    </script>
    
    <!-- Bootstrap JavaScript - needed for dropdowns to work -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Define flag to indicate Bootstrap has been loaded -->
    <script>
        window.BOOTSTRAP_LOADED = true;
        <?php define('BOOTSTRAP_LOADED', true); ?>
    </script>
    
    <!-- Dropdown Fix JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/dropdown-fix.js"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container position-relative">
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
                <ul class="navbar-nav">
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

    <!-- Main Content Container -->
    <div class="<?php echo (isset($pageTitle) && $pageTitle == 'Home') ? '' : 'container content-container my-4'; ?>">
        <?php
        if (isset($_SESSION['error'])) {
            echo displayError($_SESSION['error']);
            unset($_SESSION['error']);
        }
        
        if (isset($_SESSION['success'])) {
            echo displaySuccess($_SESSION['success']);
            unset($_SESSION['success']);
        }
        ?>
    </div>
</body>
</html>
