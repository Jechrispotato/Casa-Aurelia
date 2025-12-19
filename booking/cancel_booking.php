<?php
session_start();
include('db.php');
include('security.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to cancel a booking";
    header('Location: login.php');
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: view_bookings.php');
    exit;
}

$booking_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: view_bookings.php');
    exit;
}

// Verify that this booking belongs to the user using prepared statement
$stmt = $conn->prepare("SELECT id, status, check_in_date FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    log_security_event('unauthorized_cancel_attempt', "User $user_id attempted to cancel booking $booking_id");
    $_SESSION['error'] = "Booking not found or you don't have permission to cancel it.";
    header('Location: view_bookings.php');
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Check if booking is in pending status
$booking_status = strtolower($booking['status']);
if ($booking_status !== 'pending') {
    $_SESSION['error'] = "Only pending bookings can be cancelled.";
    header('Location: view_bookings.php');
    exit;
}

// Check if check-in date is in the future
$today = date('Y-m-d');
if (!empty($booking['check_in_date']) && date('Y-m-d', strtotime($booking['check_in_date'])) <= $today) {
    $_SESSION['error'] = "Cannot cancel bookings on or after the check-in date.";
    header('Location: view_bookings.php');
    exit;
}

// All checks passed, cancel the booking
try {
    mysqli_begin_transaction($conn);
    
    // Set status to 'cancelled' using prepared statement
    $cancel_stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $cancel_stmt->bind_param("i", $booking_id);
    
    if (!$cancel_stmt->execute()) {
        throw new Exception("Failed to cancel booking.");
    }
    
    $cancel_stmt->close();
    
    mysqli_commit($conn);
    
    log_security_event('booking_cancelled', "Booking $booking_id cancelled by user $user_id");
    
    $_SESSION['success'] = "Your booking has been successfully cancelled.";
    header('Location: view_bookings.php');
    exit;
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    log_security_event('cancel_error', "Error cancelling booking $booking_id: " . $e->getMessage());
    
    $_SESSION['error'] = "An error occurred while cancelling your booking. Please try again.";
    header('Location: view_bookings.php');
    exit;
}
?>