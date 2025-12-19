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

include('../includes/paths.php');
include('../includes/security.php');

// Generate CSRF token
$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | The Grand Aurelia</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link href="<?php echo DIST_PATH; ?>output.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Velista';
            src: url('<?php echo AURELIA_ASSETS; ?>velista.otf') format('opentype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #030712;
        }

        .font-serif {
            font-family: 'Velista', serif;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes float-delayed {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float-delayed 8s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gray-950">

    <div class="min-h-screen flex relative overflow-hidden">
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute top-[-10%] right-[-5%] w-[500px] h-[500px] bg-yellow-600/5 rounded-full blur-3xl">
            </div>
            <div class="absolute bottom-[-10%] left-[-5%] w-[600px] h-[600px] bg-yellow-900/10 rounded-full blur-3xl">
            </div>
        </div>

        <div class="w-full flex flex-col md:flex-row relative z-10">
            <!-- Left Side: Form -->
            <div class="w-full md:w-1/2 lg:w-[45%] p-10 md:p-14 lg:p-24 flex flex-col justify-center bg-gray-950">
                                    <a href="../index.php"
                        class="inline-flex items-center gap-2 mb-6 text-gray-500 hover:text-yellow-600 transition-colors text-xs font-bold uppercase tracking-widest group">
                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                        Back to Home
                    </a>
            <div class="mb-10">

                    <a href="../index.php"
                        class="inline-flex items-center gap-2 mb-10 hover:opacity-80 transition-opacity group">
                        <img src="../aurelia_assets/aurelia_main_logo_only_white.png" alt="logo"
                            class="w-10 group-hover:scale-110 transition-transform">
                        <img src="../aurelia_assets/casaaurelialogo1_white.png" alt="text logo" class="h-8">
                    </a>
                    <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6">
                        The <span class="text-yellow-600 italic">Luxury</span> you<br>deserve to have.
                    </h1>
                    <p class="text-gray-400 text-lg">Welcome back! Please login to your account to continue.</p>
                </div>

                <!-- Alerts -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div
                        class="mb-8 bg-red-900/20 text-red-400 px-6 py-4 rounded-2xl text-sm border border-red-900/50 flex items-center gap-3 animate-pulse">
                        <i class="fas fa-exclamation-circle text-lg"></i>
                        <?php
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-6 max-w-lg" action="process/process_login.php" method="POST" id="loginForm"
                    novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="space-y-5">
                        <!-- Username/Email -->
                        <div class="group">
                            <label for="username"
                                class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 ml-1">Username
                                or Email</label>
                            <div class="relative">
                                <input type="text" id="username" name="username"
                                    class="block w-full px-6 py-3.5 bg-gray-900 border-2 border-gray-800 rounded-full text-white placeholder-gray-600 focus:outline-none focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium text-sm"
                                    placeholder="Enter your username" required>
                                <div
                                    class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-600 peer-focus:text-yellow-600 transition-colors">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-red-400 hidden font-medium ml-1" id="usernameError"></p>
                        </div>

                        <!-- Password -->
                        <div class="group">
                            <div class="flex justify-between items-center mb-3 ml-1">
                                <label for="password"
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Password</label>
                                <a href="forgot_password.php"
                                    class="text-xs font-bold text-yellow-600 hover:text-yellow-500 transition-colors">Forgot
                                    Password?</a>
                            </div>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                    class="block w-full px-6 py-3.5 bg-gray-900 border-2 border-gray-800 rounded-full text-white placeholder-gray-600 focus:outline-none focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium text-sm"
                                    placeholder="Enter your password" required>
                                <button type="button"
                                    class="absolute inset-y-0 right-0 pr-6 flex items-center text-gray-600 hover:text-gray-400 transition-colors cursor-pointer focus:outline-none"
                                    id="passwordToggle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-red-400 hidden font-medium ml-1" id="passwordError"></p>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center ml-4">
                        <input id="remember-me" name="remember-me" type="checkbox"
                            class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-800 bg-gray-900 rounded cursor-pointer transition-all">
                        <label for="remember-me"
                            class="ml-3 block text-sm font-medium text-gray-400 cursor-pointer select-none">Remember my
                            session</label>
                    </div>

                    <div class="pt-8 flex flex-col sm:flex-row gap-4">
                        <button type="submit"
                            class="flex-1 py-4 px-8 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-full shadow-xl hover:shadow-yellow-600/20 transform hover:-translate-y-1 transition-all duration-300 text-xs tracking-widest uppercase">
                            Sign In
                        </button>
                        <a href="register.php"
                            class="flex-1 py-4 px-8 bg-gray-900 border-2 border-gray-800 hover:border-gray-700 text-white font-bold rounded-full transform hover:-translate-y-1 transition-all duration-300 text-center text-xs tracking-widest uppercase flex items-center justify-center">
                            Register
                        </a>
                    </div>

                    <p class="text-xs text-gray-600 text-center mt-12">
                        By accessing your account, you agree to our <a href="../pages/terms.php"
                            class="text-gray-500 hover:text-white transition-colors">Terms of Service</a>
                        and <a href="../pages/privacy.php"
                            class="text-gray-500 hover:text-white transition-colors">Privacy Policy</a>.
                    </p>
                </form>
            </div>

            <!-- Right Side: Image -->
            <div class="hidden md:block md:w-1/2 lg:w-[55%] relative overflow-hidden">
                <img src="../images/penthouse.jpg" alt="Luxury Penthouse"
                    class="absolute inset-0 w-full h-full object-cover opacity-80 transition-transform duration-[30s] hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-l from-gray-950 via-transparent to-transparent"></div>
                <div class="absolute inset-0 bg-black/20"></div>

                <!-- Abstract Floating Elements -->
                
            </div>
        </div>
    </div>

    <script>
        (function () {
            'use strict';
            const form = document.getElementById('loginForm');
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');

            passwordToggle.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });

            function showError(input, message) {
                const errorElement = document.getElementById(input.id + 'Error');
                errorElement.textContent = message;
                errorElement.classList.remove('hidden');
                input.classList.add('border-red-500');
                input.classList.remove('border-gray-800');
            }

            function clearError(input) {
                const errorElement = document.getElementById(input.id + 'Error');
                errorElement.classList.add('hidden');
                input.classList.remove('border-red-500');
                input.classList.add('border-gray-800');
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
</body>

</html>