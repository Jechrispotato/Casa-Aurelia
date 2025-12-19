<?php
session_start();
include('db.php');
include('security.php');

// Require login to make a dining reservation
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'dining.php';
    $_SESSION['error'] = 'Please login to make a dining reservation.';
    header('Location: login.php');
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: dining.php#reservation');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('csrf_token_failure', 'Dining reservation attempt with invalid CSRF token');
    $_SESSION['error'] = "Security verification failed. Please try again.";
    header('Location: dining.php#reservation');
    exit;
}

// Get user_id
$user_id = (int)$_SESSION['user_id'];

// Sanitize and validate input
$customer_name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$reservation_date = trim($_POST['date'] ?? '');
$reservation_time = trim($_POST['time'] ?? '');
$number_of_guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 0;
$venue = trim($_POST['venue'] ?? '');
$special_requests = trim($_POST['special-requests'] ?? '');

// Validate required fields
$errors = [];

if (empty($customer_name)) {
    $errors[] = "Full name is required.";
} elseif (strlen($customer_name) > 100) {
    $errors[] = "Name must be less than 100 characters.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email address is required.";
}

if (empty($reservation_date)) {
    $errors[] = "Reservation date is required.";
}

if (empty($reservation_time)) {
    $errors[] = "Reservation time is required.";
}

if ($number_of_guests < 1 || $number_of_guests > 20) {
    $errors[] = "Number of guests must be between 1 and 20. For larger groups, please contact us directly.";
}

if (empty($venue)) {
    $errors[] = "Please select a venue.";
}

// Validate date is not in the past
if (!empty($reservation_date)) {
    $today = date('Y-m-d');
    if ($reservation_date < $today) {
        $errors[] = "Reservation date cannot be in the past.";
    }
}

// If there are validation errors, return them
if (!empty($errors)) {
    $_SESSION['error'] = implode(" ", $errors);
    header('Location: dining.php#reservation');
    exit;
}

// Validate venue against whitelist
$valid_venues = ['restaurant', 'lounge', 'grand_room', 'wine_cellar'];
if (!in_array($venue, $valid_venues)) {
    log_security_event('invalid_venue_attempt', "User $user_id attempted invalid venue: $venue");
    $_SESSION['error'] = "Invalid venue selected.";
    header('Location: dining.php#reservation');
    exit;
}

// Validate special requests length
if (strlen($special_requests) > 500) {
    $_SESSION['error'] = "Special requests must be less than 500 characters.";
    header('Location: dining.php#reservation');
    exit;
}

try {
    // Insert using prepared statement
    $stmt = $conn->prepare("INSERT INTO dining_reservations 
                           (user_id, customer_name, email, reservation_date, reservation_time, number_of_guests, venue, special_requests, status) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    
    $special_requests_value = !empty($special_requests) ? $special_requests : null;
    $stmt->bind_param("issssiss", $user_id, $customer_name, $email, $reservation_date, $reservation_time, $number_of_guests, $venue, $special_requests_value);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create reservation.");
    }

    $reservation_id = $conn->insert_id;
    $stmt->close();

    log_security_event('dining_reservation_created', "Reservation $reservation_id created by user $user_id");

    // Format venue name for display
    $venue_names = [
        'restaurant' => 'The Aurelia Restaurant',
        'lounge' => 'The Skyline Lounge',
        'grand_room' => 'The Grand Room',
        'wine_cellar' => 'The Wine Cellar'
    ];
    $venue_display = isset($venue_names[$venue]) ? $venue_names[$venue] : $venue;

    // Format date and time for display
    $formatted_date = date('F j, Y', strtotime($reservation_date));
    $formatted_time = date('g:i A', strtotime($reservation_time));

    // Set success message
    $_SESSION['success'] = "Your reservation request has been submitted successfully! " .
        "We have received your request for " . $number_of_guests . " guest(s) at " .
        htmlspecialchars($venue_display) . " on " . $formatted_date . " at " . $formatted_time .
        ". Our team will confirm your reservation shortly.";

    header('Location: dining_confirmation.php');
    exit;
    
} catch (Exception $e) {
    log_security_event('dining_reservation_error', "Reservation failed for user $user_id: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing your reservation. Please try again.";
    header('Location: dining.php#reservation');
    exit;
}
