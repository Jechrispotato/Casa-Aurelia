<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../includes/header.php');
include('../includes/security.php');

// Generate CSRF token
$csrf_token = generate_csrf_token();

// Prefill name/email for logged-in users
$prefill_name = '';
$prefill_email = '';
if (isset($_SESSION['user_id'])) {
    include_once('../includes/db.php');
    $uid = (int) $_SESSION['user_id'];
    $uqr = mysqli_query($conn, "SELECT username, email FROM users WHERE id = $uid LIMIT 1");
    if ($uqr && mysqli_num_rows($uqr) > 0) {
        $urow = mysqli_fetch_assoc($uqr);
        $prefill_name = !empty($urow['username']) ? $urow['username'] : '';
        $prefill_email = !empty($urow['email']) ? $urow['email'] : '';
    }
}

// Get error/success messages
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';

// Clear messages after retrieving
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!-- Custom Styles for specific animations and premium effects -->
<style>
    .parallax-hero {
        background-image: url('../images/dining.jpg');
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
    }

    .text-gold {
        color: #d4af37;
        background: linear-gradient(45deg, #d4af37, #f3e5ab, #d4af37);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .gold-border {
        border-color: #d4af37;
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .dark-glass {
        background: rgba(17, 24, 39, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .menu-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.8s ease-out;
    }

    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }

    /* Modal Layout Sync from view_rooms.php */
    #fullMenuModal .modal-dialog,
    #cocktailMenuModal .modal-dialog {
        max-width: calc(90vw - 100px);
        max-height: calc(110vh - 200px);
        margin: 90px auto;
        transition: all 0.3s ease;
    }

    #fullMenuModal .modal-content,
    #cocktailMenuModal .modal-content {
        height: 100%;
        max-height: calc(130vh - 200px);
    }

    /* Tablet Responsiveness */
    @media (max-width: 1024px) {

        #fullMenuModal .modal-dialog,
        #cocktailMenuModal .modal-dialog {
            max-width: calc(100vw - 60px);
            max-height: calc(100vh - 170px);
            margin: 80px auto;
        }

        #fullMenuModal .modal-content,
        #cocktailMenuModal .modal-content {
            max-height: calc(120vh - 170px);
        }
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {

        #fullMenuModal .modal-dialog,
        #cocktailMenuModal .modal-dialog {
            max-width: calc(90vw - 20px);
            max-height: calc(107vh - 180px);
            margin: 90px auto;
        }

        #fullMenuModal .modal-content,
        #cocktailMenuModal .modal-content {
            max-height: calc(120vh - 170px);
        }

        .modal-header {
            height: auto !important;
            padding: 1.5rem !important;
        }

        .modal-body {
            padding: 1.5rem !important;
        }
    }

    /* Custom Scrollbar for Modal Body */
    .modal-body::-webkit-scrollbar {
        width: 6px;
    }

    .modal-body::-webkit-scrollbar-track {
        background: #111827;
    }

    .modal-body::-webkit-scrollbar-thumb {
        background: #ca8a04;
        border-radius: 10px;
    }

    .modal-body::-webkit-scrollbar-thumb:hover {
        background: #facc15;
    }
</style>

