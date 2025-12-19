<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
include('header.php');

$user_id = $_SESSION['user_id'];
?>

<div class="min-h-screen bg-gray-950 py-12">
    <div class="container mx-auto px-4 max-w-4xl">

        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-3xl font-bold font-serif text-white">Account Settings</h1>
            <a href="profile.php" class="text-gray-400 hover:text-white font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
        </div>

        <div class="grid grid-cols-1 gap-8">

            <!-- Notification Area -->
            <?php if (isset($_SESSION['error'])): ?>
                <div
                    class="bg-red-900/20 border border-red-900/50 text-red-500 px-4 py-3 rounded-xl flex items-center gap-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div
                    class="bg-green-900/20 border border-green-900/50 text-green-400 px-4 py-3 rounded-xl flex items-center gap-3">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <!-- Change Password Section -->
            <div class="bg-gray-900 rounded-2xl shadow-lg border border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-800">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-lock text-yellow-600"></i> Security
                    </h2>
                    <p class="text-gray-400 text-sm mt-1">Update your password to keep your account secure.</p>
                </div>
                <div class="p-6">
                    <form action="process_password_change.php" method="POST" class="space-y-4 max-w-lg">
                        <div>
                            <label class="block text-sm font-bold text-gray-400 mb-2">Current Password</label>
                            <input type="password" name="current_password" required
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-400 mb-2">New Password</label>
                            <input type="password" name="new_password" required minlength="8"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                            <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-400 mb-2">Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="8"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 text-white rounded-xl focus:outline-none focus:border-yellow-600 focus:ring-1 focus:ring-yellow-900 transition-colors">
                        </div>
                        <div class="pt-2">
                            <button type="submit"
                                class="bg-yellow-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-yellow-700 transition-colors shadow-lg hover:shadow-yellow-600/30 transform hover:-translate-y-0.5">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account Section -->
            <div class="bg-red-950/20 rounded-[2rem] border border-red-900/30 overflow-hidden shadow-2xl">
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="w-12 h-12 rounded-xl bg-red-900/20 flex items-center justify-center text-red-500 border border-red-900/40">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-red-500 font-serif">Danger Zone</h2>
                            <p class="text-red-400/80 text-sm">Sensitive account actions that cannot be reversed.</p>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-between flex-wrap gap-6 p-6 bg-red-950/10 border border-red-900/20 rounded-2xl">
                        <div class="max-w-md">
                            <p class="font-bold text-white text-lg">Permanently Delete Account</p>
                            <p class="text-sm text-gray-400 mt-1 leading-relaxed">Once deleted, your profile, active
                                bookings, and history will be cleared from our luxury manifest. This action is final.
                            </p>
                        </div>
                        <button onclick="document.getElementById('deleteAccountModal').classList.remove('hidden')"
                            class="bg-red-600 hover:bg-red-700 text-white px-8 py-3.5 rounded-xl font-bold transition-all shadow-lg shadow-red-600/20 transform hover:-translate-y-0.5">
                            Initiate Deletion
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAccountModal"
    class="fixed inset-0 bg-gray-950/90 z-50 hidden flex items-center justify-center backdrop-blur-xl p-4 transition-all duration-300">
    <div
        class="bg-gray-900 rounded-[2.5rem] shadow-2xl max-w-md w-full p-10 transform transition-all border border-gray-800 relative overflow-hidden">

        <!-- Danger Glow -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-600/10 blur-3xl rounded-full"></div>

        <div class="relative">
            <div
                class="w-20 h-20 bg-red-950/30 rounded-3xl flex items-center justify-center text-red-500 mb-8 border border-red-900/40 mx-auto">
                <i class="fas fa-trash-alt text-3xl"></i>
            </div>

            <h3 class="text-2xl font-bold text-white mb-4 text-center font-serif">Verify Deletion</h3>
            <p class="text-gray-400 mb-8 text-center leading-relaxed">
                To confirm you wish to permanently leave Grand Aurelia and erase your heritage with us, please type
                <span class="text-white font-mono font-bold bg-white/5 px-2 py-1 rounded">DELETE</span> below.
            </p>

            <form action="process_delete_account.php" method="POST">
                <div class="mb-8">
                    <label
                        class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-3 ml-1">Confirmation
                        Phrase</label>
                    <input type="text" name="confirmation" required pattern="DELETE" placeholder="Type DELETE..."
                        class="w-full px-5 py-4 bg-gray-800 border-2 border-gray-800 text-white rounded-2xl focus:outline-none focus:border-red-600 focus:ring-0 transition-all placeholder-gray-600 font-mono tracking-widest text-center">
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit"
                        class="w-full px-6 py-4 bg-red-600 text-white font-bold rounded-2xl hover:bg-red-700 transition-all shadow-xl shadow-red-600/20 transform hover:-translate-y-1">
                        Confirm Deletion
                    </button>
                    <button type="button"
                        onclick="document.getElementById('deleteAccountModal').classList.add('hidden')"
                        class="w-full px-6 py-4 bg-gray-800 text-gray-400 font-bold rounded-2xl hover:bg-gray-700 hover:text-white transition-all border border-gray-700">
                        Cancel and Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>