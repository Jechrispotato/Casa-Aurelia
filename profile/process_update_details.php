<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // 1. Validation
    $errors = [];

    // Username validation
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 30) {
        $errors[] = "Username must be between 3 and 30 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Email validation
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode(' ', $errors);
        header('Location: profile.php');
        exit;
    }

    // 2. Check for Duplicates (exclude current user)
    // Check Username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->bind_param("si", $username, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Username is already taken.";
        $stmt->close();
        header('Location: profile.php');
        exit;
    }
    $stmt->close();

    // Check Email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Email is already registered.";
        $stmt->close();
        header('Location: profile.php');
        exit;
    }
    $stmt->close();

    // 3. Update Database
    $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $username, $email, $user_id);

    if ($update_stmt->execute()) {
        // 4. Update Session for immediate header reflection
        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Profile details updated successfully.";
    } else {
        $_SESSION['error'] = "Database error: Failed to update profile.";
    }
    $update_stmt->close();

    header('Location: profile.php');
    exit;

} else {
    // Invalid request method
    header('Location: profile.php');
    exit;
}