<!-- Hero Section -->
<section class="parallax-hero h-screen min-h-[400px] flex items-center justify-center relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-gray-900"></div>
    <div class="text-center relative z-10 p-6 reveal active delay-100">
    <img src="../aurelia_assets/aurelia_main_logo_only_white.png" alt="Logo" class="w-16 md:w-24 mx-auto mb-1">
    
    <span class="block text-yellow-500 tracking-[0.3em] uppercase text-sm font-bold mb-4">Culinary Excellence</span>
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold text-white tracking-tight leading-tight">
            A Taste of <span class=" italic text-yellow-500">Luxury</span> <br>
        </h1>
        <p class="text-gray-200 text-lg md:text-xl max-w-2xl mx-auto font-light leading-relaxed mb-10">
            Experience a symphony of flavors in an atmosphere of refined elegance. From casual dining to exquisite
            banquets.
        </p>
        <a href="#reservation"
            class="inline-block px-8 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-semibold tracking-wide transition-all duration-300 shadow-lg hover:shadow-yellow-500/30 transform hover:-translate-y-1">
            Reserve Your Table
        </a>
    </div>

    <!-- Scroll Down Indicator -->
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce cursor-pointer">
        <a href="#dining-options" class="text-white opacity-70 hover:opacity-100 transition-opacity">
            <i class="fas fa-chevron-down text-2xl"></i>
        </a>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gray-900 text-white relative overflow-visible">
    <div class="fixed top-32 left-0 w-96 h-96 bg-yellow-500/10 
            rounded-full blur-3xl pointer-events-none z-0"></div>
    <div class="container mx-auto px-4 relative z-10">
        <div class="text-center mb-16 space-y-4">
            <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase reveal ">Dine with Us</h3>
            <h2 class="text-3xl md:text-7xl font-bold reveal">From the Taste of the World</h2>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-7xl mx-auto">
            <!-- Feature 1 -->
            <div
                class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
                <div
                    class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-utensils text-2xl"></i>
                </div>
                <h3 class="ameneties text-xl font-semibold mb-3">Master Chefs</h3>
                <p class="ameneties_description text-gray-400 text-sm leading-relaxed">Our world-renowned culinary experts craft every dish with precision and passion for an unrivaled taste.</p>
            </div>

            <!-- Feature 2 -->
            <div
                class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
                <div
                    class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-leaf text-2xl"></i>
                </div>
                <h3 class="ameneties text-xl font-semibold mb-3">Organic Ingredients</h3>
                <p class="text-gray-400 text-sm leading-relaxed">We source the finest seasonal produce directly from local organic farms to ensure the highest quality and freshness.</p>
            </div>

            <!-- Feature 3 -->
            <div
                class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
                <div
                    class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-wine-glass-alt text-2xl"></i>
                </div>
                <h3 class="ameneties text-xl font-semibold mb-3">Premium Pairings</h3>
                <p class="ameneties_description text-gray-400 text-sm leading-relaxed">Experience perfectly curated wine and beverage selections that complement the unique flavors of our signature menu.</p>
            </div>

            <!-- Feature 4 -->
            <div
                class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
                <div
                    class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <h3 class="ameneties text-xl font-semibold mb-3">Exclusive Ambiance</h3>
                <p class="ameneties_description text-gray-400 text-sm leading-relaxed">Dine in a sophisticated and elegant setting designed to elevate your overall culinary journey at Casa Aurelia.</p>
            </div>
        </div>
    </div>
</section>

