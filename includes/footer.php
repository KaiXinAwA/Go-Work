    </div>
    <!-- End Main Content Container -->

    <!-- Floating Help Centre Button -->
    <a href="<?php echo SITE_URL; ?>/pages/help_center.php" class="help-centre-btn">
        <i class="fas fa-question-circle me-2"></i> Help Centre
    </a>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p>Finding the right job or the perfect candidate has never been easier.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-white">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/jobs.php" class="text-white">Browse Jobs</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/help_center.php" class="text-white">Help Centre</a></li>
                        <?php if (!isLoggedIn()): ?>
                            <li><a href="<?php echo SITE_URL; ?>/pages/login.php" class="text-white">Login</a></li>
                            <li><a href="<?php echo SITE_URL; ?>/pages/register.php" class="text-white">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <address>
                        <i class="fas fa-map-marker-alt"></i>  1, Jalan University, 96000 Sibu, Sarawak<br>
                        <i class="fas fa-phone"></i> 084-367 300<br>
                        <i class="fas fa-envelope"></i> servicegowork@gmail.com
                    </address>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- jQuery already included in header -->
    <?php if (!defined('BOOTSTRAP_LOADED')): ?>
    <!-- Bootstrap JavaScript already loaded in header, this is just a fallback -->
    <script>
        if (typeof bootstrap === 'undefined') {
            console.log('Bootstrap not loaded in header, loading as fallback');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js';
            document.body.appendChild(script);
        }
    </script>
    <?php endif; ?>
    
    <!-- Pass PHP variables to JavaScript -->
    <script>
        // Global variables
        const siteUrl = '<?php echo SITE_URL; ?>';
    </script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <?php if (isset($extraJS)) echo $extraJS; ?>

    <!-- Add CSS for the floating help button -->
    <style>
        .help-centre-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border-radius: 50px;
            padding: 12px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .help-centre-btn:hover {
            background-color: #0056b3;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        }
        
        .help-centre-btn i {
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .help-centre-btn {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
        }
    </style>
</body>
</html>
