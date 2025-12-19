<?php
session_start();
include('db.php');
include('security.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to delete a booking";
    header('Location: login.php');
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: view_bookings.php');
    exit;
}

// Sanitize booking ID - must be integer
$booking_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

if ($booking_id <= 0) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: view_bookings.php');
    exit;
}

// Use prepared statement to verify ownership and get booking details
if ($is_admin) {
    // Admin can delete any booking
    $stmt = $conn->prepare("SELECT id, status, user_id FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $booking_id);
} else {
    // Regular users can only delete their own bookings
    $stmt = $conn->prepare("SELECT id, status, user_id FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    log_security_event('unauthorized_delete_attempt', "User $user_id attempted to delete booking $booking_id");
    $_SESSION['error'] = "Booking not found or you don't have permission to delete it.";
    header('Location: view_bookings.php');
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Only allow deletion of cancelled or rejected bookings (or any for admin)
$allowed_statuses = ['cancelled', 'rejected'];
$status = strtolower($booking['status']);

if (!$is_admin && !in_array($status, $allowed_statuses)) {
    $_SESSION['error'] = "Only cancelled or rejected bookings can be deleted. Please cancel the booking first.";
    header('Location: view_bookings.php');
    exit;
}

// Perform deletion using prepared statement
try {
    $delete_stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
    $delete_stmt->bind_param("i", $booking_id);
    
    if ($delete_stmt->execute()) {
        log_security_event('booking_deleted', "Booking $booking_id deleted by user $user_id");
        $_SESSION['success'] = "Booking deleted successfully.";
    } else {
        throw new Exception("Failed to delete booking.");
    }
    
    $delete_stmt->close();
    
} catch (Exception $e) {
    log_security_event('delete_error', "Error deleting booking $booking_id: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while deleting the booking. Please try again.";
}

header('Location: view_bookings.php');
exit;
?>
