<?php
$pageTitle = 'Page Not Found';
$root_path = $_SERVER['DOCUMENT_ROOT'] . '/GoWork/';
require_once $root_path . 'includes/config.php';
require_once $root_path . 'includes/functions.php';

// Include header
require_once $root_path . 'includes/header.php';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page py-5">
                <h1 class="display-1 text-primary">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                <div class="mt-4">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary">Go Home</a>
                    <a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="btn btn-outline-primary ms-2">Browse Jobs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once $root_path . 'includes/footer.php';
?>