<!-- Dining Options / Intro -->
<section id="dining-options" class="py-24 bg-gray-900">
    <div class="container mx-auto px-4 max-w-6xl">
        <div class="text-center mb-20 reveal">
            <span class="text-yellow-600 font-bold tracking-widest uppercase text-sm">Our Venues</span>
            <h2 class="text-4xl md:text-5xl font-serif mt-3 mb-6 text-white">Exquisite Settings</h2>
            <div class="w-24 h-1 bg-yellow-500 mx-auto"></div>
        </div>

        <!-- Restaurant Feature -->
        <div class="flex flex-col md:flex-row items-center gap-12 mb-24 reveal">
            <div class="w-full md:w-1/2 relative group">
                <div
                    class="absolute inset-0 bg-yellow-500 transform translate-x-4 translate-y-4 group-hover:translate-x-2 group-hover:translate-y-2 transition-transform duration-500">
                </div>
                <img src="../images/dining.jpg" alt="Aurelia Restaurant"
                    class="relative z-10 w-full h-[500px] object-cover shadow-lg grayscale group-hover:grayscale-0 transition-all duration-700">
            </div>
            <div class="w-full md:w-1/2 md:pl-10">
                <h3 class="text-3xl font-serif mb-4 text-white">The Aurelia Restaurant</h3>
                <p class="text-gray-400 leading-relaxed mb-6 font-light text-lg">
                    Our signature restaurant offers a blend of international cuisines made with locally-sourced,
                    seasonal ingredients.
                    Enjoy panoramic city views in a sophisticated setting suitable for any occasion.
                </p>
                <div class="bg-gray-800 p-6 border-l-4 border-yellow-500 mb-6 italic text-gray-400 font-serif">
                    "We believe in creating memorable dining experiences through a perfect blend of flavors,
                    presentation, and atmosphere."
                    <div class="text-right mt-2 text-sm not-italic font-bold text-white">- Executive Chef Michael
                        Laurent</div>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-gray-400 mb-8">
                    <span class="flex items-center gap-2"><i class="fas fa-clock text-yellow-500"></i> B:
                        6:30-10:30am</span>
                    <span class="flex items-center gap-2"><i class="fas fa-clock text-yellow-500"></i> L:
                        12:00-2:30pm</span>
                    <span class="flex items-center gap-2"><i class="fas fa-clock text-yellow-500"></i> D:
                        6:00-10:30pm</span>
                </div>
                <!-- Trigger Modal -->
                <button type="button"
                    class="px-8 py-3 border-2 border-white text-white font-semibold rounded-full hover:bg-white hover:text-gray-900 transition-all duration-300 transform hover:-translate-y-1"
                    data-bs-toggle="modal" data-bs-target="#fullMenuModal">
                    View Full Menu
                </button>
            </div>
        </div>

        <!-- Lounge Feature (Reversed) -->
        <div class="flex flex-col md:flex-row-reverse items-center gap-12 reveal">
            <div class="w-full md:w-1/2 relative group">
                <div
                    class="absolute inset-0 bg-gray-900 transform -translate-x-4 translate-y-4 group-hover:-translate-x-2 group-hover:translate-y-2 transition-transform duration-500">
                </div>
                <img src="../images/sky.jpg" alt="Skyline Lounge"
                    class="relative z-10 w-full h-[500px] object-cover shadow-lg grayscale group-hover:grayscale-0 transition-all duration-700">
            </div>
            <div class="w-full md:w-1/2 md:pr-10 text-right md:text-left">
                <div class="md:text-right">
                    <h3 class="text-3xl font-serif mb-4 text-white">The Skyline Lounge</h3>
                    <p class="text-gray-400 leading-relaxed mb-6 font-light text-lg">
                        Unwind in our sophisticated lounge with breathtaking views. Expert mixologists craft signature
                        cocktails,
                        complemented by an extensive wine list and artisanal light bites.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div
                        class="bg-gray-900 text-white p-4 text-center hover:bg-yellow-600 transition-colors cursor-default">
                        <i class="fas fa-wine-glass-alt mb-2 text-2xl text-yellow-500"></i>
                        <h4 class="font-bold">Fine Wines</h4>
                        <p class="text-xs opacity-70">Curated global selection</p>
                    </div>
                    <div
                        class="bg-gray-900 text-white p-4 text-center hover:bg-yellow-600 transition-colors cursor-default">
                        <i class="fas fa-cocktail mb-2 text-2xl text-yellow-500"></i>
                        <h4 class="font-bold">Craft Cocktails</h4>
                        <p class="text-xs opacity-70">Signature mixes</p>
                    </div>
                </div>

                <div class="md:text-right">
                    <button type="button"
                        class="px-8 py-3 border-2 border-white text-white font-semibold rounded-full hover:bg-white hover:text-gray-900 transition-all duration-300 transform hover:-translate-y-1"
                        data-bs-toggle="modal" data-bs-target="#cocktailMenuModal">
                        View Drink Menu
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Menu Preview Section -->
<section class="py-20 bg-gray-900 relative overflow-hidden text-white">
    <div class="absolute top-0 right-0 opacity-5 pointer-events-none">
        <i class="fas fa-utensils text-[300px]"></i>
    </div>
    <div class="container mx-auto px-4 max-w-6xl relative z-10">
        <div class="text-center mb-16 reveal">
            <h2 class="text-4xl font-serif text-white">Chef's Highlights</h2>
            <p class="text-gray-400 mt-2 font-light">Selections from our current seasonal menu</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Item 1 -->
            <div class="bg-gray-800 p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 reveal text-white">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="text-xl font-serif font-bold text-white">Pan-Seared Scallops</h4>
                    <span class="text-yellow-600 font-bold text-lg">$18</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-4">With cauliflower purée, crispy pancetta, and
                    truffle oil foam.</p>
                <div class="text-xs uppercase tracking-wider text-gray-500 font-bold">Appetizer</div>
            </div>

            <!-- Item 2 -->
            <div
                class="bg-gray-800 p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 transform md:-translate-y-4 reveal delay-100 text-white">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="text-xl font-serif font-bold text-white">Filet Mignon</h4>
                    <span class="text-yellow-600 font-bold text-lg">$42</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-4">8oz prime beef paired with roasted root vegetables
                    and a rich red wine reduction.</p>
                <span class="bg-yellow-900/50 text-yellow-400 text-xs px-2 py-1 rounded-full font-bold">Signature</span>
            </div>

            <!-- Item 3 -->
            <div
                class="bg-gray-800 p-8 shadow-lg hover:shadow-2xl transition-shadow duration-300 reveal delay-200 text-white">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="text-xl font-serif font-bold text-white">Chocolate Fondant</h4>
                    <span class="text-yellow-600 font-bold text-lg">$12</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed mb-4">Warm molten chocolate cake served with Madagascar
                    vanilla bean gelato.</p>
                <div class="text-xs uppercase tracking-wider text-gray-500 font-bold">Dessert</div>
            </div>
        </div>
    </div>
