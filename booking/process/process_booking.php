<?php
session_start();
include('../../includes/db.php');
include('../../includes/security.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'booking/add_booking.php';
    $_SESSION['error'] = "Please login to make a booking";
    header('Location: ../../auth/login.php');
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header('Location: ../add_booking.php');
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('csrf_token_failure', 'Booking attempt with invalid CSRF token');
    $_SESSION['error'] = "Security verification failed. Please try again.";
    header('Location: ../add_booking.php');
    exit;
}

// Validate required fields
if (
    !isset($_POST['customer_name']) || !isset($_POST['room_id']) ||
    !isset($_POST['check_in_date']) || !isset($_POST['check_out_date'])
) {
    $_SESSION['error'] = "All fields are required.";
    header('Location: ../add_booking.php');
    exit;
}

// Sanitize and validate input
$customer_name = trim($_POST['customer_name']);
$room_id = (int) $_POST['room_id'];
$check_in_date = trim($_POST['check_in_date']);
$check_out_date = trim($_POST['check_out_date']);
$user_id = (int) $_SESSION['user_id'];

// Validate customer name
if (empty($customer_name) || strlen($customer_name) > 100) {
    $_SESSION['error'] = "Please enter a valid name (max 100 characters).";
    header('Location: ../add_booking.php');
    exit;
}

// Validate room ID
if ($room_id <= 0) {
    $_SESSION['error'] = "Please select a valid room.";
    header('Location: ../add_booking.php');
    exit;
}

// Validate date format
$check_in_timestamp = strtotime($check_in_date);
$check_out_timestamp = strtotime($check_out_date);

if (!$check_in_timestamp || !$check_out_timestamp) {
    $_SESSION['error'] = "Invalid date format.";
    header('Location: ../add_booking.php');
    exit;
}

// Validate dates
$now = time();
if ($check_in_timestamp < $now - 300) { // 5 minute grace period
    $_SESSION['error'] = "Cannot check in at a time in the past.";
    header('Location: ../add_booking.php');
    exit;
}

if ($check_out_timestamp <= $check_in_timestamp) {
    $_SESSION['error'] = "Check-out date must be after check-in date.";
    header('Location: ../add_booking.php');
    exit;
}

// Format dates for database
$check_in_date = date('Y-m-d H:i:s', $check_in_timestamp);
$check_out_date = date('Y-m-d H:i:s', $check_out_timestamp);

// Check if room exists using prepared statement
$room_stmt = $conn->prepare("SELECT id, room_name, price, price_per_hour FROM rooms WHERE id = ?");
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();

if ($room_result->num_rows === 0) {
    log_security_event('invalid_room_booking', "User $user_id attempted to book non-existent room $room_id");
    $_SESSION['error'] = "Invalid room selection.";
    header('Location: ../add_booking.php');
    exit;
}

$room = $room_result->fetch_assoc();
$room_stmt->close();

// Check room availability using prepared statement
$avail_stmt = $conn->prepare("SELECT id FROM bookings 
                              WHERE room_id = ? 
                              AND status IN ('approved', 'pending')
                              AND (? < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) 
                                   AND DATE_ADD(?, INTERVAL 20 MINUTE) > check_in_date)");
$avail_stmt->bind_param("iss", $room_id, $check_in_date, $check_out_date);
$avail_stmt->execute();
$avail_result = $avail_stmt->get_result();

if ($avail_result->num_rows > 0) {
    $_SESSION['error'] = "Sorry, this room is already booked for some or all of the selected dates. Please choose another date range or room.";
    header('Location: ../add_booking.php');
    exit;
}
$avail_stmt->close();

// Room is available - proceed with booking
try {
    mysqli_begin_transaction($conn);

    // Calculate price
    $diff_seconds = $check_out_timestamp - $check_in_timestamp;
    $diff_hours = $diff_seconds / 3600;

    $hourly_price = isset($room['price_per_hour']) && $room['price_per_hour'] > 0
        ? $room['price_per_hour']
        : ceil($room['price'] * 0.15);

    if ($diff_hours < 24) {
        $hours = max(1, ceil($diff_hours));
        $total_price = $hours * $hourly_price;
        $nights = 0;
    } else {
        $nights = floor($diff_hours / 24);
        $remaining_hours = ceil(fmod($diff_hours, 24));

        if ($remaining_hours > 0) {
            $night_price = $nights * $room['price'];
            $overage_price = $remaining_hours * $hourly_price;

            if ($overage_price >= $room['price']) {
                $total_price = ($nights + 1) * $room['price'];
                $nights = $nights + 1;
            } else {
                $total_price = $night_price + $overage_price;
            }
        } else {
            $nights = max(1, $nights);
            $total_price = $nights * $room['price'];
        }
    }

    // Insert booking using prepared statement
    $insert_stmt = $conn->prepare("INSERT INTO bookings (user_id, room_id, customer_name, check_in_date, check_out_date, total_price, status, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $insert_stmt->bind_param("iisssd", $user_id, $room_id, $customer_name, $check_in_date, $check_out_date, $total_price);

    if (!$insert_stmt->execute()) {
        throw new Exception("Failed to create booking.");
    }

    $booking_id = $conn->insert_id;
    $insert_stmt->close();

    mysqli_commit($conn);

    log_security_event('booking_created', "Booking $booking_id created by user $user_id for room $room_id");

    // Set success message
    $duration_text = $nights > 0 ? "$nights night" . ($nights > 1 ? 's' : '') : round($diff_hours, 1) . " hours";
    $_SESSION['success'] = "Booking request submitted! You have requested the "
        . htmlspecialchars($room['room_name'])
        . " from " . date('F j, Y g:i A', $check_in_timestamp)
        . " to " . date('F j, Y g:i A', $check_out_timestamp)
        . " ($duration_text). Your booking will be reviewed by our staff.";

    header('Location: ../booking_confirmation.php');
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    log_security_event('booking_error', "Booking failed for user $user_id: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing your booking. Please try again.";
    header('Location: ../add_booking.php');
    exit;
}
?>