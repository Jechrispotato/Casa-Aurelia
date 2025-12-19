<?php
session_start();
include('header.php');
include('security.php');

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$csrf_token = generate_csrf_token();
$email = isset($_SESSION['reset_email']) ? $_SESSION['reset_email'] : '';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-950 py-12 px-4 sm:px-6 lg:px-8 relative font-sans">
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-10%] right-[-5%] w-96 h-96 bg-yellow-600/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-[-10%] left-[-5%] w-[500px] h-[500px] bg-yellow-900/5 rounded-full blur-3xl"></div>
    </div>

    <div
        class="max-w-[1100px] w-full bg-gray-900 rounded-[2.5rem] shadow-2xl border border-gray-800 overflow-hidden flex flex-col md:flex-row relative z-10 min-h-[600px]">

        <!-- Left Side: Form -->
        <div class="w-full md:w-1/2 p-10 md:p-14 lg:p-16 flex flex-col justify-center bg-gray-900 relative">
            <div class="mb-10">
                <div class="flex items-center gap-3 mb-8">
                    <div
                        class="w-10 h-10 rounded-xl bg-yellow-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-yellow-600/20">
                        GA
                    </div>
                    <span class="font-bold text-xl tracking-tight text-white">Grand Aurelia</span>
                </div>
                <h1 class="font-serif text-3xl md:text-5xl font-bold text-white leading-tight mb-4">
                    Reset <span class="text-yellow-600">Password</span>
                </h1>
                <p class="text-gray-400 text-lg">Enter the code sent to your email and your new password.</p>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['error'])): ?>
                <div
                    class="mb-6 bg-red-900/20 text-red-400 px-6 py-4 rounded-2xl text-sm border border-red-900/30 flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div
                    class="mb-6 bg-emerald-900/20 text-emerald-400 px-6 py-4 rounded-2xl text-sm border border-emerald-900/30 flex items-center gap-3">
                    <i class="fas fa-check-circle text-lg"></i>
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="process_reset_password.php" method="POST" id="resetPasswordForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <?php if ($email): ?>
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    <div class="p-4 bg-gray-800/50 border border-gray-700 rounded-2xl text-sm text-gray-400 mb-6">
                        Resetting password for: <strong
                            class="text-white ml-1"><?php echo htmlspecialchars($email); ?></strong>
                    </div>
                <?php else: ?>
                    <div class="space-y-2">
                        <label for="email"
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">Email
                            Address</label>
                        <div class="relative">
                            <input type="email" id="email" name="email"
                                class="block w-full px-6 py-5 bg-gray-800/50 border border-gray-700 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:bg-gray-800 focus:border-yellow-600 transition-all duration-300 peer font-medium"
                                placeholder="name@example.com" required>
                            <div
                                class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="space-y-6">
                    <!-- Reset Code -->
                    <div>
                        <label for="code"
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">Reset
                            Code</label>
                        <div class="relative">
                            <input type="text" id="code" name="code"
                                class="block w-full px-6 py-5 bg-gray-800/50 border border-gray-700 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:bg-gray-800 focus:border-yellow-600 transition-all duration-300 peer font-medium tracking-[0.5em] text-center text-xl uppercase"
                                placeholder="000000" required maxlength="6">
                            <div
                                class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="codeError"></p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password"
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">New
                            Password</label>
                        <div class="relative">
                            <input type="password" id="password" name="password"
                                class="block w-full px-6 py-5 bg-gray-800/50 border border-gray-700 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:bg-gray-800 focus:border-yellow-600 transition-all duration-300 peer font-medium"
                                placeholder="••••••••" required minlength="8">
                            <div
                                class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="passwordError"></p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="confirm_password"
                            class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-3 ml-1">Confirm
                            New Password</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password"
                                class="block w-full px-6 py-5 bg-gray-800/50 border border-gray-700 rounded-2xl text-white placeholder-gray-500 focus:outline-none focus:bg-gray-800 focus:border-yellow-600 transition-all duration-300 peer font-medium"
                                placeholder="••••••••" required>
                            <div
                                class="absolute inset-y-0 right-0 pr-6 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-red-500 hidden font-medium ml-1" id="confirmPasswordError"></p>
                    </div>
                </div>

                <div class="pt-6 flex flex-col gap-5">
                    <button type="submit"
                        class="w-full py-5 px-6 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-2xl shadow-xl shadow-yellow-600/10 hover:shadow-yellow-600/20 transform hover:-translate-y-1 transition-all duration-300 text-sm tracking-widest uppercase">
                        Change Password
                    </button>
                    <a href="login.php"
                        class="text-center font-bold text-gray-400 hover:text-white transition-colors text-sm py-2">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Security Gateway
                    </a>
                </div>
            </form>
        </div>

        <!-- Right Side: Image -->
        <div class="hidden md:block w-1/2 relative bg-gray-900 overflow-hidden border-l border-gray-800">
            <img src="images/penthouse.jpg" alt="Luxury Hotel"
                class="absolute inset-0 w-full h-full object-cover opacity-60 scale-105"
                style="filter: brightness(0.8) contrast(1.1);">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-gray-950/20 to-transparent"></div>
            <div class="absolute inset-0 bg-yellow-600/5 mix-blend-color"></div>

            <div class="absolute bottom-16 left-12 right-12 z-20">
                <div class="bg-gray-900/40 backdrop-blur-xl rounded-3xl p-8 border border-white/5 shadow-2xl">
                    <div
                        class="w-12 h-12 bg-yellow-600/20 rounded-xl flex items-center justify-center mb-6 border border-yellow-600/30">
                        <i class="fas fa-shield-alt text-2xl text-yellow-500"></i>
                    </div>
                    <h3 class="text-white font-serif text-2xl font-bold mb-3 tracking-tight">Security Protocol</h3>
                    <p class="text-gray-300 font-serif text-lg leading-relaxed italic opacity-90">"Securing your digital
                        sanctuary with institutional-grade protection."</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';
        const form = document.getElementById('resetPasswordForm');

        function showError(input, message) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
            input.classList.add('border-red-500', 'focus:border-red-500');
            input.classList.remove('border-gray-100', 'focus:border-yellow-500');
        }

        function clearError(input) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.classList.add('hidden');
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-100', 'focus:border-yellow-500');
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