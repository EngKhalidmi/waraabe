<?php
/**
 * Front Controller
 *
 * This script serves as the front controller for the Laravel application.
 * It redirects all requests to the public directory and updates the URL.
 */

// Get the requested URL
$requestUri = $_SERVER['REQUEST_URI'];

// Check if the URL already contains '/public'
if (strpos($requestUri, '/public') === false) {
    // Redirect to the URL with '/public' added
    header('Location: /public' . $requestUri);
    exit;
}

// If the URL already contains '/public', include the Laravel public/index.php file
require __DIR__ . '/public/index.php';
