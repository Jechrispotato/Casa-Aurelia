<?php
/**
 * Path Configuration
 * This file provides centralized path management for the reorganized folder structure.
 * Include this file to get proper paths relative to the current file's location.
 */

// Determine the base path from the current script
$script_path = $_SERVER['SCRIPT_NAME'];
$path_parts = explode('/', trim($script_path, '/'));

// Find Casa-Aurelia in the path and calculate depth
$casa_index = array_search('Casa-Aurelia', $path_parts);
$depth_from_root = count($path_parts) - $casa_index - 2; // -2 for Casa-Aurelia and the file itself

// Generate the prefix to get back to root
$ROOT = str_repeat('../', $depth_from_root);
if ($ROOT === '') $ROOT = './';

// Define all paths relative to root
define('SITE_ROOT', $ROOT);
define('INCLUDES_PATH', $ROOT . 'includes/');
define('ASSETS_PATH', $ROOT . 'assets/');
define('IMAGES_PATH', $ROOT . 'images/');
define('DIST_PATH', $ROOT . 'dist/');

// Page paths
define('AUTH_PATH', $ROOT . 'auth/');
define('BOOKING_PATH', $ROOT . 'booking/');
define('PAGES_PATH', $ROOT . 'pages/');
define('PROFILE_PATH', $ROOT . 'profile/');
define('RESERVATIONS_PATH', $ROOT . 'reservations/');
define('API_PATH', $ROOT . 'api/');
define('ADMIN_PATH', $ROOT . 'admin/');

// Legacy asset paths (for backward compatibility)
define('AURELIA_ASSETS', $ROOT . 'aurelia_assets/');

/**
 * Helper function to get a path relative to site root
 * @param string $path The path from root (e.g., 'booking/add_booking.php')
 * @return string The properly prefixed path
 */
function site_url($path = '') {
    return SITE_ROOT . ltrim($path, '/');
}

/**
 * Helper function to check current page context
 */
function get_current_section() {
    $path = $_SERVER['PHP_SELF'];
    if (strpos($path, '/admin/') !== false) return 'admin';
    if (strpos($path, '/auth/') !== false) return 'auth';
    if (strpos($path, '/booking/') !== false) return 'booking';
    if (strpos($path, '/pages/') !== false) return 'pages';
    if (strpos($path, '/profile/') !== false) return 'profile';
    if (strpos($path, '/reservations/') !== false) return 'reservations';
    if (strpos($path, '/api/') !== false) return 'api';
    return 'root';
}
?>
