<?php
include('../includes/db.php');

header('Content-Type: application/json');

// Check if required parameters are set
if (!isset($_POST['room_id']) || !isset($_POST['check_in_date']) || !isset($_POST['check_out_date'])) {
    echo json_encode(['available' => false, 'error' => 'Missing required parameters']);
    exit;
}

// Sanitize inputs
$room_id = (int)$_POST['room_id'];
$check_in_date = trim($_POST['check_in_date']);
$check_out_date = trim($_POST['check_out_date']);
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

// Validate room_id
if ($room_id <= 0) {
    echo json_encode(['available' => false, 'error' => 'Invalid room ID']);
    exit;
}

// Validate date format (basic check)
$check_in_timestamp = strtotime($check_in_date);
$check_out_timestamp = strtotime($check_out_date);

if (!$check_in_timestamp || !$check_out_timestamp) {
    echo json_encode(['available' => false, 'error' => 'Invalid date format']);
    exit;
}

// Validate dates
if ($check_in_timestamp >= $check_out_timestamp) {
    echo json_encode(['available' => false, 'error' => 'Check-out date must be after check-in date']);
    exit;
}

// Format dates for database
$check_in_date = date('Y-m-d H:i:s', $check_in_timestamp);
$check_out_date = date('Y-m-d H:i:s', $check_out_timestamp);

// Check if the room exists using prepared statement
$room_stmt = $conn->prepare("SELECT id FROM rooms WHERE id = ?");
$room_stmt->bind_param("i", $room_id);
$room_stmt->execute();
$room_result = $room_stmt->get_result();

if ($room_result->num_rows === 0) {
    echo json_encode(['available' => false, 'error' => 'Invalid room ID']);
    $room_stmt->close();
    exit;
}
$room_stmt->close();

// Check availability using prepared statement
if ($booking_id > 0) {
    // Editing an existing booking - exclude it from the check
    $avail_stmt = $conn->prepare("SELECT id FROM bookings 
                                  WHERE room_id = ? 
                                  AND status IN ('approved', 'pending')
                                  AND id != ?
                                  AND (? < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) 
                                       AND DATE_ADD(?, INTERVAL 20 MINUTE) > check_in_date)");
    $avail_stmt->bind_param("iiss", $room_id, $booking_id, $check_in_date, $check_out_date);
} else {
    // New booking
    $avail_stmt = $conn->prepare("SELECT id FROM bookings 
                                  WHERE room_id = ? 
                                  AND status IN ('approved', 'pending')
                                  AND (? < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) 
                                       AND DATE_ADD(?, INTERVAL 20 MINUTE) > check_in_date)");
    $avail_stmt->bind_param("iss", $room_id, $check_in_date, $check_out_date);
}

$avail_stmt->execute();
$result = $avail_stmt->get_result();

// Return availability status
echo json_encode(['available' => ($result->num_rows === 0)]);

$avail_stmt->close();