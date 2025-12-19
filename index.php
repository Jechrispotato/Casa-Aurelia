<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<div class="relative h-screen min-h-[400px] flex items-center justify-center overflow-hidden ">
    <!-- Background Image with Parallax Effect -->
    <div class="absolute inset-0 z-0">
        <img src="images/background.jpg" alt="Luxury Hotel"
            class=" bg_index w-full h-full object-cover transform scale-105 animate-slow-zoom">
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/40 to-gray-900"></div>
    </div>

    <!-- Hero Content -->
    <div class="text-center px-4 max-w-5xl mx-auto space-y-8 animate-fade-in-up">
        <div class="space-y-4">
            <img src="aurelia_assets/aurelia_main_logo_only_white.png" alt="Logo" class="w-16 md:w-24 mx-auto mb-1">
            <h2 class="text-yellow-500 font-medium tracking-[0.2em] text-sm md:text-base uppercase animate-slide-down">
                Welcome to The Casa Aurelia</h2>
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold text-white tracking-tight leading-tight">
                Experience <span class=" italic text-yellow-500">Luxury</span> <br> & Comfort
            </h1>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-8">
            <a href="booking/add_booking.php"
                class="px-8 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-semibold tracking-wide transition-all duration-300 shadow-lg hover:shadow-yellow-500/30 transform hover:-translate-y-1">
                Book Your Stay
            </a>
            <a href="pages/view_rooms.php"
                class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 rounded-full font-semibold tracking-wide transition-all duration-300 transform hover:-translate-y-1">
                View Suites
            </a>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
        <i class="fas fa-chevron-down text-white/50 text-2xl"></i>
    </div>
</div>

<!-- Features Section -->
<section class="py-20 bg-gray-900 text-white relative overflow-visible">

    <div class="text-center mb-16 space-y-4">
        <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase reveal ">Why Choose Us</h3>
        <h2 class="text-3xl md:text-7xl font-bold reveal">World Class Amenities</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 max-w-6xl mx-auto">

        <!-- Feature 1 -->
        <div
            class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
            <div
                class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                <i class="fas fa-wifi text-2xl"></i>
            </div>
            <h3 class="ameneties text-xl font-semibold mb-3">High-Speed Wi-Fi</h3>
            <p class="ameneties_description text-gray-400 text-sm leading-relaxed">Stay seamlessly connected with our
                complimentary premium internet access throughout the property.</p>
        </div>

        <!-- Feature 2 -->
        <div
            class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
            <div
                class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                <i class="fas fa-parking text-2xl"></i>
            </div>
            <h3 class="ameneties text-xl font-semibold mb-3">Valet Parking</h3>
            <p class="text-gray-400 text-sm leading-relaxed">Secure and convenient parking services available 24/7 for
                all our valued guests.</p>
        </div>

        <!-- Feature 3 -->
        <div
            class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
            <div
                class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                <i class="fas fa-utensils text-2xl"></i>
            </div>
            <h3 class="ameneties text-xl font-semibold mb-3">Gourmet Dining</h3>
            <p class="ameneties_description text-gray-400 text-sm leading-relaxed">Indulge in exquisite culinary
                masterpieces at our award-winning fine dining restaurants.</p>
        </div>

        <!-- Feature 4 -->
        <div
            class="ameneties_box group p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 hover:bg-gray-800 hover:border-yellow-500/30 transition-all duration-300 hover:-translate-y-2 reveal">
            <div
                class="w-14 h-14 rounded-full bg-gray-700/50 flex items-center justify-center mb-6 group-hover:bg-yellow-500/20 group-hover:text-yellow-500 transition-colors duration-300">
                <i class="fas fa-spa text-2xl"></i>
            </div>
            <h3 class="ameneties text-xl font-semibold mb-3">Luxury Spa</h3>
            <p class="ameneties_description text-gray-400 text-sm leading-relaxed">Rejuvenate your senses with our
                world-class treatments and wellness facilities.</p>
        </div>
    </div>
    </div>
</section>

<section class="autoblur-section">
    <div class="fixed bottom-32 right-0 w-96 h-96 bg-blue-500/10 
            rounded-full blur-3xl pointer-events-none z-0">
    </div>
    <div class="autoblur italic reveal">NUMBER 1</div>
    <div class="autoblur reveal">5-STAR LUXURY</div>
    <div class="autoblur reveal">HOTEL IN THE</div>
    <div class="autoblur reveal">PHILIPPINES</div>
    <div class="autoblur reveal">
        <h6>© Forbes Asia Metropolitan Awards [2025].</h6>
    </div>
