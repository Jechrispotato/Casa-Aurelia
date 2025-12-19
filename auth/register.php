<?php
session_start();

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
                    Begin Your <span class="text-yellow-600">Journey</span>
                </h1>
                <p class="text-gray-400 text-sm">Create your account to unlock exclusive benefits.</p>
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

            <form class="space-y-4" action="process/process_register.php" method="POST" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <!-- Username -->
                <div class="group">
                    <label for="username"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Username</label>
                    <div class="relative">
                        <input type="text" id="username" name="username"
                            class="block w-full pl-5 pr-12 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Username" pattern="^[a-zA-Z0-9_]+$" minlength="3" maxlength="30" required>
                        <div
                            class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="usernameError"></p>
                </div>

                <!-- Email -->
                <div class="group">
                    <label for="email"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Email
                        Address</label>
                    <div class="relative">
                        <input type="email" id="email" name="email"
                            class="block w-full pl-5 pr-12 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Email" required>
                        <div
                            class="absolute inset-y-0 right-0 pr-5 flex items-center pointer-events-none text-gray-500 peer-focus:text-yellow-600 transition-colors">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="emailError"></p>
                </div>

                <!-- Password -->
                <div class="group">
                    <label for="password"
                        class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="block w-full pl-5 pr-12 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Password" minlength="8" required>
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-5 flex items-center z-20 text-gray-500 hover:text-gray-400 transition-colors cursor-pointer focus:outline-none"
                            id="passwordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <!-- Strength Meter -->
                    <div class="mt-2 h-1 w-full bg-gray-800 rounded-full overflow-hidden">
                        <div id="passwordStrengthBar" class="h-full w-0 bg-red-500 transition-all duration-500"></div>
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
                            class="block w-full pl-5 pr-12 py-3.5 bg-gray-800 border-2 border-gray-700 rounded-2xl text-white placeholder-transparent focus:outline-none focus:bg-gray-800 focus:border-yellow-600 focus:ring-0 transition-all duration-300 peer font-medium"
                            placeholder="Confirm Password" required>
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-5 flex items-center z-20 text-gray-500 hover:text-gray-400 transition-colors cursor-pointer focus:outline-none"
                            id="confirmPasswordToggle">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-red-400 hidden font-medium ml-1" id="confirm_passwordError"></p>
                </div>

                <div class="pt-4 flex gap-4 flex-col sm:flex-row">
                    <button type="submit"
                        class="w-full py-4 px-6 bg-yellow-600 hover:bg-yellow-700 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 text-sm tracking-wide uppercase">
                        Create Account
                    </button>
                </div>

                <div class="text-center mt-3">
                    <p class="text-xs text-gray-500">
                        By creating an account, you agree to our <a href="../pages/terms.php"
                            class="underline hover:text-gray-400">Terms</a> and <a href="../pages/privacy.php"
                            class="underline hover:text-gray-400">Privacy Policy</a>.
                    </p>
                </div>

                <div class="text-center mt-4">
                    <p class="text-sm text-gray-400">Already have an account? <a href="login.php"
                            class="font-bold text-yellow-600 hover:text-yellow-500 transition-colors">Sign In</a></p>
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
                <h3 class="text-white font-serif text-2xl font-bold tracking-wide">Elite Status</h3>
                <p class="text-white/80 text-sm mt-1">Join the club of distinguished guests.</p>
            </div>

            <div class="absolute bottom-12 left-12 right-12 z-20">
                <div
                    class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 shadow-xl transform transition-all hover:bg-white/20">
                    <p class="text-white font-serif text-lg leading-relaxed italic opacity-95">"The attention to detail
                        is cleaner than anything I've experienced."</p>
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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-10px);
        }

        75% {
            transform: translateX(10px);
        }
    }

    .animate-float {
        animation: float 7s ease-in-out infinite;
    }

    .animate-float-delayed {
        animation: float-delayed 9s ease-in-out infinite;
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    .animate-slide-up {
        animation: slideUp 0.8s ease-out;
    }

    .animate-shake {
        animation: shake 0.5s ease-in-out;
    }

    /* Input focus glow effect */
    input:focus {
        box-shadow: 0 0 0 3px rgba(202, 138, 4, 0.1);
    }

    /* Button loading state */
    .btn-loading {
        position: relative;
        color: transparent;
    }

    .btn-loading::after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 50%;
        left: 50%;
        margin-left: -8px;
        margin-top: -8px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.6s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    (function () {
        'use strict';

        const form = document.getElementById('registerForm');
        const username = document.getElementById('username');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const submitBtn = form.querySelector('button[type="submit"]');

        // Page load animations
        document.addEventListener('DOMContentLoaded', function () {
            const formContainer = form.closest('.max-w-\\[1100px\\]');
            if (formContainer) {
                formContainer.classList.add('animate-fade-in');
            }
        });

        // Input sanitization function
        function sanitizeInput(value) {
            return value
                .replace(/<[^>]*>/g, '') // Remove HTML tags
                .replace(/[<>"'`]/g, '') // Remove dangerous chars
                .replace(/javascript:/gi, '') // Remove javascript: protocol
                .replace(/on\w+\s*=/gi, '') // Remove event handlers
                .trim();
        }

        // Enhanced email validation
        function validateEmail(email) {
            const sanitized = sanitizeInput(email);
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (!emailRegex.test(sanitized)) {
                return { valid: false, message: 'Please enter a valid email address' };
            }

            return { valid: true, value: sanitized };
        }

        // Enhanced username validation
        function validateUsername(username) {
            const sanitized = sanitizeInput(username);
            const sqlPatterns = /(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|EXECUTE|UNION|OR|AND)\b)/gi;

            if (sqlPatterns.test(sanitized)) {
                return { valid: false, message: 'Invalid characters detected' };
            }

            if (sanitized.length < 3) {
                return { valid: false, message: 'Username must be at least 3 characters' };
            }

            if (sanitized.length > 30) {
                return { valid: false, message: 'Username is too long' };
            }

            if (!/^[a-zA-Z0-9_]+$/.test(sanitized)) {
                return { valid: false, message: 'Username can only contain letters, numbers, and underscores' };
            }

            return { valid: true, value: sanitized };
        }

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
            input.classList.add('border-red-500', 'focus:border-red-500', 'animate-shake');
            input.classList.remove('border-gray-700', 'focus:border-yellow-600');

            setTimeout(() => {
                input.classList.remove('animate-shake');
            }, 500);
        }

        function clearError(input) {
            const errorElement = document.getElementById(input.id + 'Error');
            errorElement.classList.add('hidden');
            input.classList.remove('border-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-700', 'focus:border-yellow-600');
        }

        function validatePasswordStrength(val) {
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            let width = '0%';
            let color = 'bg-red-500';

            if (val.length === 0) { } else if (score < 2) {
                width = '25%';
                color = 'bg-red-500';
            } else if (score < 3) {
                width = '50%';
                color = 'bg-yellow-500';
            } else if (score < 4) {
                width = '75%';
                color = 'bg-blue-500';
            } else {
                width = '100%';
                color = 'bg-green-500';
            }

            passwordStrengthBar.className = `h-full transition-all duration-500 ${color}`;
            passwordStrengthBar.style.width = width;
        }

        // Real-time sanitization
        username.addEventListener('input', function () {
            this.value = sanitizeInput(this.value);
            clearError(this);
        });

        email.addEventListener('input', function () {
            this.value = sanitizeInput(this.value);
            clearError(this);
        });

        password.addEventListener('input', function () {
            validatePasswordStrength(this.value);
            if (this.value.length > 0 && this.value.length < 8) {
                showError(this, 'Min 8 characters');
            } else {
                clearError(this);
            }
            if (confirmPassword.value && confirmPassword.value !== this.value) {
                showError(confirmPassword, 'Passwords do not match');
            } else if (confirmPassword.value) {
                clearError(confirmPassword);
            }
        });

        confirmPassword.addEventListener('input', function () {
            if (password.value !== this.value) {
                showError(this, 'Passwords do not match');
            } else {
                clearError(this);
            }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            let isValid = true;

            // Validate username
            const usernameValidation = validateUsername(username.value);
            if (!usernameValidation.valid) {
                isValid = false;
                showError(username, usernameValidation.message);
            } else {
                username.value = usernameValidation.value;
                clearError(username);
            }

            // Validate email
            const emailValidation = validateEmail(email.value);
            if (!emailValidation.valid) {
                isValid = false;
                showError(email, emailValidation.message);
            } else {
                email.value = emailValidation.value;
                clearError(email);
            }

            // Validate password
            if (password.value.length < 8) {
                isValid = false;
                showError(password, 'Password must be at least 8 characters');
            } else {
                clearError(password);
            }

            // Validate confirm password
            if (password.value !== confirmPassword.value) {
                isValid = false;
                showError(confirmPassword, 'Passwords do not match');
            } else {
                clearError(confirmPassword);
            }

            if (isValid) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;

                setTimeout(() => {
                    form.submit();
                }, 300);
            }
        });
    })();
</script>