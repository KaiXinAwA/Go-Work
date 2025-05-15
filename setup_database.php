<?php
/**
 * Database setup script for GoWork
 */

// Database configuration - same as in config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connect to MySQL server (without database)
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>GoWork Database Setup</h2>";

// Read SQL from file
$sql_file = file_get_contents(__DIR__ . '/db/gowork_db.sql');

// Split SQL file into individual statements
$statements = explode(';', $sql_file);

// Execute each statement
foreach ($statements as $statement) {
    $statement = trim($statement);
    
    if (!empty($statement)) {
        if ($conn->query($statement) === TRUE) {
            echo "Executed: " . substr($statement, 0, 50) . "...<br>";
        } else {
            echo "Error executing statement: " . $conn->error . "<br>";
            echo "Statement: " . $statement . "<br>";
        }
    }
}

echo "<h3>Database setup completed!</h3>";
echo "<p>You can now access your site at <a href='http://localhost'>http://localhost</a></p>";

// Test for other potential issues
echo "<h3>Testing for common issues:</h3>";

// Check if uploads directory is writable
echo "<p>Checking if uploads directory is writable: ";
if (is_writable(__DIR__ . '/uploads')) {
    echo "<span style='color:green'>Yes</span></p>";
} else {
    echo "<span style='color:red'>No</span> - Please set write permissions manually</p>";
}

// Create a diagnostic file
echo "<p>Attempting to create a test file in uploads directory: ";
$test_file = __DIR__ . '/uploads/test_write.txt';
if (file_put_contents($test_file, 'Test write access')) {
    echo "<span style='color:green'>Success</span></p>";
    unlink($test_file); // Clean up
} else {
    echo "<span style='color:red'>Failed</span> - Please check file permissions</p>";
}

// Check database connection with the new database
echo "<p>Testing database connection to gowork_db: ";
try {
    $db_conn = new mysqli(DB_HOST, DB_USER, DB_PASS, 'gowork_db');
    if ($db_conn->connect_error) {
        throw new Exception($db_conn->connect_error);
    }
    echo "<span style='color:green'>Success</span></p>";
    $db_conn->close();
} catch (Exception $e) {
    echo "<span style='color:red'>Failed</span> - " . $e->getMessage() . "</p>";
}

// PHP Configuration
echo "<h3>PHP Configuration:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "</p>";
echo "<p>File Uploads Enabled: " . (ini_get('file_uploads') ? 'Yes' : 'No') . "</p>";
echo "<p>Max Upload Size: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>Max Post Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Current Script: " . __FILE__ . "</p>";

// Create upload directories if they don't exist
$uploadDirs = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/resumes',
    __DIR__ . '/uploads/licenses',
    __DIR__ . '/uploads/profile_pictures'
];

echo "<h3>Creating upload directories:</h3>";

foreach ($uploadDirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "Created directory: $dir<br>";
        } else {
            echo "Failed to create directory: $dir<br>";
        }
    } else {
        echo "Directory already exists: $dir<br>";
    }
    
    // Set permissions either way
    if (!chmod($dir, 0777)) {
        echo "Warning: Could not change permissions on $dir<br>";
    } else {
        echo "Set permissions on $dir<br>";
    }
}

$conn->close();
?>
