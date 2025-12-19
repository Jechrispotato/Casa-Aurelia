<?php
session_start();
include('../includes/header.php');
include('../includes/security.php');

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$csrf_token = generate_csrf_token();
$email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';
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
                    Reset Your <span class="text-yellow-600">Password</span>
                </h1>
                <p class="text-gray-400 text-sm">Enter the code sent to your email and create a new password.</p>
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

            <?php if (isset($_SESSION['success'])): ?>
                <div
                    class="mb-6 bg-emerald-900/20 text-emerald-400 px-4 py-3 rounded-xl text-sm border border-emerald-900/50 flex items-center gap-3 animate-pulse">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="space-y-4" action="process/process_reset_password.php" method="POST" id="resetPasswordForm"
                novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <?php if ($email): ?>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <div class="p-4 bg-gray-800 border-2 border-gray-700 rounded-2xl text-sm text-gray-400 mb-4">
                        <i class="fas fa-envelope mr-2 text-yellow-600"></i>Resetting for: <strong
                            class="text-white ml-1"><?php echo htmlspecialchars($email); ?></strong>
                    </div>
                <?php else: ?>
                    <div class="group">
                        <label for="email"
                            class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Email
                            Address</label>
                        <div class="relative">
                            <input type="email" id="email" name="email"
                                class="block w-full px-5 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                                placeholder="Email Address" required>
                            <div
                                class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reset Code -->
                <div class="group">
                    <label for="code"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Reset
                        Code</label>
                    <div class="relative">
                        <input type="text" id="code" name="code"
                            class="block w-full px-5 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium tracking-widest text-center text-lg uppercase"
                            placeholder="000000" required maxlength="6">
                        <div
                            class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="codeError"></p>
                </div>

                <!-- New Password -->
                <div class="group">
                    <label for="password"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">New
                        Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="block w-full px-5 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="New Password" required minlength="8">
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-500 hover:text-gray-400 transition-colors cursor-pointer focus:outline-none"
                            id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="passwordError"></p>
                </div>

                <!-- Confirm Password -->
                <div class="group">
                    <label for="confirm_password"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Confirm
                        Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="block w-full px-5 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Confirm Password" required>
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-500 hover:text-gray-400 transition-colors cursor-pointer focus:outline-none"
                            id="confirmPasswordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="confirmPasswordError"></p>
                </div>

                <div class="pt-4 flex gap-4 flex-col">
                    <button type="submit"
                        class="w-full py-4 px-6 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 text-sm tracking-wide uppercase">
                        <i class="fas fa-lock mr-2"></i>Reset Password
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a href="login.php"
                        class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-yellow-600 transition-colors font-medium">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Login</span>
                    </a>
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
                <h3 class="text-white font-serif text-2xl font-bold tracking-wide">New Beginning</h3>
                <p class="text-white/80 text-sm mt-1">Secure your account with a fresh password.</p>
            </div>

            <div class="absolute bottom-12 left-12 right-12 z-20">
                <div
                    class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-xl transform transition-all hover:bg-white/20">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 bg-yellow-600/20 rounded-xl flex items-center justify-center border border-yellow-600/30">
                            <i class="fas fa-shield-check text-lg text-yellow-500"></i>
                        </div>
                        <h4 class="text-white font-serif text-lg font-bold">Protected Access</h4>
                    </div>
                    <p class="text-white/90 text-sm leading-relaxed">Your security is paramount. We use
                        industry-standard encryption to protect your information.</p>
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
        const form = document.getElementById('resetPasswordForm');

        // Password toggle functionality
        function setupToggle(btnId, inputId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            if (btn && input) {
                btn.addEventListener('click', function () {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }
        }
        setupToggle('passwordToggle', 'password');
        setupToggle('confirmPasswordToggle', 'confirm_password');

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
            const codeInput = document.getElementById('code');
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');

            // Validate Code
            if (!codeInput.value.trim()) {
                isValid = false;
                showError(codeInput, 'Reset code is required');
            } else if (codeInput.value.trim().length !== 6) {
                isValid = false;
                showError(codeInput, 'Code must be 6 digits');
            } else {
                clearError(codeInput);
            }

            // Validate Password
            if (!passwordInput.value) {
                isValid = false;
                showError(passwordInput, 'Password is required');
            } else if (passwordInput.value.length < 8) {
                isValid = false;
                showError(passwordInput, 'Password must be at least 8 characters');
            } else {
                clearError(passwordInput);
            }

            // Validate Confirm Password
            if (passwordInput.value !== confirmInput.value) {
                isValid = false;
                showError(confirmInput, 'Passwords do not match');
            } else {
                clearError(confirmInput);
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Clear errors on input
        ['code', 'password', 'confirm_password'].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => clearError(input));
            }
        });
    })();
</script>

<?php include('../includes/footer.php'); ?>