</section>


<!-- Experience Section (Alternate Grid) -->
<section class="experience py-20 bg-gray-900 text-white">
    <div class="container mx-auto px-4">
        <!-- Dining -->
        <div class="flex flex-col lg:flex-row items-center gap-12 mb-20 appear autoblur_image">
            <div class="lg:w-1/2 relative group">
                <div
                    class="absolute -inset-2 bg-yellow-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
                <img src="images/dining.jpg" alt="Fine Dining"
                    class="relative rounded-2xl shadow-2xl w-full object-cover h-[400px] transform transition-transform duration-500 group-hover:scale-[1.01] slide-in-left">
            </div>
            <div class="lg:w-1/2 space-y-6 text-left slide-in-right">
                <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase reveal ">Culinary Excellence
                </h3>
                <h2 class="text-4xl font-bold reveal">Exquisite Dining Experiences</h2>
                <p class="text-gray-400 text-lg leading-relaxed reveal">
                    Savor the authentic flavors prepared by our world-renowned chefs. Whether it's a romantic dinner or
                    a casual brunch, our restaurants offer an ambiance that perfectly complements the exquisite cuisine.
                </p>
                <ul class="space-y-3 text-gray-300">
                    <li class="flex items-center gap-3 reveal"><i class="fas fa-check text-yellow-500"></i>
                        Farm-to-table ingredients</li>
                    <li class="flex items-center gap-3 reveal"><i class="fas fa-check text-yellow-500"></i>
                        Award-winning wine list</li>
                    <li class="flex items-center gap-3"><i class="fas fa-check text-yellow-500"></i> Private dining
                        options</li>
                </ul>
                <a href="pages/dining.php"
                    class="custom-btn-hover inline-block px-8 py-3 mt-4 border border-white text-white rounded-full hover:bg-white transition-all duration-300 reveal">
                    Explore Dining
                </a>
            </div>
        </div>

        <!-- Spa -->
        <div class="flex flex-col lg:flex-row-reverse items-center gap-12 autoblur_image">
            <div class="fixed bottom-32 right-0 w-96 h-96 bg-blue-500/20 
            rounded-full blur-3xl pointer-events-none z-0"></div>

            <div class="lg:w-1/2 relative group">
                <div
                    class="absolute -inset-2 bg-blue-500/20 rounded-2xl blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
                <img src="images/spa.jpg" alt="Aurelia Spa"
                    class="relative rounded-2xl shadow-2xl w-full object-cover h-[400px] transform transition-transform duration-500 group-hover:scale-[1.01] slide-in-right">
            </div>
            <div
                class="lg:w-1/2 space-y-6 text-left lg:text-right flex flex-col items-start lg:items-end slide-in-left">
                <h3 class="text-yellow-500 font-medium tracking-widest text-sm uppercase reveal">Wellness & Serenity
                </h3>
                <h2 class="text-4xl font-bold reveal">Rejuvenate Your Senses</h2>
                <p class="text-gray-400 text-lg leading-relaxed reveal">
                    Escape to a sanctuary of peace in our premium spa. From therapeutic massages to revitalizing
                    facials, every treatment is designed to restore balance to your body and mind.
                </p>
                <div class="flex gap-4">
                    <span class="px-4 py-2 bg-gray-800 rounded-lg text-sm text-gray-300 reveal">Massage Therapy</span>
                    <span class="px-4 py-2 bg-gray-800 rounded-lg text-sm text-gray-300 reveal">Hydrotherapy</span>
                    <span class="px-4 py-2 bg-gray-800 rounded-lg text-sm text-gray-300 reveal">Yoga</span>
                </div>
                <a href="pages/spa.php"
                    class="custom-btn-hover inline-block px-8 py-3 mt-4 border border-white text-white rounded-full hover:bg-white transition-all duration-300">
                    Discover Spa
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Rooms Section -->
<section class="accomodation py-24 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col items-center text-center mb-16 gap-6">
            <div class="space-y-4">
                <h3 class="text-yellow-600 font-medium tracking-widest text-sm uppercase reveal">
                    Accommodations
                </h3>
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 reveal">
                    Our Luxurious Rooms
                </h2>
            </div>

            <a href="pages/view_rooms.php"
                class="Accomodation_button backdrop-blur-sm bg-gray-700/50 border border-gray-700/50 text-white px-4 py-2 rounded-md flex items-center justify-center gap-2 hover:bg-yellow-500/20 hover:text-yellow-500 transition-colors duration-300 reveal">
                VIEW ALL ROOMS
                <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
            </a>

        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Room 1 -->
            <div
                class="group bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 slide-in-center-left">
                <div class="relative h-72 overflow-hidden">
                    <div
                        class="room_price absolute top-4 right-4 z-10  font-bold px-4 py-2 rounded-full shadow-lg group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        ₱1,500<span
                            class="room_price text-sm font-normal text-gray-600 group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">/night</span>
                    </div>
                    <img src="images/penthouse.jpg" alt="Penthouse"
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="room_title text-2xl font-bold text-gray-900 mb-2">PENTHOUSE SUITE</h3>
                    <p class="text-gray-600 mb-6 line-clamp-2">Experience the pinnacle of luxury with breathtaking city
                        views and exclusive amenities.</p>
                    <div
                        class="room_details flex items-center gap-4 text-sm text-gray-500 mb-6 border-t border-gray-100 pt-6">
                        <span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-600"></i> King Bed</span>
                        <span class="flex items-center gap-2"><i class="fas fa-ruler-combined text-yellow-600"></i> 75
                            m²</span>
                        <span class="flex items-center gap-2"><i class="fas fa-mountain text-yellow-600"></i> City
                            View</span>
                    </div>
                    <a href="pages/view_rooms.php"
                        class="room_button block w-full text-center py-3 border-2 border-gray-900 text-gray-900 font-semibold rounded-xl group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Room 2 -->
            <div
                class="group bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 reveal">
                <div class="relative h-72 overflow-hidden">
                    <div
                        class="room_price absolute top-4 right-4 z-10  font-bold px-4 py-2 rounded-full shadow-lg group-hover:bg-gray-900 group-hover:text-white transition-all duration-300 group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        ₱350<span
                            class="room_price text-sm font-normal text-gray-600 group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">/night</span>
                    </div>
                    <img src="images/bridal.jpg" alt="Bridal Suite"
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="room_title text-2xl font-bold text-gray-900 mb-2">BRIDAL SUITE</h3>
                    <p class="text-gray-600 mb-6 line-clamp-2">Celebrate love in our elegantly designed Bridal Suite
                        with romantic touches.</p>
                    <div
                        class="room_details flex items-center gap-4 text-sm text-gray-500 mb-6 border-t border-gray-100 pt-6">
                        <span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-600"></i> Queen
                            Bed</span>
                        <span class="flex items-center gap-2"><i class="fas fa-ruler-combined text-yellow-600"></i> 55
                            m²</span>
                        <span class="flex items-center gap-2"><i class="fas fa-glass-cheers text-yellow-600"></i>
                            Lounge</span>
                    </div>
                    <a href="pages/view_rooms.php"
                        class="room_button block w-full text-center py-3 border-2 border-gray-900 text-gray-900 font-semibold rounded-xl group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        View Details
                    </a>
                </div>
            </div>

            <!-- Room 3 -->
            <div
                class="group bg-white rounded-3xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 reveal slide-in-center-right">
                <div class="relative h-72 overflow-hidden">
                    <div
                        class="room_price absolute top-4 right-4 z-10  font-bold px-4 py-2 rounded-full shadow-lg group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        ₱400<span
                            class="room_price text-sm font-normal text-gray-600 group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">/night</span>
                    </div>
                    <img src="images/honeymoon.jpg" alt="Honeymoon Suite"
                        class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    </div>
                </div>
                <div class="p-8">
                    <h3 class="room_title text-2xl font-bold text-gray-900 mb-2">HONEYMOON SUITE</h3>
                    <p class="text-gray-600 mb-6 line-clamp-2">Private balcony and jacuzzi for the ultimate romantic
                        getaway.</p>
                    <div
                        class="room_details flex items-center gap-4 text-sm text-gray-500 mb-6 border-t border-gray-100 pt-6">
                        <span class="flex items-center gap-2"><i class="fas fa-bed text-yellow-600"></i> King Bed</span>
                        <span class="flex items-center gap-2"><i class="fas fa-ruler-combined text-yellow-600"></i> 60
                            m²</span>
                        <span class="flex items-center gap-2"><i class="fas fa-hot-tub text-yellow-600"></i>
                            Jacuzzi</span>
                    </div>
                    <a href="pages/view_rooms.php"
                        class="room_button block w-full text-center py-3 border-2 border-gray-900 text-gray-900 font-semibold rounded-xl group-hover:bg-gray-900 group-hover:text-white transition-all duration-300">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="guest py-24 bg-gray-50 from-gray-50 to-white bg-gradient-to-b">
    <div class="container mx-auto px-4 text-center">
        <h3 class="text-yellow-600 font-medium tracking-widest text-sm uppercase mb-4 reveal">
            Guest Reviews
        </h3>
        <h2 class="guest_title text-5xl font-bold text-gray-900 mb-16 reveal">
            What Our Guests Say
        </h2>

        <!-- Carousel -->
        <div class="carousel reveal">
            <div class="carousel-track">

                <div class="carousel-group">
                    <!-- Review 1 -->
                    <div class="review-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 relative">
                        <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 italic mb-6">
                            "Amazing experience! The staff was incredibly helpful and the room exceeded our expectations
                            in every way."
                        </p>
                        <div class="border-t border-gray-100 pt-6">
                            <div class="font-bold text-gray-900">Kurt Umali</div>
                            <div class="text-sm text-gray-500">Business Traveler</div>
                        </div>
                    </div>

                    <!-- Review 2 -->
                    <div class="review-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 relative">
                        <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 italic mb-6">
                            "Perfect location, luxurious rooms, and outstanding service. The dining experience was
                            absolute perfection."
                        </p>
                        <div class="border-t border-gray-100 pt-6">
                            <div class="font-bold text-gray-900">Kart Mendoza</div>
                            <div class="text-sm text-gray-500">Vacation Guest</div>
                        </div>
                    </div>

                    <!-- Review 3 -->
                    <div class="review-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 relative">
                        <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 italic mb-6">
                            "The attention to detail and customer service made our stay truly memorable. The spa is a
                            must-try!"
                        </p>
                        <div class="border-t border-gray-100 pt-6">
                            <div class="font-bold text-gray-900">Ahron Valenzuela</div>
                            <div class="text-sm text-gray-500">Honeymoon Couple</div>
                        </div>
                    </div>
                </div>

                <div class="carousel-group">
                    <!-- Review 1 -->
                    <div class="review-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 relative">
                        <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 italic mb-6">
                            "Amazing experience! The staff was incredibly helpful and the room exceeded our expectations
                            in every way."
                        </p>
                        <div class="border-t border-gray-100 pt-6">
                            <div class="font-bold text-gray-900">Kurt Umali</div>
                            <div class="text-sm text-gray-500">Business Traveler</div>
                        </div>
                    </div>

                    <!-- Review 2 -->
                    <div class="review-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 relative">
                        <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 italic mb-6">
                            "Perfect location, luxurious rooms, and outstanding service. The dining experience was
                            absolute perfection."
                        </p>
                        <div class="border-t border-gray-100 pt-6">
                            <div class="font-bold text-gray-900">Kart Mendoza</div>
                            <div class="text-sm text-gray-500">Vacation Guest</div>
                        </div>
                    </div>

                    <!-- Review 3 -->
                    <div class="review-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 relative">
                        <div class="flex justify-center gap-1 text-yellow-400 mb-6 mt-4">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 italic mb-6">
                            "The attention to detail and customer service made our stay truly memorable. The spa is a
                            must-try!"
                        </p>
                        <div class="border-t border-gray-100 pt-6">
                            <div class="font-bold text-gray-900">Ahron Valenzuela</div>
                            <div class="text-sm text-gray-500">Honeymoon Couple</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>
