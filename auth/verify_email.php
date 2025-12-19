<?php
session_start();

// Check if user has a pending verification
if (!isset($_SESSION['pending_verification_email']) || !isset($_SESSION['pending_verification_user_id'])) {
    header('Location: register.php');
    exit;
}

include('../includes/header.php');
include('../includes/security.php');

// Generate CSRF token
$csrf_token = generate_csrf_token();

$email = $_SESSION['pending_verification_email'];
?>

<div class="min-h-screen flex items-center justify-center bg-gray-950 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <img src="../aurelia_assets/aurelia_main_logo_only_white.png" alt="Casa Aurelia Logo"
                class="w-20 h-20 mx-auto mb-4">
            <h1 class="text-4xl md:text-5xl font-bold font-serif text-white mb-2">
                Verify Your <span class="text-yellow-500">Email</span>
            </h1>
            <p class="text-gray-400">We've sent a verification code to your inbox</p>
        </div>

        <!-- Main Card -->
        <div class="bg-gray-900 rounded-3xl shadow-2xl border border-gray-800 overflow-hidden">
            <div class="p-8 md:p-12">

                <!-- Email Display -->
                <div class="mb-8 p-6 bg-yellow-900/20 rounded-2xl border border-yellow-900/50 flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-full bg-yellow-900/30 flex items-center justify-center text-yellow-500 shrink-0">
                        <i class="fas fa-envelope text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-500 font-bold tracking-wider mb-1">Code sent to</p>
                        <p class="font-medium text-white"><?php echo htmlspecialchars($email); ?></p>
                    </div>
                </div>

                <!-- Alerts -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div
                        class="mb-6 bg-red-900/20 text-red-500 px-6 py-4 rounded-2xl text-sm border border-red-900/50 flex items-center gap-3">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div
                        class="mb-6 bg-green-900/20 text-green-400 px-6 py-4 rounded-2xl text-sm border border-green-900/50 flex items-center gap-3">
                        <i class="fas fa-check-circle text-xl"></i>
                        <span><?php echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Verification Form -->
                <form action="process/process_verification.php" method="POST" id="verificationForm" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div>
                        <label for="verification_code"
                            class="block text-sm font-bold text-gray-400 uppercase tracking-wider mb-3 text-center">
                            Enter 6-Digit Code
                        </label>
                        <div class="relative">
                            <input type="text"
                                class="block w-full px-6 py-5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-gray-600 focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-2 focus:ring-yellow-900 transition-all duration-300 text-center font-mono text-3xl tracking-[0.5em] font-bold"
                                id="verification_code" name="verification_code" maxlength="6" placeholder="000000"
                                autocomplete="off" required>
                        </div>
                        <p class="text-xs text-red-500 hidden font-medium text-center mt-3" id="codeError"></p>
                    </div>

                    <button type="submit"
                        class="w-full py-5 px-6 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-yellow-600/30 transform hover:-translate-y-1 transition-all duration-300 text-base tracking-wide uppercase">
                        <i class="fas fa-check-circle mr-2"></i>Verify Email
                    </button>
                </form>

                <!-- Helper Links -->
                <div class="mt-8 flex flex-col gap-4 text-center">
                    <p class="text-gray-400 text-sm">
                        Didn't receive the code?
                        <a href="process/resend_verification.php"
                            class="font-bold text-yellow-500 hover:text-yellow-400 transition-colors ml-1">
                            Resend Code
                        </a>
                    </p>
                    <a href="logout.php"
                        class="text-gray-500 hover:text-gray-400 transition-colors text-sm flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Cancel and return to home
                    </a>
                </div>
            </div>

            <!-- Security Info -->
            <div class="bg-gray-950 border-t border-gray-800 p-6">
                <div class="flex items-center gap-4 text-sm">
                    <div
                        class="w-10 h-10 rounded-full bg-yellow-900/30 flex items-center justify-center text-yellow-500 shrink-0">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <p class="text-white font-semibold mb-1">Secure Verification</p>
                        <p class="text-gray-400 text-xs">Your code expires in 24 hours. Keep it confidential.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="mt-6 text-center">
            <p class="text-gray-500 text-xs">
                <i class="fas fa-info-circle mr-1"></i>
                Check your spam folder if you don't see the email
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes float {

        0%,
        100% {
            transform: translateY(-20px);
        }

        50% {
            transform: translateY(0);
        }
    }

    .animate-float {
        animation: float 8s ease-in-out infinite;
    }
</style>

<script>
    (function () {
        'use strict';

        const form = document.getElementById('verificationForm');
        const input = document.getElementById('verification_code');
        const error = document.getElementById('codeError');

        function showError(msg) {
            error.textContent = msg;
            error.classList.remove('hidden');
            input.classList.add('border-red-500');
            input.classList.remove('border-gray-100', 'focus:border-yellow-500');
        }

        function clearError() {
            error.classList.add('hidden');
            input.classList.remove('border-red-500');
            input.classList.add('border-gray-100', 'focus:border-yellow-500');
        }

        input.addEventListener('input', function (e) {
            // Only numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 0) clearError();
        });

        form.addEventListener('submit', function (e) {
            const code = input.value.trim();
            if (code.length !== 6) {
                e.preventDefault();
                showError('Please enter a valid 6-digit code');
            }
        });

        input.focus();
    })();
</script>

<?php include('../includes/footer.php'); ?>