<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to edit a booking";
    header('Location: ../auth/login.php');
    exit;
}

include('../includes/db.php');
include('../includes/header.php');

$user_id = (int) $_SESSION['user_id'];

// Check if a booking ID is passed
if (isset($_GET['id'])) {
    $booking_id = (int) $_GET['id'];

    // Fetch the current booking details - make sure it belongs to this user
    $result = mysqli_query($conn, "SELECT b.*, r.room_name, r.price 
                                   FROM bookings b
                                   JOIN rooms r ON b.room_id = r.id
                                   WHERE b.id = $booking_id AND b.user_id = $user_id");

    if (mysqli_num_rows($result) === 0) {
        $_SESSION['error'] = "Booking not found or you don't have permission to edit it.";
        header('Location: view_bookings.php');
        exit;
    }

    $booking = mysqli_fetch_assoc($result);

    // Check if booking is in an editable state (pending only)
    if (strtolower($booking['status']) !== 'pending') {
        $_SESSION['error'] = "Only pending bookings can be edited.";
        header('Location: view_bookings.php');
        exit;
    }

    // Fetch all rooms for the dropdown
    $room_result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY price ASC");
} else {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: view_bookings.php');
    exit;
}

// Get today's date and time for minimum check-in date
$today = date('Y-m-d H:i');

// Handle booking update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_booking'])) {
    // Sanitize inputs
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name']);
    $room_id = (int) $_POST['room_id'];
    $check_in_date = mysqli_real_escape_string($conn, $_POST['check_in_date']);
    $check_out_date = mysqli_real_escape_string($conn, $_POST['check_out_date']);

    // Validate dates
    if ($check_in_date < $today) {
        $_SESSION['error'] = "Cannot check in on a date in the past.";
        header("Location: edit_booking.php?id=$booking_id");
        exit;
    }

    if ($check_out_date <= $check_in_date) {
        $_SESSION['error'] = "Check-out date must be after check-in date.";
        header("Location: edit_booking.php?id=$booking_id");
        exit;
    }

    // Check if room is available for the selected dates (excluding this booking)
    $availability_query = "SELECT id FROM bookings 
                          WHERE room_id = $room_id 
                          AND id != $booking_id
                          AND status = 'approved'
                          AND (
                              ('$check_in_date' < DATE_ADD(check_out_date, INTERVAL 20 MINUTE) AND 
                               DATE_ADD('$check_out_date', INTERVAL 20 MINUTE) > check_in_date)
                          )";
    $availability_result = mysqli_query($conn, $availability_query);

    if (mysqli_num_rows($availability_result) > 0) {
        $_SESSION['error'] = "This room is already booked for some or all of the selected dates.";
        header("Location: edit_booking.php?id=$booking_id");
        exit;
    }

    // Calculate number of nights/hours and total price
    $diff_seconds = strtotime($check_out_date) - strtotime($check_in_date);
    $diff_hours = $diff_seconds / 3600;

    $room_query = mysqli_query($conn, "SELECT price, price_per_hour FROM rooms WHERE id = $room_id");
    $room = mysqli_fetch_assoc($room_query);

    $hourly_price = isset($room['price_per_hour']) && $room['price_per_hour'] > 0
        ? $room['price_per_hour']
        : ceil($room['price'] * 0.15);

    if ($diff_hours < 24) {
        $hours = max(1, ceil($diff_hours));
        $total_price = $hours * $hourly_price;
    } else {
        $nights = floor($diff_hours / 24);
        $remaining_hours = ceil(fmod($diff_hours, 24));

        if ($remaining_hours > 0) {
            $night_price = $nights * $room['price'];
            $overage_price = $remaining_hours * $hourly_price;

            if ($overage_price >= $room['price']) {
                $total_price = ($nights + 1) * $room['price'];
            } else {
                $total_price = $night_price + $overage_price;
            }
        } else {
            $nights = max(1, $nights);
            $total_price = $nights * $room['price'];
        }
    }

    try {
        // Start transaction
        mysqli_begin_transaction($conn);

        // Update the booking - reset status to pending for admin approval
        $update_sql = "UPDATE bookings 
                      SET customer_name = '$customer_name', 
                          room_id = $room_id, 
                          check_in_date = '$check_in_date', 
                          check_out_date = '$check_out_date', 
                          total_price = $total_price,
                          status = 'pending'
                      WHERE id = $booking_id AND user_id = $user_id";

        if (!mysqli_query($conn, $update_sql)) {
            throw new Exception("Error updating booking: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $_SESSION['success'] = "Your booking has been updated and will be reviewed by our staff.";
        header('Location: view_bookings.php');
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        $_SESSION['error'] = $e->getMessage();
        header("Location: edit_booking.php?id=$booking_id");
        exit;
    }
}

// Check for error messages
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Clear error message after getting it
?>

<!-- Add Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">

<!-- Custom styles for blocked dates -->
<style>
    .flatpickr-day.flatpickr-disabled,
    .flatpickr-day.flatpickr-blocked {
        background-color: #ffcccb !important;
        color: #333 !important;
        text-decoration: line-through;
        position: relative;
    }

    .flatpickr-day.flatpickr-disabled::after,
    .flatpickr-day.flatpickr-blocked::after {
        content: "✕";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ff0000;
        font-weight: bold;
    }

    .date-info-tooltip {
        display: none;
        position: absolute;
        background: #333;
        color: white;
        padding: 5px;
        border-radius: 3px;
        z-index: 100;
    }
</style>

<div class="min-h-screen bg-gray-950 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="row justify-content-center">
            <div class="col-md-10 lg:col-md-8">
                <div class="bg-gray-900 rounded-3xl shadow-2xl border border-gray-800 overflow-hidden">
                    <div class="p-8">
                        <h2 class="text-3xl font-bold font-serif text-white text-center mb-8">Edit Your Booking</h2>

                        <?php if ($error_message): ?>
                            <div
                                class="bg-red-900/20 border border-red-900/50 text-red-500 px-6 py-4 rounded-2xl flex items-center gap-3 mb-6">
                                <i class="fas fa-exclamation-circle"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <div
                            class="bg-yellow-900/20 border border-yellow-900/50 text-yellow-500 px-6 py-4 rounded-2xl flex items-center gap-3 mb-8">
                            <i class="fas fa-info-circle shrink-0"></i>
                            <p class="text-sm font-medium">When editing, please ensure the selected room and dates are
                                available.</p>
                        </div>

                        <form action="edit_booking.php?id=<?php echo $booking_id; ?>" method="POST" id="editBookingForm"
                            class="space-y-6">
                            <!-- Customer Name -->
                            <div>
                                <label for="customer_name"
                                    class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Your
                                    Name</label>
                                <input type="text" name="customer_name" id="customer_name"
                                    class="w-full px-6 py-4 bg-gray-800 border border-gray-700 text-white rounded-2xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-all"
                                    value="<?php echo htmlspecialchars($booking['customer_name']); ?>" required>
                            </div>

                            <!-- Select Room -->
                            <div>
                                <label for="room_id"
                                    class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Select
                                    Room</label>
                                <div class="relative">
                                    <select name="room_id" id="room_id"
                                        class="w-full px-6 py-4 bg-gray-800 border border-gray-700 text-white rounded-2xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-all appearance-none cursor-pointer"
                                        required>
                                        <?php
                                        mysqli_data_seek($room_result, 0);
                                        while ($room = mysqli_fetch_assoc($room_result)) {
                                            $selected = ($booking['room_id'] == $room['id']) ? 'selected' : '';
                                            echo "<option value='" . $room['id'] . "' " . $selected . " class='bg-gray-800'>"
                                                . htmlspecialchars($room['room_name'])
                                                . " - $" . number_format($room['price'], 2)
                                                . " per night</option>";
                                        }
                                        ?>
                                    </select>
                                    <div
                                        class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Check-in and Check-out Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="check_in_date"
                                        class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Check-in
                                        Date & Time</label>
                                    <div class="relative">
                                        <input type="text" name="check_in_date" id="check_in_date"
                                            class="w-full px-6 py-4 bg-gray-800 border border-gray-700 text-white rounded-2xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-all cursor-pointer"
                                            placeholder="Select date and time" data-min-date="<?php echo $today; ?>"
                                            data-enable-time="true"
                                            value="<?php echo isset($booking['check_in_date']) ? $booking['check_in_date'] : $booking['booking_date']; ?>"
                                            required>
                                        <div
                                            class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="check_out_date"
                                        class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Check-out
                                        Date & Time</label>
                                    <div class="relative">
                                        <input type="text" name="check_out_date" id="check_out_date"
                                            class="w-full px-6 py-4 bg-gray-800 border border-gray-700 text-white rounded-2xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-all cursor-pointer"
                                            placeholder="Select date and time" data-min-date="<?php echo $today; ?>"
                                            data-enable-time="true"
                                            value="<?php echo isset($booking['check_out_date']) ? $booking['check_out_date'] : date('Y-m-d', strtotime($booking['booking_date'] . ' +1 day')); ?>"
                                            required>
                                        <div
                                            class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="availability-message"
                                class="hidden px-6 py-4 rounded-2xl font-bold flex items-center gap-3"></div>

                            <div id="total-price"
                                class="hidden bg-gray-800 border border-gray-700 rounded-3xl p-8 space-y-4">
                                <!-- Price breakdown will be injected here -->
                            </div>

                            <div class="flex flex-col sm:flex-row gap-4 pt-4">
                                <a href="view_bookings.php"
                                    class="flex-1 px-8 py-4 bg-gray-800 text-white font-bold rounded-2xl border border-gray-700 hover:bg-gray-700 transition-all text-center">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                                </a>
                                <button type="submit" name="update_booking"
                                    class="flex-1 px-8 py-4 bg-yellow-600 text-white font-bold rounded-2xl hover:bg-yellow-700 transition-all shadow-lg hover:shadow-yellow-600/30 transform hover:-translate-y-1">
                                    <i class="fas fa-save me-2"></i>Update Booking
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('editBookingForm');
        const roomSelect = document.getElementById('room_id');
        const checkInDateInput = document.getElementById('check_in_date');
        const checkOutDateInput = document.getElementById('check_out_date');
        const messageDiv = document.getElementById('availability-message');
        const totalPriceDiv = document.getElementById('total-price');
        const priceValueSpan = document.getElementById('price-value');

        // Store room prices
        // Store room prices
        const roomData = {};
        <?php
        mysqli_data_seek($room_result, 0);
        while ($room = mysqli_fetch_assoc($room_result)) {
            $hourly = isset($room['price_per_hour']) && $room['price_per_hour'] > 0 ? $room['price_per_hour'] : ceil($room['price'] * 0.15);
            echo "roomData[" . $room['id'] . "] = { nightly: " . $room['price'] . ", hourly: " . $hourly . " };\n";
        }
        ?>

        // Initialize date pickers
        let checkInPicker;
        let checkOutPicker;
        let disabledDates = [];
        let currentBookingId = <?php echo $booking_id; ?>;

        function initDatePickers() {
            // Destroy existing pickers if they exist
            if (checkInPicker) {
                checkInPicker.destroy();
            }
            if (checkOutPicker) {
                checkOutPicker.destroy();
            }

            // Check-in date picker
            checkInPicker = flatpickr(checkInDateInput, {
                minDate: "today",
                disable: disabledDates,
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                defaultDate: checkInDateInput.value,
                onChange: function (selectedDates, dateStr) {
                    checkOutPicker.set('minDate', dateStr);
                    calculateTotalPrice();
                    checkAvailability();
                }
            });

            // Check-out date picker
            checkOutPicker = flatpickr(checkOutDateInput, {
                minDate: checkInDateInput.value || "today",
                disable: disabledDates,
                enableTime: true,
                dateFormat: "Y-m-d H:i",
                defaultDate: checkOutDateInput.value,
                onChange: function () {
                    calculateTotalPrice();
                    checkAvailability();
                }
            });
        }

        // Load booked dates for a room
        async function loadBookedDates(roomId) {
            if (!roomId) return;

            try {
                const response = await fetch(`../api/get_booked_dates.php?room_id=${roomId}`);
                const data = await response.json();

                disabledDates = [];

                // Process each booking period
                if (data.booked_periods && data.booked_periods.length > 0) {
                    data.booked_periods.forEach(period => {
                        disabledDates.push({
                            from: period.start,
                            to: period.end
                        });
                    });
                }

                // Reinitialize the date pickers with new disabled dates
                initDatePickers();

                // Check availability with new dates
                checkAvailability();

            } catch (error) {
                console.error('Error loading booked dates:', error);
            }
        }

        function calculateTotalPrice() {
            const roomId = roomSelect.value;
            const checkInDate = new Date(checkInDateInput.value);
            const checkOutDate = new Date(checkOutDateInput.value);

            if (!roomId || !checkInDateInput.value || !checkOutDateInput.value) {
                totalPriceDiv.classList.add('d-none');
                return;
            }

            // Calculate number of nights
            // Calculate duration in milliseconds
            const timeDiff = checkOutDate - checkInDate;
            const diffHours = timeDiff / (1000 * 3600); // exact float hours

            if (diffHours <= 0) {
                priceValueSpan.textContent = '$0.00';
                totalPriceDiv.classList.add('d-none');
                return;
            }

            if (roomData[roomId]) {
                let totalPrice = 0;
                let breakdownHtml = "";
                let rateText = "";

                // Helper for currency formatting
                const formatMoney = (amount) => '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                if (diffHours < 24) {
                    // Hourly Pricing
                    const hoursCharged = Math.max(1, Math.ceil(diffHours));
                    const hourlyRate = roomData[roomId].hourly;
                    totalPrice = hoursCharged * hourlyRate;

                    breakdownHtml = `
                        <div class="d-flex justify-content-between mb-2">
                            <span>Duration (${hoursCharged} hrs)</span>
                            <span>${hoursCharged} x ${formatMoney(hourlyRate)}/hr</span>
                        </div>
                        <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                            <span>Total</span>
                            <span>${formatMoney(totalPrice)}</span>
                        </div>
                    `;
                    rateText = ` (${hoursCharged} hours @ ${formatMoney(hourlyRate)}/hr)`;
                } else {
                    // Nightly + Hourly Overage
                    const nights = Math.floor(diffHours / 24);
                    const remainingHours = Math.ceil(diffHours % 24);
                    const nightlyRate = roomData[roomId].nightly;
                    const hourlyRate = roomData[roomId].hourly;

                    if (remainingHours > 0) {
                        const nightPrice = nights * nightlyRate;
                        const hourlyPrice = remainingHours * hourlyRate;

                        if (hourlyPrice >= nightlyRate) {
                            totalPrice = (nights + 1) * nightlyRate;

                            breakdownHtml = `
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Nights (${nights + 1})</span>
                                    <span>${nights + 1} x ${formatMoney(nightlyRate)}</span>
                                </div>
                                <div class="text-muted small mb-2 fst-italic">
                                    * Hourly overage capped at nightly rate.
                                </div>
                                <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>${formatMoney(totalPrice)}</span>
                                </div>
                             `;
                            rateText = ` (${nights + 1} nights @ ${formatMoney(nightlyRate)}/night)`;
                        } else {
                            totalPrice = nightPrice + hourlyPrice;
                            breakdownHtml = `
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Nights (${nights})</span>
                                    <span>${nights} x ${formatMoney(nightlyRate)}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Extra Time (${remainingHours} hrs)</span>
                                    <span>${remainingHours} x ${formatMoney(hourlyRate)}</span>
                                </div>
                                <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>${formatMoney(totalPrice)}</span>
                                </div>
                             `;
                            rateText = ` (${nights} nights + ${remainingHours} hours)`;
                        }
                    } else {
                        // Exact number of nights
                        const nightsToCharge = Math.max(1, nights);
                        totalPrice = nightsToCharge * nightlyRate;
                        breakdownHtml = `
                            <div class="d-flex justify-content-between mb-2">
                                <span>Nights (${nightsToCharge})</span>
                                <span>${nightsToCharge} x ${formatMoney(nightlyRate)}</span>
                            </div>
                            <div class="border-top pt-2 d-flex justify-content-between fw-bold">
                                <span>Total</span>
                                <span>${formatMoney(totalPrice)}</span>
                            </div>
                        `;
                        rateText = ` (${nightsToCharge} nights @ ${formatMoney(nightlyRate)}/night)`;
                    }
                }

                // Update the DOM manually since structure changed
                const priceContainer = document.getElementById('total-price');
                priceContainer.innerHTML = `<h3 class="text-xl font-bold font-serif text-white mb-6 flex items-center gap-2"><i class="fas fa-receipt text-yellow-600"></i>Price Breakdown</h3>` +
                    `<div class="space-y-3 text-gray-300">${breakdownHtml}</div>`;

                // priceValueSpan.textContent = '$' + totalPrice.toFixed(2) + rateText;
                totalPriceDiv.classList.remove('hidden');
            } else {
                totalPriceDiv.classList.add('hidden');
            }
        }

        async function checkAvailability() {
            if (!roomSelect.value || !checkInDateInput.value || !checkOutDateInput.value) return;

            try {
                const response = await fetch('../api/check_availability.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `room_id=${roomSelect.value}&check_in_date=${checkInDateInput.value}&check_out_date=${checkOutDateInput.value}&booking_id=${currentBookingId}`
                });

                const data = await response.json();

                messageDiv.classList.remove('hidden', 'bg-emerald-900/20', 'text-emerald-400', 'border-emerald-900/50', 'bg-red-900/20', 'text-red-500', 'border-red-900/50', 'border');

                if (data.available) {
                    messageDiv.classList.add('bg-emerald-900/20', 'text-emerald-400', 'border', 'border-emerald-900/50');
                    messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Room is available for the selected dates!';
                    messageDiv.classList.remove('hidden');
                    form.querySelector('button[type="submit"]').disabled = false;
                    form.querySelector('button[type="submit"]').classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    messageDiv.classList.add('bg-red-900/20', 'text-red-500', 'border', 'border-red-900/50');
                    messageDiv.innerHTML = '<i class="fas fa-times-circle"></i> Sorry, this room is already booked for these dates.';
                    messageDiv.classList.remove('hidden');
                    form.querySelector('button[type="submit"]').disabled = true;
                    form.querySelector('button[type="submit"]').classList.add('opacity-50', 'cursor-not-allowed');
                }
            } catch (error) {
                console.error('Error checking availability:', error);
            }
        }

        roomSelect.addEventListener('change', function () {
            loadBookedDates(this.value);
            calculateTotalPrice();
        });

        // Initialize date pickers on page load
        initDatePickers();

        // Load booked dates for initially selected room
        loadBookedDates(roomSelect.value);

        // Calculate total price on page load
        calculateTotalPrice();
    });
</script>

<?php include('../includes/footer.php'); ?>

<?php
// Flush the output buffer and send the content to the browser
ob_end_flush();
?>