<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Support redirect via GET param (sets session redirect target safely)
if (isset($_GET['redirect'])) {
    $raw = $_GET['redirect'];
    $path = parse_url($raw, PHP_URL_PATH);
    $base = basename($path);
    $allowed = ['spa.php', 'dining.php', 'add_booking.php', 'view_rooms.php', 'index.php'];
    if (in_array($base, $allowed)) {
        $_SESSION['redirect_after_login'] = $base;
    }
}

// Optional notification key to display a friendly message after redirect
if (isset($_GET['notify'])) {
    $notify = $_GET['notify'];
    $allowed_notifications = ['please_login'];
    if (in_array($notify, $allowed_notifications)) {
        $_SESSION['error'] = 'Please login to make a booking';
    }
}

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

include('../includes/header.php');
include('../includes/security.php');

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>

<div class="min-h-screen flex items-center justify-center bg-gray-950 py-12 px-4 sm:px-6 lg:px-8 relative font-sans">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-yellow-600/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-yellow-900/5 rounded-full blur-3xl"></div>
    </div>

    <div
        class="max-w-[1100px] w-full bg-gray-900 rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 min-h-[600px] border border-gray-800">

        <!-- Left Side: Form -->
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-gray-900 relative">
            <div class="mb-8 text-center md:text-left">
                <h1 class="font-serif text-3xl md:text-4xl font-bold text-white leading-tight mb-2">
                    Welcome <span class="text-yellow-600">Back</span>
                </h1>
                <p class="text-gray-400 text-sm">Sign in to continue your luxury experience.</p>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['error'])): ?>
                <div
                    class="mb-6 bg-red-900/20 text-red-400 px-4 py-3 rounded-xl text-sm border border-red-900/50 flex items-center gap-3 animate-pulse">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="space-y-4" action="process/process_login.php" method="POST" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Username/Email -->
                <div class="group">
                    <label for="username"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Username or
                        Email</label>
                    <div class="relative">
                        <input type="text" id="username" name="username"
                            class="block w-full px-5 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Username or Email" required>
                        <div
                            class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="usernameError"></p>
                </div>

                <!-- Password -->
                <div class="group">
                    <label for="password"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="block w-full px-5 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Password" required>
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-500 hover:text-gray-400 transition-colors cursor-pointer focus:outline-none"
                            id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="flex justify-between items-center mt-1 ml-1">
                        <p class="text-xs text-red-400 hidden font-medium" id="passwordError"></p>
                        <a href="forgot_password.php"
                            class="text-xs font-semibold text-yellow-600 hover:text-yellow-500 transition-colors">Forgot
                            Password?</a>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                        class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-700 bg-gray-800 rounded cursor-pointer transition-all">
                    <label for="remember-me"
                        class="ml-2 block text-sm text-gray-400 cursor-pointer select-none">Remember
                        me</label>
                </div>

                <div class="pt-4 flex gap-4 flex-col sm:flex-row">
                    <button type="submit"
                        class="w-full py-4 px-6 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 text-sm tracking-wide uppercase">
                        Sign In
                    </button>
                </div>

                <div class="text-center mt-3">
                    <p class="text-xs text-gray-500">
                        By signing in, you agree to our <a href="../pages/terms.php"
                            class="underline hover:text-gray-400">Terms</a> and <a href="../pages/privacy.php"
                            class="underline hover:text-gray-400">Privacy Policy</a>.
                    </p>
                </div>

                <div class="text-center mt-4">
                    <p class="text-sm text-gray-400">Don't have an account? <a href="register.php"
                            class="font-bold text-yellow-600 hover:text-yellow-500 transition-colors">Create One</a></p>
                </div>
            </form>
        </div>

        <!-- Right Side: Image -->
        <div class="hidden md:block w-1/2 relative bg-gray-900 overflow-hidden">
            <img src="../images/penthouse.jpg" alt="Luxury Hotel"
                class="absolute inset-0 w-full h-full object-cover opacity-90 transition-transform duration-[20s] hover:scale-110">
            <div
                class="absolute inset-0 bg-gradient-to-br from-blue-900/30 via-transparent to-yellow-900/30 mix-blend-overlay">
            </div>
            <div class="absolute inset-0 bg-black/20"></div>

            <!-- Abstract Floating Elements -->
            <div
                class="absolute top-1/3 left-[-30px] w-64 h-64 bg-gradient-to-br from-white/10 to-transparent backdrop-blur-2xl rounded-full border border-white/10 shadow-2xl z-10 animate-float">
            </div>
            <div
                class="absolute bottom-1/4 right-10 w-40 h-40 bg-yellow-500/20 backdrop-blur-3xl rounded-full blur-xl animate-float-delayed">
            </div>

            <div class="absolute top-12 right-12 z-20 text-right">
                <h3 class="text-white font-serif text-2xl font-bold tracking-wide">Your Account</h3>
                <p class="text-white/80 text-sm mt-1">Access your bookings and preferences.</p>
            </div>

            <div class="absolute bottom-12 left-12 right-12 z-20">
                <div
                    class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-xl transform transition-all hover:bg-white/20">
                    <p class="text-white font-serif text-lg leading-relaxed italic opacity-95">"Casa Aurelia offers
                        an unmatched experience of comfort."</p>
                    <div class="flex gap-1 text-yellow-400 mt-2 text-xs">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(-30px);
        }

        50% {
            transform: translateY(0);
        }
    }

    @keyframes float-delayed {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    .animate-float {
        animation: float 7s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float-delayed 9s ease-in-out infinite;
    }
</style>

<script>
    (function () {
        'use strict';

        const form = document.getElementById('loginForm');
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('password');

        // Password toggle functionality
        if (passwordToggle && passwordInput) {
            passwordToggle.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        function showError(input, message) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            input.classList.add('border-red-500', 'focus:border-red-500');
            input.classList.remove('border-gray-700', 'focus:border-yellow-600');
        }

        function clearError(input) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.classList.add('hidden');
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-700', 'focus:border-yellow-600');
        }

        form.addEventListener('submit', function (e) {
            let isValid = true;
            const inputs = form.querySelectorAll('input[required]');
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    showError(input, 'This field is required');
                } else {
                    clearError(input);
                }
            });
            if (!isValid) e.preventDefault();
        });

        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => clearError(input));
        });
    })();
</script>

<?php include('../includes/footer.php'); ?>