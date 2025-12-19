<?php
/**
 * Security Headers
 * Include this file at the very beginning of your pages to set security headers
 */

// Prevent clickjacking attacks
header('X-Frame-Options: SAMEORIGIN');

// Prevent MIME type sniffing
header('X-Content-Type-Options: nosniff');

// Enable XSS filter in browsers
header('X-XSS-Protection: 1; mode=block');

// Referrer policy - only send referrer for same-origin requests
header('Referrer-Policy: strict-origin-when-cross-origin');

// Permissions policy - disable unused browser features
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Content Security Policy - adjust as needed for your site
// Note: This is a moderate policy. Tighten as needed for production.
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://js.stripe.com; ";
$csp .= "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
$csp .= "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:; ";
$csp .= "img-src 'self' data: https: blob:; ";
$csp .= "connect-src 'self' https://api.stripe.com; ";
$csp .= "frame-src 'self' https://js.stripe.com https://hooks.stripe.com; ";
$csp .= "object-src 'none'; ";
$csp .= "base-uri 'self'; ";
$csp .= "form-action 'self';";

header("Content-Security-Policy: $csp");

// Set secure cookie flags for session
if (session_status() === PHP_SESSION_NONE) {
    // Set secure session cookie parameters before starting session
    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $httponly = true;
    $samesite = 'Lax';
    
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=' . $samesite, '', $secure, $httponly);
    }
}

/**
 * Function to regenerate session ID periodically to prevent session fixation
 * Call this after successful login
 */
function secure_session_regenerate() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/**
 * Function to destroy session securely
 * Call this on logout
 */
function secure_session_destroy() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        // Unset all session variables
        $_SESSION = array();
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }
}
?>
