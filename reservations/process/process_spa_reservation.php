<?php
session_start();
include('db.php');
include('security.php');

// Require login to make a spa reservation
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'spa.php';
    $_SESSION['error'] = 'Please login to make a spa reservation.';
    header('Location: login.php');
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request.";
    header('Location: spa.php#spa-reserve');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('csrf_token_failure', 'Spa reservation attempt with invalid CSRF token');
    $_SESSION['error'] = "Security verification failed. Please try again.";
    header('Location: spa.php#spa-reserve');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Sanitize and validate input
$customer_name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$treatment = trim($_POST['treatment'] ?? '');
$spa_date = trim($_POST['date'] ?? '');
$spa_time = trim($_POST['time'] ?? '');
$guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
$special_requests = trim($_POST['special_requests'] ?? '');

// Validation
$errors = [];

if (empty($customer_name)) {
    $errors[] = "Full name is required.";
} elseif (strlen($customer_name) > 100) {
    $errors[] = "Name must be less than 100 characters.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required.";
}

if (!empty($phone) && strlen($phone) > 20) {
    $errors[] = "Phone number is too long.";
}

if (empty($treatment)) {
    $errors[] = "Please select a treatment.";
} elseif (strlen($treatment) > 100) {
    $errors[] = "Treatment name is too long.";
}

if (empty($spa_date)) {
    $errors[] = "Date is required.";
}

if (empty($spa_time)) {
    $errors[] = "Time is required.";
}

if ($guests < 1 || $guests > 10) {
    $errors[] = "Guests must be between 1 and 10.";
}

// Date cannot be in the past
if (!empty($spa_date)) {
    $today = date('Y-m-d');
    if ($spa_date < $today) {
        $errors[] = "Date cannot be in the past.";
    }
}

// Validate special requests length
if (strlen($special_requests) > 500) {
    $errors[] = "Special requests must be less than 500 characters.";
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header('Location: spa.php#spa-reserve');
    exit;
}

// Validate treatment against known treatments (optional whitelist)
$valid_treatments = [
    'Swedish Massage', 'Deep Tissue Massage', 'Hot Stone Therapy', 'Aromatherapy Massage',
    'Hydrating Facial', 'Anti-Aging Treatment', 'Brightening Facial', 'Deep Cleanse',
    'Relaxation Package', 'Renewal Package', 'Couples Retreat'
];

// Allow custom treatments but log if not in expected list
if (!in_array($treatment, $valid_treatments)) {
    log_security_event('custom_treatment', "User $user_id requested non-standard treatment: $treatment");
}

try {
    // Insert using prepared statement
    $stmt = $conn->prepare("INSERT INTO spa_bookings 
                           (user_id, customer_name, email, phone, treatment, spa_date, spa_time, guests, special_requests, status)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    
    $phone_value = !empty($phone) ? $phone : null;
    $special_requests_value = !empty($special_requests) ? $special_requests : null;
    
    $stmt->bind_param("issssssss", $user_id, $customer_name, $email, $phone_value, $treatment, $spa_date, $spa_time, $guests, $special_requests_value);
    
    if (!$stmt->execute()) {
        throw new Exception("DB insert failed.");
    }

    $booking_id = $conn->insert_id;
    $stmt->close();

    log_security_event('spa_booking_created', "Spa booking $booking_id created by user $user_id");

    $_SESSION['success'] = "Spa booking received for " . htmlspecialchars($treatment) . " on " .
        date('F j, Y', strtotime($spa_date)) . " at " . date('g:i A', strtotime($spa_time)) .
        " for $guests guest(s). We'll confirm shortly.";
    
    header('Location: spa_confirmation.php');
    exit;
    
} catch (Exception $e) {
    log_security_event('spa_booking_error', "Spa booking failed for user $user_id: " . $e->getMessage());
    $_SESSION['error'] = "Could not complete your booking. Please try again.";
    header('Location: spa.php#spa-reserve');
    exit;
}