</section>


<!-- Call to Action Banner -->
<section class="py-20 relative overflow-hidden flex items-center justify-center">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="images/background.jpg" alt="Background"
            class="w-full h-full object-cover filter brightness-[0.3] absolute inset-0">
        <!-- Gradient overlay from top to center -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#111827] to-transparent" style="height: 50%; top: 0;"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <img src="aurelia_assets/aurelia_main_logo_only_white.png" alt="Logo" class="w-32 md:w-48 mx-auto mb-1">
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-6 reveal">Ready for an Unforgettable Stay?</h2>
        <p class="text-gray-300 text-lg mb-10">Book your luxury escape today and experience the Grand Aurelia
            difference.</p>
        <a href="booking/add_booking.php"
            class="inline-block px-10 py-4 bg-yellow-600 hover:bg-yellow-700 text-white rounded-full font-bold text-lg shadow-lg hover:shadow-yellow-500/50 transition-all duration-300 transform hover:-translate-y-1">
            Book Now
        </a>
    </div>
</section>

<style>
    @font-face {
        font-family: 'Velista';
        src: url('aurelia_assets/velista.otf') format('opentype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }

    @keyframes slow-zoom {
        0% {
            transform: scale(1);
        }

        100% {
            transform: scale(1.1);
        }
    }

    .animate-slow-zoom {
        animation: slow-zoom 20s infinite alternate;
    }

    @keyframes slide-down {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .animate-slide-down {
        animation: slide-down 1s ease-out forwards;
    }

    @keyframes fade-in-up {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 1s ease-out forwards 0.5s;
        opacity: 0;
    }

    .custom-btn-hover:hover {
        color: #000000 !important;
    }

    .autoblur-section {
        display: grid;
        gap: 10px;
        text-align: center;
        padding: 40px 20px;
        background: #111827;
        backdrop-filter: blur(10px);
    }

    .autoblur {
        font-size: clamp(2rem, 10vw, 6rem);
        font-weight: bold;
        font-family: 'Velista', sans-serif;
        line-height: normal;
        color: #ffffff;
        animation: autoblurAnimation linear both;
        animation-timeline: view();
    }

    .autoblur_image {
        animation: autoblurAnimation2 linear both;
        animation-timeline: view();
    }

    .autoblur h6 {
        font-size: clamp(1rem, 3vw, 1rem);
        font-weight: bold;
        font-family: 'Velista', sans-serif;
        line-height: normal;
        color: #ffffffff;
        animation: autoblurAnimation linear both;
        animation-timeline: view();
    }

    @keyframes autoblurAnimation {
        0% {
            filter: blur(30px);
        }

        45%,
        55% {
            filter: blur(0px);
        }

        100% {
            filter: blur(30px);
        }
    }

    @keyframes autoblurAnimation2 {
        0% {
            filter: blur(10px);
            opacity: -1;
            scale: .8;
        }

        45%,
        55% {
            filter: blur(0px);
            opacity: 1;
            scale: 1;
        }

        100% {
            filter: blur(10px);
            opacity: 0;
            scale: .9;
        }
    }

    .slide-in-left {
        animation: slide_animation linear both;
        animation-timeline: view();
        transition: all 0.8s ease-out;
    }

    .slide-in-right {
        animation: slide_animation2 linear both;
        animation-timeline: view();
        transition: all 0.8s ease-out;
    }


    @keyframes slide_animation {
        0% {
            opacity: 0;
            transform: translateX(-400px);
        }

        45%,
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slide_animation2 {
        0% {
            opacity: 0;
            transform: translateX(400px);
        }

        45%,
        100% {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .slide-in-center-left {
        animation: slide_center1 linear both;
        animation-timeline: view();
        transition: all 0.8s ease-out;
    }

    @keyframes slide_center1 {
        0% {
            opacity: 0;
            transform: translateX(300px) translateY(100px) rotate(-15deg) scale(0.5);
            filter: blur(10px);
        }

        45%,
        100% {
            opacity: 1;
            transform: translateX(0) translateY(0) rotate(0deg);
            filter: blur(0px);
        }
    }


    .slide-in-center-right {
        animation: slide_center2 linear both;
        animation-timeline: view();
        transition: all 0.8s ease-out;
    }

    @keyframes slide_center2 {
        0% {
            opacity: 0;
            transform: translateX(-300px) translateY(100px) rotate(15deg) scale(0.5);
            filter: blur(10px);
        }

        45%,
        100% {
            opacity: 1;
            transform: translateX(0px) translateY(0px) rotate(0deg);
            filter: blur(00px);
        }
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

</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('loaded');

        // Reveal elements on scroll using IntersectionObserver
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12
        });

        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
    });
</script>

<?php include 'includes/footer.php'; ?>