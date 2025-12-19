<?php
session_start();
include('header.php');

// Redirect if no success message (prevents direct access)
if (!isset($_SESSION['success'])) {
    header('Location: view_rooms.php');
    exit;
}

$success_message = $_SESSION['success'];
unset($_SESSION['success']); // Clear the message after displaying
?>

<div class="min-h-[80vh] bg-gray-950 flex items-center py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="row justify-content-center">
            <div class="col-md-10 lg:col-md-8">
                <div
                    class="bg-gray-900 rounded-3xl border border-gray-800 shadow-2xl overflow-hidden transform animate-fade-in-up">
                    <div class="p-10 text-center">
                        <div class="mb-8">
                            <div
                                class="inline-flex items-center justify-center w-24 h-24 bg-emerald-900/20 rounded-full mb-4 border border-emerald-900/50">
                                <i class="fas fa-check text-emerald-500" style="font-size: 3rem;"></i>
                            </div>
                        </div>

                        <h2 class="text-4xl font-bold font-serif text-white mb-6">Booking Confirmed!</h2>

                        <div
                            class="bg-emerald-900/10 border border-emerald-900/30 text-emerald-400 p-6 rounded-2xl mb-8 leading-relaxed">
                            <p class="text-lg font-medium"><?php echo htmlspecialchars($success_message); ?></p>
                        </div>

                        <p class="text-gray-400 mb-10 text-lg">Thank you for choosing Casa Aurelia. We look forward to
                            welcoming you to our sanctuary of luxury and comfort.</p>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="view_bookings.php"
                                class="px-8 py-4 bg-yellow-600 text-white font-bold rounded-2xl hover:bg-yellow-700 transition-all shadow-lg hover:shadow-yellow-600/30 transform hover:-translate-y-1">
                                <i class="fas fa-list me-2"></i>My Reservations
                            </a>
                            <a href="view_rooms.php"
                                class="px-8 py-4 bg-gray-800 text-white font-bold rounded-2xl border border-gray-700 hover:bg-gray-700 transition-all transform hover:-translate-y-1">
                                <i class="fas fa-hotel me-2"></i>Browse Collection
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>