</section>

<!-- Private Dining -->
<section class="py-24 bg-gray-900 text-white relative">
    <div class="absolute inset-0 bg-[url('../images/wine.jpg')] bg-cover bg-center opacity-20"></div>
    <div class="container mx-auto px-4 relative z-10 text-center reveal">
        <h2 class="text-4xl md:text-5xl font-serif mb-6 text-gold">Private Dining & Events</h2>
        <p class="text-gray-300 max-w-2xl mx-auto text-lg mb-12 font-light">
            Host your special occasion in the exclusive Grand Room or the intimate Wine Cellar.
            Bespoke menus and dedicated service ensure an unforgettable experience.
        </p>
        <div class="flex justify-center gap-6">
            <div
                class="text-center p-6 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-users text-3xl text-yellow-500 mb-3"></i>
                <h4 class="font-bold">The Grand Room</h4>
                <p class="text-sm opacity-70">Up to 20 Guests</p>
            </div>
            <div
                class="text-center p-6 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg hover:bg-white/10 transition-colors">
                <i class="fas fa-wine-bottle text-3xl text-yellow-500 mb-3"></i>
                <h4 class="font-bold">The Wine Cellar</h4>
                <p class="text-sm opacity-70">Up to 12 Guests</p>
            </div>
        </div>
    </div>
</section>

<!-- Reservation Section -->
<section id="reservation" class="py-24 bg-gray-900">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="dark-glass p-8 md:p-12 rounded-3xl shadow-2xl reveal">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-serif text-white font-bold">Make a Reservation</h2>
                <p class="text-gray-400 mt-2">Secure your table at The Aurelia</p>
            </div>

            <?php if ($error_message): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-3 border border-red-200">
                    <i class="fas fa-exclamation-circle text-xl"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3 border border-green-200">
                    <i class="fas fa-check-circle text-xl"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="../reservations/process/process_dining_reservation.php" method="POST" id="reservationForm"
                class="space-y-6">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Personal Info -->
                    <div class="space-y-2">
                        <label for="name" class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Full
                            Name</label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                            <input type="text" id="name" name="name" required
                                value="<?php echo htmlspecialchars($prefill_name); ?>"
                                class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label for="email" class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Email
                            Address</label>
                        <div class="relative">
                            <i
                                class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                            <input type="email" id="email" name="email" required
                                value="<?php echo htmlspecialchars($prefill_email); ?>"
                                class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all">
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="space-y-2">
                        <label for="date"
                            class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Date</label>
                        <input type="date" id="date" name="date" required min="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all cursor-pointer">
                    </div>
                    <div class="space-y-2">
                        <label for="time"
                            class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Time</label>
                        <div class="relative">
                            <i
                                class="fas fa-clock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                            <input type="time" id="time" name="time" required
                                class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all cursor-pointer">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="guests" class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Number
                            of Guests</label>
                        <select id="guests" name="guests" required
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all appearance-none">
                            <option value="">Select Guests</option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> Person<?php echo $i > 1 ? 's' : ''; ?>
                                </option>
                            <?php endfor; ?>
                            <option value="12">12 People</option>
                            <option value="15">15 People</option>
                            <option value="20">20 People (Max)</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label for="venue" class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Venue
                            Preference</label>
                        <select id="venue" name="venue" required
                            class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all appearance-none">
                            <option value="">Select Venue</option>
                            <option value="restaurant">The Aurelia Restaurant</option>
                            <option value="lounge">The Skyline Lounge</option>
                            <option value="grand_room">The Grand Room (Private)</option>
                            <option value="wine_cellar">The Wine Cellar (Private)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="special-requests"
                        class="text-xs font-bold text-gray-400 uppercase tracking-wide ml-1">Special Requests</label>
                    <textarea id="special-requests" name="special-requests" rows="3"
                        placeholder="Dietary restrictions, allergies, or special occasion notes..."
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:border-yellow-500 focus:ring-2 focus:ring-yellow-900 outline-none transition-all"></textarea>
                </div>

                <div class="pt-4 text-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit"
                            class="w-full md:w-auto px-10 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1">
                            Confirm Reservation
                        </button>
                    <?php else: ?>
                        <a href="../auth/login.php?redirect=pages/dining.php&notify=please_login"
                            class="inline-block w-full md:w-auto px-10 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1">
                            Login to Reserve
                        </a>
                        <p class="mt-4 text-sm text-gray-400">You must be logged in to make a reservation.</p>
                    <?php endif; ?>
                </div>

            </form>
        </div>
    </div>
