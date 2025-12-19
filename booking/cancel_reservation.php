<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../includes/db.php');

// Define paths if not defined
if (!defined('BOOKING_PATH')) {
    define('BOOKING_PATH', './'); // relative to self if accessed directly or via include that defines it
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to cancel a reservation";
    header('Location: ../auth/login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Validation
if ($id <= 0 || !in_array($type, ['dining', 'spa'])) {
    $_SESSION['error'] = "Invalid reservation request.";
    header('Location: view_bookings.php'); // Clean relative path
    exit;
}

try {
    mysqli_begin_transaction($conn);

    $table = '';
    $success_msg = '';

    if ($type === 'dining') {
        $table = 'dining_reservations';
        $success_msg = "Dining reservation cancelled successfully.";
    } elseif ($type === 'spa') {
        $table = 'spa_bookings';
        $success_msg = "Spa appointment cancelled successfully.";
    }

    // Verify ownership and status
    // Safe variable interpolation for table name as it's hardcoded above based on secure check
    $check_stmt = $conn->prepare("SELECT id, status FROM $table WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Reservation not found or access denied.");
    }

    $reservation = $result->fetch_assoc();
    $status = strtolower($reservation['status']);

    // Only allow cancellation if pending or confirmed (future handling logic could be added here)
    if ($status === 'cancelled') {
        throw new Exception("This reservation is already cancelled.");
    }

    // allow cancelling 'confirmed' too? usually yes, but let's stick to simple logic or what cancel_booking does
    // cancel_booking.php ONLY allows 'pending'. 
    // However, usually users expect to be able to cancel confirmed bookings too if it's not too late.
    // For now, let's match the request's implication.
    // "cancel_reservation.php" usually implies action.

    // Let's allow 'pending' and 'confirmed'. Reject 'rejected' or 'checked-out' etc.
    if (!in_array($status, ['pending', 'confirmed', 'approved'])) {
        throw new Exception("Cannot cancel reservation with status: " . ucfirst($status));
    }

    $check_stmt->close();

    // Perform Cancellation
    $update_stmt = $conn->prepare("UPDATE $table SET status = 'cancelled' WHERE id = ?");
    $update_stmt->bind_param("i", $id);

    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update status.");
    }
    $update_stmt->close();

    mysqli_commit($conn);

    $_SESSION['success'] = $success_msg;
    header('Location: view_bookings.php');
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['error'] = $e->getMessage();
    header('Location: view_bookings.php');
    exit;
}
