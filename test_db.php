<?php
require_once 'includes/config.php';
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
} else {
    echo "Connection successful!";
    $mysqli->close();
}
?>