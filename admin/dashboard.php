<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . AUTH_PATH . 'login.php');
    exit;
}

include('../includes/header.php');
include('sidebar.php');
include('../includes/db.php');

// Get pending bookings with error handling
$pending_bookings_query = "SELECT b.*, r.room_name, u.username 
                          FROM bookings b 
                          JOIN rooms r ON b.room_id = r.id 
                          JOIN users u ON b.user_id = u.id 
                          WHERE b.status = 'pending' 
                          ORDER BY b.created_at DESC";
$pending_bookings = mysqli_query($conn, $pending_bookings_query);

if (!$pending_bookings) {
    $error_message = "Error fetching bookings: " . mysqli_error($conn);
}

// Get hotel statistics
// Total rooms
$total_rooms_query = "SELECT COUNT(*) as total FROM rooms";
$total_rooms_result = mysqli_query($conn, $total_rooms_query);
$total_rooms = mysqli_fetch_assoc($total_rooms_result)['total'] ?? 0;

// Currently booked rooms
$booked_rooms_query = "SELECT COUNT(DISTINCT room_id) as booked FROM bookings 
                      WHERE status = 'approved' 
                      AND (NOW() BETWEEN check_in_date AND DATE_ADD(check_out_date, INTERVAL 20 MINUTE))";
$booked_rooms_result = mysqli_query($conn, $booked_rooms_query);
$booked_rooms = mysqli_fetch_assoc($booked_rooms_result)['booked'] ?? 0;

// Available rooms
$available_rooms = $total_rooms - $booked_rooms;

// Pending bookings count
$pending_count = mysqli_num_rows($pending_bookings);

// Total bookings
$total_bookings_query = "SELECT COUNT(*) as total FROM bookings";
$total_bookings_result = mysqli_query($conn, $total_bookings_query);
$total_bookings = mysqli_fetch_assoc($total_bookings_result)['total'] ?? 0;

// Recent bookings (last 7 days)
$recent_bookings_query = "SELECT COUNT(*) as recent FROM bookings WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
$recent_bookings = mysqli_fetch_assoc($recent_bookings_result)['recent'] ?? 0;

// Dining reservation stats
$pending_dining_query = "SELECT COUNT(*) as pending FROM dining_reservations WHERE status = 'pending'";
$pending_dining_result = mysqli_query($conn, $pending_dining_query);
$pending_dining = mysqli_fetch_assoc($pending_dining_result)['pending'] ?? 0;

$total_dining_query = "SELECT COUNT(*) as total FROM dining_reservations";
$total_dining_result = mysqli_query($conn, $total_dining_query);
$total_dining = mysqli_fetch_assoc($total_dining_result)['total'] ?? 0;

// Spa booking stats
$pending_spa_query = "SELECT COUNT(*) as pending FROM spa_bookings WHERE status = 'pending'";
$pending_spa_result = mysqli_query($conn, $pending_spa_query);
$pending_spa = mysqli_fetch_assoc($pending_spa_result)['pending'] ?? 0;

$total_spa_query = "SELECT COUNT(*) as total FROM spa_bookings";
$total_spa_result = mysqli_query($conn, $total_spa_query);
$total_spa = mysqli_fetch_assoc($total_spa_result)['total'] ?? 0;
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white mb-2" style="font-family: 'AureliaLight';">Admin Dashboard</h2>
    <p class="text-gray-400">Welcome back, Admin. Here's what's happening today.</p>
</div>

