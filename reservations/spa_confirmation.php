<?php
session_start();
include('header.php');
?>

<style>
    .confirmation-section {
        min-height: 80vh;
        background-color: #030712;
        /* gray-950 */
        display: flex;
        align-items: center;
        padding: 80px 0;
    }

    .confirmation-card {
        background-color: #111827;
        /* gray-900 */
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .confirmation-header {
        background: linear-gradient(to right, #030712, #111827);
        padding: 3rem 2rem;
        text-align: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .confirmation-icon-wrapper {
        width: 100px;
        height: 100px;
        background-color: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        animation: scaleIn 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .confirmation-icon {
        font-size: 3rem;
        color: #10b981;
        /* emerald-500 */
    }

    @keyframes scaleIn {
        from {
            transform: scale(0.5);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .confirmation-body {
        padding: 4rem 3rem;
    }

    .btn-confirmation {
        background-color: #d97706;
        /* yellow-600 */
        border: none;
        color: white;
        padding: 16px 40px;
        border-radius: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.2);
    }

    .btn-confirmation:hover {
        background-color: #b45309;
        /* yellow-700 */
        transform: translateY(-3px);
        box-shadow: 0 20px 25px -5px rgba(217, 119, 6, 0.3);
        color: white;
    }

    .btn-secondary-custom {
        background-color: #1f2937;
        /* gray-800 */
        border: 1px solid #374151;
        /* gray-700 */
        color: white;
        padding: 16px 40px;
        border-radius: 16px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s;
    }

    .btn-secondary-custom:hover {
        background-color: #374151;
        transform: translateY(-3px);
        color: white;
    }

    .status-alert {
        padding: 1.5rem;
        border-radius: 20px;
        margin-bottom: 2.5rem;
        font-weight: 600;
    }

    .alert-success-custom {
        background-color: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #34d399;
    }

    .alert-error-custom {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #f87171;
    }

    .alert-info-custom {
        background-color: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.2);
        color: #60a5fa;
    }
</style>

<div class="confirmation-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7">
                <div class="confirmation-card">
                    <div class="confirmation-header">
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="confirmation-icon-wrapper">
                                <i class="fas fa-check confirmation-icon"></i>
                            </div>
                            <h2 class="text-3xl font-bold font-serif text-white mb-0">Spa Booking Submitted!</h2>
                        <?php else: ?>
                            <div class="confirmation-icon-wrapper"
                                style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2);">
                                <i class="fas fa-exclamation confirmation-icon" style="color: #f87171;"></i>
                            </div>
                            <h2 class="text-3xl font-bold font-serif text-white mb-0">Booking Status</h2>
                        <?php endif; ?>
                    </div>

                    <div class="confirmation-body text-center">
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="status-alert alert-success-custom">
                                <i class="fas fa-magic me-2"></i>
                                <?php echo htmlspecialchars($_SESSION['success']); ?>
                            </div>
                            <p class="text-gray-300 text-lg leading-relaxed mb-10">Thank you for choosing the Aurelia Spa.
                                Your appointment request has been received and is awaiting confirmation from our wellness
                                team.</p>
                        <?php elseif (!empty($_SESSION['error'])): ?>
                            <div class="status-alert alert-error-custom">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($_SESSION['error']); ?>
                            </div>
                        <?php else: ?>
                            <div class="status-alert alert-info-custom">
                                <i class="fas fa-info-circle me-2"></i>
                                No booking information found.
                            </div>
                        <?php endif; ?>

                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="spa.php" class="btn btn-confirmation">
                                <i class="fas fa-spa me-2"></i>Back to Spa
                            </a>
                            <a href="view_bookings.php" class="btn btn-secondary-custom">
                                <i class="fas fa-list me-2"></i>My Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
unset($_SESSION['success'], $_SESSION['error']);
include('footer.php');
?>