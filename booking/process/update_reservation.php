<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view_bookings.php');
    exit;
}

include('../../includes/db.php');

$user_id = $_SESSION['user_id'];
$id = (int) $_POST['id'];
$type = $_POST['type'];

// Prevent empty ID
if ($id <= 0) {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ../view_bookings.php');
    exit;
}

if ($type === 'dining') {
    $venue = $_POST['venue'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = (int) $_POST['guests'];
    $special = $_POST['special_requests'];

    // Update query
    $stmt = $conn->prepare("UPDATE dining_reservations SET 
                            venue = ?, reservation_date = ?, reservation_time = ?, number_of_guests = ?, special_requests = ? 
                            WHERE id = ? AND user_id = ? AND status = 'pending'");

    if ($stmt) {
        $stmt->bind_param("sssisii", $venue, $date, $time, $guests, $special, $id, $user_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['success'] = 'Dining reservation updated successfully.';
        } elseif ($stmt->affected_rows === 0) {
            // Check if it failed because ID was wrong or because nothing changed
            $_SESSION['success'] = 'No changes made or reservation is not pending.';
        } else {
            $_SESSION['error'] = 'Failed to update reservation.';
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Database error.';
    }

} elseif ($type === 'spa') {
    $treatment = $_POST['treatment'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = (int) $_POST['guests'];
    $special = $_POST['special_requests'];

    // Update query
    $stmt = $conn->prepare("UPDATE spa_bookings SET 
                            treatment = ?, spa_date = ?, spa_time = ?, guests = ?, special_requests = ? 
                            WHERE id = ? AND user_id = ? AND status = 'pending'");

    if ($stmt) {
        $stmt->bind_param("sssisii", $treatment, $date, $time, $guests, $special, $id, $user_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $_SESSION['success'] = 'Spa appointment updated successfully.';
        } elseif ($stmt->affected_rows === 0) {
            $_SESSION['success'] = 'No changes made or reservation is not pending.';
        } else {
            $_SESSION['error'] = 'Failed to update reservation.';
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'Database error.';
    }
} else {
    $_SESSION['error'] = 'Invalid reservation type.';
}

header('Location: ../view_bookings.php');
exit;