<?php if (isset($error_message)): ?>
    <div class="bg-red-900/20 text-red-400 p-4 rounded-xl border border-red-900/50 flex items-center mb-6">
        <i class="fas fa-exclamation-circle mr-3"></i>
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Rooms -->
    <div
        class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-blue-900/20 text-blue-400"
                style="width: 3rem; height: 3rem;">
                <i class="fas fa-door-open text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wide">Total Rooms</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-white"><?php echo $total_rooms; ?></p>
        </div>
        <a href="<?php echo ADMIN_PATH; ?>rooms.php"
            class="text-sm font-medium text-blue-400 mt-3 inline-block hover:text-blue-300">Manage Rooms
            &rarr;</a>
    </div>

    <!-- Available Rooms -->
    <div
        class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-green-900/20 text-green-400"
                style="width: 3rem; height: 3rem;">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wide">Available Rooms</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-white"><?php echo $available_rooms; ?></p>
            <span class="ml-2 text-sm text-gray-500">/ <?php echo $total_rooms; ?></span>
        </div>
        <p class="text-sm text-gray-500 mt-3"><?php echo $booked_rooms; ?> currently booked</p>
    </div>

    <!-- Pending Bookings -->
    <div
        class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-yellow-900/20 text-yellow-500"
                style="width: 3rem; height: 3rem;">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <?php if ($pending_count > 0): ?>
                <span class="bg-red-900/20 text-red-400 text-xs font-bold px-2 py-1 rounded-full animate-pulse">Action
                    Needed</span>
            <?php endif; ?>
        </div>
        <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wide">Pending Bookings</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-white"><?php echo $pending_count; ?></p>
        </div>
        <a href="<?php echo ADMIN_PATH; ?>bookings.php?status=pending"
            class="text-sm font-medium text-yellow-500 mt-3 inline-block hover:text-yellow-400">Review Now &rarr;</a>
    </div>

    <!-- Recent Activity -->
    <div
        class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
        <div class="flex items-center justify-between mb-4">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-cyan-900/20 text-cyan-400"
                style="width: 3rem; height: 3rem;">
                <i class="fas fa-chart-line text-xl"></i>
            </div>
        </div>
        <h3 class="text-gray-400 text-sm font-medium uppercase tracking-wide">Recent Bookings</h3>
        <div class="flex items-baseline mt-1">
            <p class="text-3xl font-bold text-white"><?php echo $recent_bookings; ?></p>
        </div>
        <p class="text-sm text-gray-500 mt-3">In the last 7 days</p>
    </div>
</div>

<!-- Secondary Stats (Dining & Spa) -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div
                class="w-10 h-10 rounded-full bg-orange-900/20 text-orange-500 flex items-center justify-center shrink-0">
                <i class="fas fa-utensils"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Total Dining</h4>
                <p class="text-xl font-bold text-white"><?php echo $total_dining; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-red-900/20 text-red-400 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Pending Dining</h4>
                <p class="text-xl font-bold text-white"><?php echo $pending_dining; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-teal-900/20 text-teal-400 flex items-center justify-center shrink-0">
                <i class="fas fa-spa"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Total Spa</h4>
                <p class="text-xl font-bold text-white"><?php echo $total_spa; ?></p>
            </div>
        </div>
    </div>
    <div class="bg-gray-900 rounded-2xl p-6 shadow-sm border border-gray-800 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-red-900/20 text-red-400 flex items-center justify-center shrink-0">
                <i class="fas fa-exclamation"></i>
            </div>
            <div>
                <h4 class="text-gray-500 text-xs font-bold uppercase">Pending Spa</h4>
                <p class="text-xl font-bold text-white"><?php echo $pending_spa; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Bookings Table -->
