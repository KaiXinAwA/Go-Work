<?php
/**
 * Initialization file that includes all required files
 */

// Include core files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Include custom functions
if (file_exists(__DIR__ . '/functions_culture.php')) {
    require_once __DIR__ . '/functions_culture.php';
} 