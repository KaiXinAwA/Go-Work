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
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/company/applications.php">View Applications</a>
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
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php?tab=users">Manage Users</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/users_combined.php?tab=staff">Manage Staff</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/companies.php">Manage Licenses</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php">Admin Dashboard</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php
                                $user = getCurrentUser();
                                echo htmlspecialchars($user['username']);
                                ?>
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
    <div class="container my-4">
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