<div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden mb-8">
    <div class="p-6 bg-gray-900 border-b border-gray-800 flex justify-between items-center">
        <h3 class="font-bold text-xl text-white" style="font-family: 'AureliaLight';">Pending Approvals</h3>
        <a href="<?php echo ADMIN_PATH; ?>bookings.php?status=pending"
            class="text-sm font-bold text-yellow-500 hover:text-yellow-400">View
            All</a>
    </div>
    <div class="overflow-x-auto">
        <?php if ($pending_bookings && mysqli_num_rows($pending_bookings) > 0): ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold">User</th>
                        <th class="p-4 font-bold">Room</th>
                        <th class="p-4 font-bold">Check-in / Check-out</th>
                        <th class="p-4 font-bold">Customer</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php while ($booking = mysqli_fetch_assoc($pending_bookings)): ?>
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="p-4">
                                <span class="font-bold text-white"><?php echo htmlspecialchars($booking['username']); ?></span>
                            </td>
                            <td class="p-4">
                                <span class="text-gray-400"><?php echo htmlspecialchars($booking['room_name']); ?></span>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-white">
                                    <span class="text-xs font-bold text-gray-500 uppercase mr-1">In:</span>
                                    <?php echo !empty($booking['check_in_date']) ? date('M j, g:i A', strtotime($booking['check_in_date'])) : 'N/A'; ?>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <span class="text-xs font-bold text-gray-500 uppercase mr-1">Out:</span>
                                    <?php echo !empty($booking['check_out_date']) ? date('M j, Y g:i A', strtotime($booking['check_out_date'])) : 'N/A'; ?>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="text-white"><?php echo htmlspecialchars($booking['customer_name']); ?></span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button
                                        class="approve-booking px-3 py-1.5 bg-green-900/30 text-green-400 rounded-lg text-xs font-bold hover:bg-green-900/50 transition-colors"
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                        Approve
                                    </button>
                                    <button
                                        class="reject-booking px-3 py-1.5 bg-red-900/30 text-red-400 rounded-lg text-xs font-bold hover:bg-red-900/50 transition-colors"
                                        data-booking-id="<?php echo $booking['id']; ?>">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center text-gray-500">
                <i class="fas fa-check-circle text-4xl mb-4 text-gray-800"></i>
                <p>All caught up! No pending bookings.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-2xl border-0 shadow-2xl overflow-hidden bg-gray-900">
            <div class="modal-header border-0 p-6" id="modalHeader">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center" id="modalIconBg">
                        <i class="fas" id="modalIcon"></i>
                    </div>
                    <h5 class="modal-title font-bold text-xl text-white" id="modalTitle">Confirm Action</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-6 text-center">
                <p class="text-gray-300 mb-2" id="modalMessage">Are you sure?</p>
                <input type="hidden" id="actionBookingId">
                <input type="hidden" id="actionType">
            </div>
            <div class="modal-footer border-0 p-6 pt-0 flex justify-center gap-3">
                <button type="button"
                    class="px-6 py-2.5 rounded-xl text-gray-400 font-bold hover:bg-gray-800 transition-all"
                    data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="px-6 py-2.5 rounded-xl font-bold text-white transition-all shadow-lg"
                    id="confirmActionBtn">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Modal
        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        const modalEl = document.getElementById('confirmationModal');

        // Elements
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const modalIcon = document.getElementById('modalIcon');
        const modalIconBg = document.getElementById('modalIconBg');
        const modalHeader = document.getElementById('modalHeader');
        const confirmBtn = document.getElementById('confirmActionBtn');
        const bookingIdInput = document.getElementById('actionBookingId');
        const actionTypeInput = document.getElementById('actionType');

        // Move modal to body to avoid z-index issues
        if (modalEl.parentElement !== document.body) {
            document.body.appendChild(modalEl);
        }

        // Cleanup modal backdrop
        modalEl.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.style.overflow = '';
        });

        // Event Delegation for Approve/Reject Actions
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.approve-booking, .reject-booking');
            if (!btn) return;

            e.preventDefault();
            e.stopPropagation();

            const isApprove = btn.classList.contains('approve-booking');
            const bookingId = btn.dataset.bookingId;
            const action = isApprove ? 'approved' : 'rejected';

            // Configure Modal
            bookingIdInput.value = bookingId;
            actionTypeInput.value = action;

            if (isApprove) {
                modalTitle.textContent = 'Approve Booking';
                modalMessage.textContent = 'Are you sure you want to approve this booking?';
                modalIcon.className = 'fas fa-check text-xl';
                modalIconBg.className = 'w-12 h-12 rounded-full bg-green-900/20 text-green-500 flex items-center justify-center';
                modalHeader.className = 'modal-header border-0 bg-green-900/10 p-6';
                confirmBtn.className = 'px-6 py-2.5 rounded-xl font-bold text-white transition-all shadow-lg bg-green-600 hover:bg-green-700';
                confirmBtn.textContent = 'Approve Booking';
            } else {
                modalTitle.textContent = 'Reject Booking';
                modalMessage.textContent = 'Are you sure you want to reject this booking?';
                modalIcon.className = 'fas fa-times text-xl';
                modalIconBg.className = 'w-12 h-12 rounded-full bg-red-900/20 text-red-500 flex items-center justify-center';
                modalHeader.className = 'modal-header border-0 bg-red-900/10 p-6';
                confirmBtn.className = 'px-6 py-2.5 rounded-xl font-bold text-white transition-all shadow-lg bg-red-600 hover:bg-red-700';
                confirmBtn.textContent = 'Reject Booking';
            }

            confirmationModal.show();
        });

        // Handle Confirm Click
        confirmBtn.addEventListener('click', async function () {
            const bookingId = bookingIdInput.value;
            const status = actionTypeInput.value;
            const btn = this;

            // Loading State
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
            btn.disabled = true;

            try {
                // Use relative path strictly
                const response = await fetch('update_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `booking_id=${bookingId}&status=${status}`
                });

                const text = await response.text();

                if (response.ok) {
                    confirmationModal.hide();
                    location.reload();
                } else {
                    alert('Error: ' + text);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Network error:', error);
                alert('Connection error. Please try again.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });
</script>

</main>
</div>
</div>

<?php include('../includes/footer.php'); ?>