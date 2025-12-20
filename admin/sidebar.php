<?php
// Admin sidebar - include this in admin pages for consistent navigation
$current_admin_page = basename($_SERVER['PHP_SELF']);
?>

<div class="flex min-h-screen bg-[#111827]" style="font-family: 'AureliaLight';">
    <!-- Sidebar -->
    <aside class="w-64 bg-[#111827] text-white flex-shrink-0 hidden lg:block relative z-10">
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white tracking-wide">
                Admin<span class="text-yellow-600">Panel</span>
            </h1>
        </div>

        <nav class="mt-2 text-white">
            <div class="px-4 mb-2 text-xs font-bold text-white uppercase tracking-wider opacity-60">Main</div>
            <a href="<?php echo ADMIN_PATH; ?>dashboard.php"
                class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'dashboard.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-white hover:bg-white/10'; ?>">
                <i
                    class="fas fa-tachometer-alt w-6 text-center mr-3 <?php echo $current_admin_page === 'dashboard.php' ? 'text-white' : 'text-white/60 group-hover:text-yellow-500'; ?>"></i>
                Dashboard
            </a>

            <div class="px-4 mt-8 mb-2 text-xs font-bold text-white uppercase tracking-wider opacity-60">Bookings &
                Reservations
            </div>
            <a href="<?php echo ADMIN_PATH; ?>bookings.php"
                class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'bookings.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-white hover:bg-white/10'; ?>">
                <i
                    class="fas fa-hotel w-6 text-center mr-3 <?php echo $current_admin_page === 'bookings.php' ? 'text-white' : 'text-white/60 group-hover:text-yellow-500'; ?>"></i>
                Room Bookings
            </a>
            <a href="<?php echo ADMIN_PATH; ?>dining_bookings.php"
                class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'dining_bookings.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-white hover:bg-white/10'; ?>">
                <i
                    class="fas fa-utensils w-6 text-center mr-3 <?php echo $current_admin_page === 'dining_bookings.php' ? 'text-white' : 'text-white/60 group-hover:text-yellow-500'; ?>"></i>
                Dining Reservations
            </a>
            <a href="<?php echo ADMIN_PATH; ?>spa_bookings.php"
                class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'spa_bookings.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-white hover:bg-white/10'; ?>">
                <i
                    class="fas fa-spa w-6 text-center mr-3 <?php echo $current_admin_page === 'spa_bookings.php' ? 'text-white' : 'text-white/60 group-hover:text-yellow-500'; ?>"></i>
                Spa Bookings
            </a>

            <div class="px-4 mt-8 mb-2 text-xs font-bold text-white uppercase tracking-wider opacity-60">Management
            </div>
            <a href="<?php echo ADMIN_PATH; ?>rooms.php"
                class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'rooms.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-white hover:bg-white/10'; ?>">
                <i
                    class="fas fa-door-open w-6 text-center mr-3 <?php echo $current_admin_page === 'rooms.php' ? 'text-white' : 'text-white/60 group-hover:text-yellow-500'; ?>"></i>
                Manage Rooms
            </a>
            <a href="<?php echo ADMIN_PATH; ?>add_room.php"
                class="group flex items-center px-6 py-3 text-sm font-medium transition-colors <?php echo $current_admin_page === 'add_room.php' ? 'bg-yellow-600 text-white border-r-4 border-yellow-400' : 'text-white hover:bg-white/10'; ?>">
                <i
                    class="fas fa-plus-circle w-6 text-center mr-3 <?php echo $current_admin_page === 'add_room.php' ? 'text-white' : 'text-white/60 group-hover:text-yellow-500'; ?>"></i>
                Add Room
            </a>
        </nav>

        <div class="absolute bottom-0 w-full p-6">
            <a href="<?php echo AUTH_PATH; ?>logout.php"
                class="flex items-center text-white hover:text-yellow-500 transition-colors">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#111827]">
        <!-- Optional Topbar for Mobile -->
        <header class="bg-gray-900 border-b border-gray-800 lg:hidden h-16 flex items-center justify-between px-4 z-20">
            <span class="font-bold text-xl text-white" style="font-family: 'AureliaLight';">Admin Panel</span>
            <button class="text-gray-500 focus:outline-none">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#111827] p-6 md:p-8" <?php echo ($current_admin_page !== 'index.php') ? 'style="padding-top: 80px;"' : ''; ?>>