<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Unauthorized');
}

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int) $_POST['room_id'];
    $force = isset($_POST['force']) && $_POST['force'] === 'true';

    // First check if room exists
    $room_check = mysqli_query($conn, "SELECT id, room_name FROM rooms WHERE id = $room_id");
    if (!$room_check || mysqli_num_rows($room_check) === 0) {
        http_response_code(404);
        exit('Room not found');
    }

    // Check if room has any ACTIVE bookings (pending or approved, not yet checked out)
    $check_active_query = "SELECT COUNT(*) as count FROM bookings 
                           WHERE room_id = $room_id 
                           AND status IN ('pending', 'approved')
                           AND check_out_date >= CURDATE()";
    $result = mysqli_query($conn, $check_active_query);
    $active_count = $result ? mysqli_fetch_assoc($result)['count'] : 0;

    if ($active_count > 0 && !$force) {
        http_response_code(400);
        exit("Cannot delete room: It has $active_count active booking(s). Please cancel or complete these bookings first.");
    }

    // Delete all bookings for this room (cleanup)
    $cleanup_query = "DELETE FROM bookings WHERE room_id = $room_id";
    if (!mysqli_query($conn, $cleanup_query)) {
        // If cleanup fails, try to continue anyway
        error_log("Warning: Could not cleanup bookings for room $room_id: " . mysqli_error($conn));
    }

    // Also remove any room likes/reviews if they exist
    mysqli_query($conn, "DELETE FROM room_likes WHERE room_id = $room_id");
    mysqli_query($conn, "DELETE FROM room_reviews WHERE room_id = $room_id");

    // Delete room
    $query = "DELETE FROM rooms WHERE id = $room_id";

    if (mysqli_query($conn, $query)) {
        if (mysqli_affected_rows($conn) > 0) {
            http_response_code(200);
            exit('Room deleted successfully');
        } else {
            http_response_code(404);
            exit('Room not found or already deleted');
        }
    } else {
        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    http_response_code(405);
    exit('Method not allowed');
}
