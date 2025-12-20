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

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';


// Build the query
$query = "SELECT s.*, u.username, u.email 
          FROM spa_bookings s 
          LEFT JOIN users u ON s.user_id = u.id 
          WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND s.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
}



$query .= " ORDER BY s.created_at DESC";

$bookings = mysqli_query($conn, $query);
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-3xl font-bold text-white mb-2" style="font-family: 'AureliaLight';">Spa Appointments</h2>
    <p class="text-gray-400">Manage spa treatments and appointments.</p>
</div>

<!-- Filters -->
<div class="bg-gray-900 rounded-2xl shadow-sm border border-gray-800 p-6 mb-8">
    <form class="flex flex-col md:flex-row gap-4">
        <div class="flex-shrink-0 w-full md:w-48">
            <select
                class="w-full px-4 py-3 rounded-xl border border-gray-700 bg-gray-800 text-white focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 outline-none transition-all appearance-none cursor-pointer"
                name="status" onchange="this.form.submit()">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Bookings</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed
                </option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled
                </option>
            </select>
        </div>
    </form>
</div>

<!-- Appointments Table -->
<div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
        <?php if ($bookings && mysqli_num_rows($bookings) > 0): ?>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="p-4 font-bold">ID</th>
                        <th class="p-4 font-bold">Guest</th>
                        <th class="p-4 font-bold">Treatment</th>
                        <th class="p-4 font-bold">Date & Time</th>
                        <th class="p-4 font-bold">Guests</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                        <?php
                        $status = strtolower($booking['status'] ?? 'pending');
                        $status_styles = [
                            'pending' => 'bg-yellow-900/30 text-yellow-500',
                            'confirmed' => 'bg-green-900/30 text-green-400',
                            'cancelled' => 'bg-gray-800 text-gray-500'
                        ];
                        $status_class = $status_styles[$status] ?? 'bg-gray-800 text-gray-500';
                        ?>
                        <tr class="hover:bg-gray-800/50 transition-colors group">
                            <td class="p-4 font-mono text-xs text-gray-400">#<?php echo $booking['id']; ?></td>
                            <td class="p-4">
                                <div class="font-bold text-white"><?php echo htmlspecialchars($booking['customer_name']); ?>
                                </div>
                                <div class="text-xs text-gray-400">
                                    <?php echo htmlspecialchars($booking['email'] ?? 'No email'); ?>
                                </div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($booking['phone'] ?? ''); ?>
                                </div>
                            </td>
                            <td class="p-4 font-medium text-gray-300"><?php echo htmlspecialchars($booking['treatment']); ?>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-white">
                                    <?php echo isset($booking['spa_date']) ? date('M d, Y', strtotime($booking['spa_date'])) : '-'; ?>
                                </div>
                                <div class="text-xs text-gray-400">
                                    <?php echo isset($booking['spa_time']) ? date('g:i A', strtotime($booking['spa_time'])) : '-'; ?>
                                </div>
                            </td>
                            <td class="p-4 text-sm text-gray-400"><?php echo htmlspecialchars($booking['guests']); ?></td>
                            <td class="p-4">
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase <?php echo $status_class; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <?php if ($status === 'pending'): ?>
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
                                <?php else: ?>
                                    <?php
                                    $status_badge_class = 'text-gray-400';
                                    if ($status === 'confirmed')
                                        $status_badge_class = 'bg-green-500 text-white shadow-sm';
                                    if ($status === 'cancelled')
                                        $status_badge_class = 'bg-red-500 text-white shadow-sm';
                                    ?>
                                    <div
                                        class="inline-block px-3 py-1.5 text-xs <?php echo $status_badge_class; ?> font-bold rounded-lg">
                                        <?php echo ucfirst($status); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-600">
                    <i class="fas fa-spa text-2xl"></i>
                </div>
                <p class="text-gray-400 font-medium">No appointments found matching your criteria.</p>
                <a href="<?php echo ADMIN_PATH; ?>spa_bookings.php"
                    class="text-yellow-500 hover:text-yellow-400 text-sm font-bold mt-2 inline-block">Clear Filters</a>
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
            const action = isApprove ? 'confirmed' : 'cancelled'; // SPA status logic

            // Configure Modal
            bookingIdInput.value = bookingId;
            actionTypeInput.value = action;

            if (isApprove) {
                modalTitle.textContent = 'Confirm Appointment';
                modalMessage.textContent = 'Are you sure you want to confirm this spa appointment?';
                modalIcon.className = 'fas fa-check text-xl';
                modalIconBg.className = 'w-12 h-12 rounded-full bg-green-900/20 text-green-500 flex items-center justify-center';
                modalHeader.className = 'modal-header border-0 bg-green-900/10 p-6';
                confirmBtn.className = 'px-6 py-2.5 rounded-xl font-bold text-white transition-all shadow-lg bg-green-600 hover:bg-green-700';
                confirmBtn.textContent = 'Confirm Appointment';
            } else {
                modalTitle.textContent = 'Cancel Appointment';
                modalMessage.textContent = 'Are you sure you want to cancel this spa appointment?';
                modalIcon.className = 'fas fa-times text-xl';
                modalIconBg.className = 'w-12 h-12 rounded-full bg-red-900/20 text-red-500 flex items-center justify-center';
                modalHeader.className = 'modal-header border-0 bg-red-900/10 p-6';
                confirmBtn.className = 'px-6 py-2.5 rounded-xl font-bold text-white transition-all shadow-lg bg-red-600 hover:bg-red-700';
                confirmBtn.textContent = 'Cancel Appointment';
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
                const response = await fetch('update_spa_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
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