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
    $room_name = mysqli_real_escape_string($conn, $_POST['room_name']);
    $price = (float) $_POST['price'];
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    $existing_images = isset($_POST['existing_images']) ? $_POST['existing_images'] : '';

    // Validate input
    if (empty($room_name) || $price <= 0 || $room_id <= 0) {
        http_response_code(400);
        exit('Invalid input');
    }

    // Process images
    $all_images = [];

    // Add existing images that weren't removed
    if (!empty($existing_images)) {
        $existing_arr = array_filter(array_map('trim', explode(',', $existing_images)));
        $all_images = array_merge($all_images, $existing_arr);
    }

    // Handle new uploaded images
    if (isset($_FILES['new_images']) && !empty($_FILES['new_images']['name'][0])) {
        $upload_dir = '../images/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        $file_count = count($_FILES['new_images']['name']);

        for ($i = 0; $i < $file_count; $i++) {
            // Skip empty entries
            if (empty($_FILES['new_images']['name'][$i]))
                continue;

            $tmp_name = $_FILES['new_images']['tmp_name'][$i];
            $file_name = $_FILES['new_images']['name'][$i];
            $file_size = $_FILES['new_images']['size'][$i];
            $file_error = $_FILES['new_images']['error'][$i];

            // Check for errors
            if ($file_error !== UPLOAD_ERR_OK) {
                continue; // Skip files with errors
            }

            // Verify file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($finfo, $tmp_name);
            finfo_close($finfo);

            if (!in_array($file_type, $allowed_types)) {
                continue; // Skip invalid types
            }

            // Check file size
            if ($file_size > $max_size) {
                continue; // Skip large files
            }

            // Generate unique filename
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $new_filename = 'room_' . time() . '_' . uniqid() . '_' . $i . '.' . $ext;
            $destination = $upload_dir . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($tmp_name, $destination)) {
                $all_images[] = $new_filename;
            }
        }
    }

    // Limit to 4 images max
    $all_images = array_slice($all_images, 0, 4);

    // Create comma-separated string
    $room_image = implode(',', $all_images);
    $room_image = mysqli_real_escape_string($conn, $room_image);

    // Update room
    $query = "UPDATE rooms 
              SET room_name = '$room_name', 
                  price = $price, 
                  description = '$description', 
                  room_image = '$room_image' 
              WHERE id = $room_id";

    if (mysqli_query($conn, $query)) {
        http_response_code(200);
        exit('Room updated successfully');
    } else {
        http_response_code(500);
        exit('Database error: ' . mysqli_error($conn));
    }
} else {
    http_response_code(405);
    exit('Method not allowed');
}