<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in 
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'notifications.php';
    $_SESSION['error'] = "Please login to view your notifications";
    header('Location: login.php');
    exit;
}

include('../includes/db.php');

// Mark as read function - must be before any HTML output
if (isset($_GET['mark_read']) && $_GET['mark_read'] == 'all') {
    $user_id = $_SESSION['user_id'];

    // Check if central notifications table exists
    $check_table = @mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
    $table_exists = ($check_table && mysqli_num_rows($check_table) > 0);

    if ($table_exists) {
        // Mark central notifications as read
        $update = "UPDATE notifications SET seen = 1 WHERE user_id = $user_id AND seen = 0";
        @mysqli_query($conn, $update);
    } else {
        // Fall back to legacy per-table updates
        $update_rooms = "UPDATE bookings SET notification_seen = 1 
                        WHERE user_id = $user_id AND notification_seen = 0";
        @mysqli_query($conn, $update_rooms);

        $update_dining = "UPDATE dining_reservations SET notification_seen = 1 
                          WHERE user_id = $user_id AND notification_seen = 0";
        @mysqli_query($conn, $update_dining);

        $update_spa = "UPDATE spa_bookings SET notification_seen = 1 
                       WHERE user_id = $user_id AND notification_seen = 0";
        @mysqli_query($conn, $update_spa);
    }

    // Redirect to remove the query string
    header('Location: notifications.php');
    exit;
}

// Include header after redirect logic
include('../includes/header.php');

// Get user's ID
$user_id = $_SESSION['user_id'];

// Load notifications from central notifications table
$notifications = [];
$notif_q = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC";
$notif_res = mysqli_query($conn, $notif_q);
if ($notif_res) {
    while ($row = mysqli_fetch_assoc($notif_res)) {
        $notifications[] = $row;
    }
}

// Unread count (central)
$unread_count = 0;
$unread_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM notifications WHERE user_id = $user_id AND seen = 0");
$unread_count = ($unread_res) ? (int) mysqli_fetch_assoc($unread_res)['c'] : 0;
?>

<div class="min-h-screen bg-gray-950 py-12">
    <div class="container mx-auto px-4 max-w-5xl">

        <!-- Page Header -->
        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold font-serif text-white">Your Notifications</h1>
            <a href="profile.php" class="text-gray-400 hover:text-white font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>

        <!-- Notifications Card -->
        <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
            <!-- Header -->
            <div class="bg-gray-950 p-6 border-b border-gray-800 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white font-serif">Notifications</h2>
                    <p class="text-gray-400 text-sm mt-1">Stay updated on your bookings and reservations</p>
                </div>
                <?php if ($unread_count > 0): ?>
                    <span class="bg-yellow-600 text-white font-bold text-sm px-4 py-2 rounded-full shadow-lg">
                        <?php echo $unread_count; ?> New
                    </span>
                <?php endif; ?>
            </div>

            <!-- Notifications Body -->
            <?php if (!empty($notifications)): ?>
                <div class="p-6 space-y-4">
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        $type = $notification['type'] ?? 'room';
                        $status = $notification['status'] ?? '';
                        $is_positive = in_array($status, ['approved', 'confirmed']);
                        $date_formatted = date('M j, Y', strtotime($notification['created_at']));
                        $message = $notification['message'] ?? '';
                        $url = $notification['url'] ?? '';
                        $is_unread = ($notification['seen'] ?? 0) == 0;

                        // Icon and color based on type
                        $type_icon = 'fa-bell';
                        $icon_bg = 'bg-gray-800';
                        $icon_color = 'text-gray-400';

                        if ($type === 'dining') {
                            $type_icon = 'fa-utensils';
                            $icon_bg = 'bg-yellow-900/30';
                            $icon_color = 'text-yellow-500';
                        } elseif ($type === 'spa') {
                            $type_icon = 'fa-spa';
                            $icon_bg = 'bg-emerald-900/30';
                            $icon_color = 'text-emerald-400';
                        } else {
                            $type_icon = 'fa-bed';
                            $icon_bg = 'bg-blue-900/30';
                            $icon_color = 'text-blue-400';
                        }

                        // Status styling
                        $status_colors = [
                            'pending' => 'bg-yellow-900/30 text-yellow-500 border-yellow-900/50',
                            'confirmed' => 'bg-green-900/30 text-green-400 border-green-900/50',
                            'approved' => 'bg-green-900/30 text-green-400 border-green-900/50',
                            'cancelled' => 'bg-gray-800 text-gray-500 border-gray-700',
                            'rejected' => 'bg-red-900/30 text-red-500 border-red-900/50'
                        ];
                        $s_key = strtolower($status);
                        $status_class = isset($status_colors[$s_key]) ? $status_colors[$s_key] : 'bg-gray-800 text-gray-400 border-gray-700';
                        ?>

                        <div
                            class="p-5 <?php echo $is_unread ? 'bg-yellow-900/10 border-l-4 border-yellow-600' : 'bg-gray-800'; ?> rounded-xl border border-gray-700 hover:bg-gray-800/80 transition-all">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div
                                    class="shrink-0 w-12 h-12 rounded-full <?php echo $icon_bg . ' ' . $icon_color; ?> flex items-center justify-center">
                                    <i class="fas <?php echo $type_icon; ?>"></i>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <h3 class="font-bold text-white text-lg">
                                            <?php echo ($type === 'dining') ? 'Dining Reservation' : (($type === 'spa') ? 'Spa Reservation' : 'Room Booking'); ?>
                                        </h3>
                                        <span class="text-xs text-gray-500 shrink-0"><?php echo $date_formatted; ?></span>
                                    </div>

                                    <p class="text-gray-300 mb-3 leading-relaxed">
                                        <?php echo htmlspecialchars($message); ?>
                                    </p>

                                    <div class="flex items-center gap-3 flex-wrap">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-bold uppercase border <?php echo $status_class; ?>">
                                            <?php echo htmlspecialchars($status); ?>
                                        </span>

                                        <?php if (!empty($url)): ?>
                                            <a href="<?php echo BOOKING_PATH; ?>view_bookings.php"
                                                class="text-sm text-yellow-500 hover:text-yellow-400 font-medium flex items-center gap-1 transition-colors">
                                                View All Details <i class="fas fa-arrow-right text-xs"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Mark All as Read Button -->
                <a href="?mark_read=all"
                    class="block p-4 bg-gray-800 hover:bg-gray-700 text-center text-gray-300 font-semibold transition-colors border-t border-gray-700">
                    <i class="fas fa-check-double mr-2"></i>Mark All as Read
                </a>
            <?php else: ?>
                <!-- Empty State -->
                <div class="p-12 text-center">
                    <div class="w-20 h-20 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bell-slash text-4xl text-gray-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No Notifications</h3>
                    <p class="text-gray-400 mb-1">You have no notifications at this time.</p>
                    <p class="text-gray-500 text-sm">We'll notify you when there are updates to your bookings.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Footer -->
        <div class="mt-8 text-center space-y-4">
            <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
                <i class="fas fa-info-circle"></i>
                Notifications show status updates for your bookings from the last 30 days
            </p>
            <a href="../booking/view_bookings.php"
                class="inline-flex items-center gap-2 px-6 py-3 bg-gray-900 border border-gray-700 text-gray-300 font-bold rounded-xl hover:bg-gray-800 hover:border-yellow-600 transition-all shadow-lg">
                <i class="fas fa-list"></i>View All My Bookings
            </a>
        </div>
    </div>
</div>