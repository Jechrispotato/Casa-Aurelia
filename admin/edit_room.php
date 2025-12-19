<?php
session_start();
include('../includes/db.php');
include('../includes/security.php');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    log_security_event('unauthorized_room_edit', 'Non-admin user attempted to access edit_room.php');
    $_SESSION['error'] = "You don't have permission to access this page.";
    header('Location: ../auth/login.php');
    exit;
}

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Check if a room ID is passed
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid room ID.";
    header('Location: rooms.php');
    exit;
}

// Sanitize room ID
$room_id = (int)$_GET['id'];

if ($room_id <= 0) {
    $_SESSION['error'] = "Invalid room ID.";
    header('Location: rooms.php');
    exit;
}

// Fetch the current room details using prepared statement
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Room not found.";
    header('Location: rooms.php');
    exit;
}

$room = $result->fetch_assoc();
$stmt->close();

$success_message = '';
$error_message = '';

// Handle room update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        log_security_event('csrf_token_failure', 'Room edit attempt with invalid CSRF token');
        $error_message = "Security verification failed. Please try again.";
    } else {
        // Validate and sanitize inputs
        $room_name = trim($_POST['room_name'] ?? '');
        $price = $_POST['price'] ?? '';
        $price_per_hour = $_POST['price_per_hour'] ?? '';
        $description = trim($_POST['description'] ?? '');
        
        $errors = [];
        
        // Validate room name
        if (empty($room_name)) {
            $errors[] = "Room name is required.";
        } elseif (strlen($room_name) > 100) {
            $errors[] = "Room name must be less than 100 characters.";
        }
        
        // Validate price
        if (!is_numeric($price) || $price < 0) {
            $errors[] = "Price must be a valid positive number.";
        }
        
        // Validate hourly price
        if (!empty($price_per_hour) && (!is_numeric($price_per_hour) || $price_per_hour < 0)) {
            $errors[] = "Hourly price must be a valid positive number.";
        }
        
        // Validate description length
        if (strlen($description) > 1000) {
            $errors[] = "Description must be less than 1000 characters.";
        }
        
        if (!empty($errors)) {
            $error_message = implode(' ', $errors);
        } else {
            // Update using prepared statement
            $price = (float)$price;
            $price_per_hour = !empty($price_per_hour) ? (float)$price_per_hour : ceil($price * 0.15);
            
            $update_stmt = $conn->prepare("UPDATE rooms SET room_name = ?, price = ?, price_per_hour = ?, description = ? WHERE id = ?");
            $update_stmt->bind_param("sddsi", $room_name, $price, $price_per_hour, $description, $room_id);
            
            if ($update_stmt->execute()) {
                log_security_event('room_updated', "Room $room_id updated by admin " . $_SESSION['user_id']);
                $success_message = "Room updated successfully!";
                
                // Refresh room data
                $room['room_name'] = $room_name;
                $room['price'] = $price;
                $room['price_per_hour'] = $price_per_hour;
                $room['description'] = $description;
            } else {
                log_security_event('room_update_error', "Failed to update room $room_id");
                $error_message = "Error updating room. Please try again.";
            }
            
            $update_stmt->close();
        }
    }
    
    // Regenerate CSRF token after form submission
    $csrf_token = generate_csrf_token();
}
?>

<?php include('../includes/header.php'); ?>

<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-6">
        <a href="rooms.php" class="text-yellow-600 hover:text-yellow-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Rooms
        </a>
    </div>
    
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Room</h2>
        
        <?php if ($success_message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="edit_room.php?id=<?php echo $room_id; ?>" method="POST" class="space-y-6">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">

            <!-- Room Name -->
            <div>
                <label for="room_name" class="block text-sm font-medium text-gray-700 mb-2">Room Name</label>
                <input type="text" name="room_name" id="room_name" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all" 
                    value="<?php echo htmlspecialchars($room['room_name']); ?>" 
                    required 
                    maxlength="100">
            </div>

            <!-- Price -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">Price per Night ($)</label>
                    <input type="number" name="price" id="price" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all" 
                        value="<?php echo htmlspecialchars($room['price']); ?>" 
                        required 
                        min="0" 
                        step="0.01">
                </div>
                
                <div>
                    <label for="price_per_hour" class="block text-sm font-medium text-gray-700 mb-2">Price per Hour ($)</label>
                    <input type="number" name="price_per_hour" id="price_per_hour" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all" 
                        value="<?php echo htmlspecialchars($room['price_per_hour'] ?? ''); ?>" 
                        min="0" 
                        step="0.01"
                        placeholder="Leave empty for auto-calculate">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to auto-calculate (15% of nightly rate)</p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" id="description" rows="4" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all resize-none"
                    maxlength="1000"><?php echo htmlspecialchars($room['description'] ?? ''); ?></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-4 pt-4">
                <button type="submit" name="update_room" 
                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-yellow-500/30">
                    Update Room
                </button>
                <a href="rooms.php" 
                    class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-300 text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
