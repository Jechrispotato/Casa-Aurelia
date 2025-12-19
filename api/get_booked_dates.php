<?php
include('../includes/db.php');

header('Content-Type: application/json');

// Check if room_id is provided
if (!isset($_GET['room_id'])) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$room_id = (int)$_GET['room_id'];

// Validate room_id
if ($room_id <= 0) {
    echo json_encode(['error' => 'Invalid room ID']);
    exit;
}

// Get all approved bookings for this room using prepared statement
$stmt = $conn->prepare("SELECT check_in_date, DATE_ADD(check_out_date, INTERVAL 20 MINUTE) as check_out_date 
                        FROM bookings 
                        WHERE room_id = ? 
                        AND status IN ('approved', 'pending')
                        AND DATE_ADD(check_out_date, INTERVAL 20 MINUTE) >= NOW()");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_periods = [];
while ($booking = $result->fetch_assoc()) {
    $booked_periods[] = [
        'start' => $booking['check_in_date'],
        'end' => $booking['check_out_date']
    ];
}

$stmt->close();

echo json_encode(['booked_periods' => $booked_periods]);