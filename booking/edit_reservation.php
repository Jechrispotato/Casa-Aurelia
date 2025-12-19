<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

include('../includes/header.php');
include('../includes/db.php');

$user_id = $_SESSION['user_id'];
$reservation_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Validation
if ($reservation_id <= 0 || !in_array($type, ['dining', 'spa'])) {
    $_SESSION['error'] = 'Invalid reservation details.';
    header('Location: view_bookings.php');
    exit;
}

// Fetch Reservation details
$details = null;

if ($type === 'dining') {
    $stmt = $conn->prepare("SELECT * FROM dining_reservations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reservation_id, $user_id);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif ($type === 'spa') {
    $stmt = $conn->prepare("SELECT * FROM spa_bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $reservation_id, $user_id);
    $stmt->execute();
    $details = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Check if found and editable (pending status only)
if (!$details) {
    $_SESSION['error'] = 'Reservation not found or access denied.';
    header('Location: view_bookings.php');
    exit;
}

if (strtolower($details['status']) !== 'pending') {
    $_SESSION['error'] = 'Only pending reservations can be edited.';
    header('Location: view_bookings.php');
    exit;
}
?>

<div class="min-h-screen bg-gray-950 py-12 font-sans">
    <div class="container mx-auto px-4 max-w-2xl">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white font-serif mb-2">Edit Reservation</h1>
                <p class="text-gray-400">Update your <?php echo ucfirst($type); ?> details</p>
            </div>
            <a href="view_bookings.php"
                class="px-4 py-2 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <div class="bg-gray-900 rounded-2xl shadow-xl border border-gray-800 overflow-hidden p-8">

            <?php if (isset($_SESSION['error'])): ?>
                <div
                    class="mb-6 bg-red-900/20 text-red-500 px-4 py-3 rounded-xl border border-red-900/50 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="process/update_reservation.php" method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $reservation_id; ?>">
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">

                <?php if ($type === 'dining'): ?>
                    <!-- Dining Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Venue</label>
                            <select name="venue" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                                <?php
                                $venues = [
                                    'restaurant' => 'The Aurelia Restaurant',
                                    'lounge' => 'The Skyline Lounge',
                                    'grand_room' => 'The Grand Room',
                                    'wine_cellar' => 'The Wine Cellar'
                                ];
                                foreach ($venues as $val => $label) {
                                    $selected = ($details['venue'] === $val) ? 'selected' : '';
                                    echo "<option value=\"$val\" $selected>$label</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date</label>
                            <input type="date" name="date"
                                value="<?php echo htmlspecialchars($details['reservation_date']); ?>" required
                                min="<?php echo date('Y-m-d'); ?>"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Time</label>
                            <select name="time" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                                <?php
                                $times = ['17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'];
                                $current_time = date('H:i', strtotime($details['reservation_time']));
                                foreach ($times as $t) {
                                    $selected = ($current_time === $t) ? 'selected' : '';
                                    echo "<option value=\"$t\" $selected>" . date('g:i A', strtotime($t)) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Guests</label>
                            <select name="guests" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($details['number_of_guests'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Person<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Special Requests</label>
                        <textarea name="special_requests" rows="3"
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors"
                            placeholder="Any dietary restrictions or special occasions?"><?php echo htmlspecialchars($details['special_requests']); ?></textarea>
                    </div>

                <?php elseif ($type === 'spa'): ?>
                    <!-- Spa Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Treatment</label>
                            <select name="treatment" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                                <optgroup label="Massage">
                                    <option value="Swedish Massage" <?php echo ($details['treatment'] == 'Swedish Massage') ? 'selected' : ''; ?>>Swedish Massage (60 mins)</option>
                                    <option value="Deep Tissue Massage" <?php echo ($details['treatment'] == 'Deep Tissue Massage') ? 'selected' : ''; ?>>Deep Tissue Massage (60 mins)</option>
                                    <option value="Hot Stone Massage" <?php echo ($details['treatment'] == 'Hot Stone Massage') ? 'selected' : ''; ?>>Hot Stone Massage (90 mins)</option>
                                    <option value="Aromatherapy Massage" <?php echo ($details['treatment'] == 'Aromatherapy Massage') ? 'selected' : ''; ?>>Aromatherapy Massage (60 mins)</option>
                                </optgroup>
                                <optgroup label="Packages">
                                    <option value="Relaxation Package" <?php echo ($details['treatment'] == 'Relaxation Package') ? 'selected' : ''; ?>>Relaxation Package (3 hrs)</option>
                                    <option value="Renewal Package" <?php echo ($details['treatment'] == 'Renewal Package') ? 'selected' : ''; ?>>Renewal Package (4 hrs)</option>
                                    <option value="Couples Retreat" <?php echo ($details['treatment'] == 'Couples Retreat') ? 'selected' : ''; ?>>Couples Retreat (3 hrs)</option>
                                </optgroup>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date</label>
                            <input type="date" name="date" value="<?php echo htmlspecialchars($details['spa_date']); ?>"
                                required min="<?php echo date('Y-m-d'); ?>"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Time</label>
                            <select name="time" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                                <?php
                                $times = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
                                $current_time = date('H:i', strtotime($details['spa_time']));
                                foreach ($times as $t) {
                                    $selected = ($current_time === $t) ? 'selected' : '';
                                    echo "<option value=\"$t\" $selected>" . date('g:i A', strtotime($t)) . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Guests</label>
                            <select name="guests" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo ($details['guests'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Person<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Special Requests</label>
                        <textarea name="special_requests" rows="3"
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors"
                            placeholder="Any specific preferences or focus areas?"><?php echo htmlspecialchars($details['special_requests']); ?></textarea>
                    </div>
                <?php endif; ?>

                <div class="flex gap-4 pt-4 border-t border-gray-800">
                    <button type="submit"
                        class="px-8 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-yellow-600/30 transform hover:-translate-y-0.5">
                        Save Changes
                    </button>
                    <a href="view_bookings.php"
                        class="px-6 py-3 bg-gray-800 text-gray-300 font-bold rounded-xl hover:bg-gray-700 transition flex items-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>