</section>

<!-- Full Menu Modal (Inline) -->
<div class="modal fade" id="fullMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content overflow-hidden border-0 rounded-3xl shadow-2xl bg-gray-900 text-white">
            <div class="modal-header border-b border-gray-800 py-6">
                <h5 class="modal-title font-serif text-3xl text-gold w-full text-center">Full Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-gray-900">
                <div class="container mx-auto p-8">
                    <!-- Sections -->
                    <div class="mb-12">
                        <h3 class="text-2xl font-serif text-yellow-600 mb-6 border-b pb-2">Appetizers</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php
                            $appetizers = [
                                ['name' => 'Pan-Seared Scallops', 'price' => 18, 'desc' => 'With cauliflower purée, crispy pancetta, and truffle oil', 'img' => '../DiningImage/scallops.jpg'],
                                ['name' => 'Artisanal Cheese Platter', 'price' => 22, 'desc' => 'Selection of fine cheeses, honeycomb, nuts, and artisanal bread', 'img' => '../DiningImage/artisanal.jpg'],
                                ['name' => 'Wild Mushroom Risotto', 'price' => 16, 'desc' => 'Creamy Arborio rice with seasonal wild mushrooms and Parmesan', 'img' => '../DiningImage/mushroom.jpg']
                            ];
                            foreach ($appetizers as $item) {
                                echo '
                                <div class="bg-gray-800 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-700">
                                    <div class="h-48 bg-gray-700 overflow-hidden relative">
                                        <img src="' . $item['img'] . '" alt="' . $item['name'] . '" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div class="p-5">
                                        <div class="flex justify-between items-baseline mb-2">
                                            <h4 class="font-serif font-bold text-white">' . $item['name'] . '</h4>
                                            <span class="text-yellow-600 font-bold">$' . $item['price'] . '</span>
                                        </div>
                                        <p class="text-sm text-gray-400">' . $item['desc'] . '</p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>

                    <div class="mb-12">
                        <h3 class="text-2xl font-serif text-yellow-600 mb-6 border-b pb-2">Main Courses</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php
                            $mains = [
                                ['name' => 'Filet Mignon', 'price' => 42, 'desc' => '8oz prime beef with roasted vegetables and red wine reduction', 'img' => '../DiningImage/filet.jpg'],
                                ['name' => 'Pan-Seared Sea Bass', 'price' => 38, 'desc' => 'With saffron risotto, asparagus, and lemon butter sauce', 'img' => '../DiningImage/bass.jpg'],
                                ['name' => 'Truffle Tagliatelle', 'price' => 32, 'desc' => 'Handmade pasta with black truffle, wild mushrooms, and Parmesan', 'img' => '../DiningImage/truffle.jpg']
                            ];
                            foreach ($mains as $item) {
                                echo '
                                <div class="bg-gray-800 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-700">
                                    <div class="h-48 bg-gray-700 overflow-hidden relative">
                                        <img src="' . $item['img'] . '" alt="' . $item['name'] . '" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div class="p-5">
                                        <div class="flex justify-between items-baseline mb-2">
                                            <h4 class="font-serif font-bold text-white">' . $item['name'] . '</h4>
                                            <span class="text-yellow-600 font-bold">$' . $item['price'] . '</span>
                                        </div>
                                        <p class="text-sm text-gray-400">' . $item['desc'] . '</p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="mb-12">
                        <h3 class="text-2xl font-serif text-yellow-600 mb-6 border-b pb-2">Desserts</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php
                            $desserts = [
                                ['name' => 'Chocolate Fondant', 'price' => 12, 'desc' => 'Warm chocolate cake with vanilla gelato', 'img' => '../DiningImage/fondant.jpg'],
                                ['name' => 'Lemon Tart', 'price' => 10, 'desc' => 'Citrus tart with raspberry coulis', 'img' => '../DiningImage/tart.jpg'],
                                ['name' => 'Cheesecake', 'price' => 11, 'desc' => 'Classic New York style with seasonal compote', 'img' => '../DiningImage/cheesecake.jpg']
                            ];
                            foreach ($desserts as $item) {
                                echo '
                                <div class="bg-gray-800 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all border border-gray-700">
                                    <div class="h-48 bg-gray-700 overflow-hidden relative">
                                        <img src="' . $item['img'] . '" alt="' . $item['name'] . '" class="w-full h-full object-cover transform hover:scale-110 transition-transform duration-500">
                                    </div>
                                    <div class="p-5">
                                        <div class="flex justify-between items-baseline mb-2">
                                            <h4 class="font-serif font-bold text-white">' . $item['name'] . '</h4>
                                            <span class="text-yellow-600 font-bold">$' . $item['price'] . '</span>
                                        </div>
                                        <p class="text-sm text-gray-400">' . $item['desc'] . '</p>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-gray-900 border-t border-gray-800">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close Menu</button>
            </div>
        </div>
    </div>
</div>

<!-- Cocktail Menu Modal (Inline) -->
<div class="modal fade" id="cocktailMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content overflow-hidden border-0 rounded-3xl shadow-2xl bg-gray-900 text-white">
            <div class="modal-header border-b border-gray-800 py-6">
                <h5 class="modal-title font-serif text-3xl text-gold w-full text-center">Cocktail Collection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-gray-900">
                <div class="container mx-auto p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <?php
                        $cocktails = [
                            ['name' => 'Aurelia Sunset', 'price' => 16, 'desc' => 'Vodka, elderflower, passion fruit, and a hint of sunset', 'img' => '../DiningImage/sunset.jpg'],
                            ['name' => 'Golden Whisper', 'price' => 18, 'desc' => 'Premium whiskey, honey, and aromatic bitters with a citrus twist', 'img' => '../DiningImage/golden.jpg'],
                            ['name' => 'Skyline Martini', 'price' => 15, 'desc' => 'Gin, dry vermouth, and a perfect blend of botanicals', 'img' => '../DiningImage/martini.jpg'],
                            ['name' => 'Botanical Gin Fizz', 'price' => 14, 'desc' => 'Infused gin with fresh herbs, lemon, and sparkling water', 'img' => '../DiningImage/gin.jpg'],
                            ['name' => 'Mojito', 'price' => 13, 'desc' => 'Rum, fresh mint, lime, sugar, and soda water', 'img' => '../DiningImage/mohito.jpg'],
                            ['name' => 'Margarita', 'price' => 14, 'desc' => 'Tequila, lime juice, and orange liqueur with a salt rim', 'img' => '../DiningImage/margarita.jpg']
                        ];
                        foreach ($cocktails as $item) {
                            echo '
                            <div class="bg-gray-800 rounded-lg overflow-hidden border border-gray-700 hover:border-yellow-500 transition-colors group">
                                <div class="h-64 overflow-hidden relative">
                                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent z-10"></div>
                                    <img src="' . $item['img'] . '" alt="' . $item['name'] . '" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                                </div>
                                <div class="p-6 relative z-20 -mt-10">
                                    <div class="flex justify-between items-baseline mb-2">
                                        <h4 class="font-serif font-bold text-lg text-white group-hover:text-yellow-400 transition-colors">' . $item['name'] . '</h4>
                                        <span class="text-yellow-500 font-bold">$' . $item['price'] . '</span>
                                    </div>
                                    <p class="text-sm text-gray-400">' . $item['desc'] . '</p>
                                </div>
                            </div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-t border-gray-800">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close Menu</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Move modals to body to ensure they display correctly (Bootstrap fixed position vs parent transform)
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => document.body.appendChild(modal));

        // Validation logic for date
        const dateInput = document.getElementById('date');
        if (dateInput) {
            dateInput.min = new Date().toISOString().split('T')[0];
        }

        // Simple Reveal Animation on Scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, {
            threshold: 0.1
        });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Auto-scroll to error if present
        <?php if ($error_message): ?>
            window.location.hash = 'reservation';
        <?php endif; ?>
    });
</script>

<?php include('../includes/footer.php'); ?>