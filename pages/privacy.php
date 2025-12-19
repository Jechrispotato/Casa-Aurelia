<?php
session_start();
include('../includes/header.php');
?>

<div class="bg-gray-950 min-h-screen py-24">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-5xl font-serif font-bold text-white mb-12 text-center">Privacy Policy</h1>

        <div
            class="bg-gray-900 rounded-[2rem] border border-gray-800 shadow-2xl p-8 md:p-16 space-y-12 text-gray-400 leading-relaxed text-lg">

            <section>
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <span
                        class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center mr-4 text-sm">1</span>
                    Information We Collect
                </h2>
                <p>We collect information you provide directly to us when making a reservation, such as your name, email
                    address, phone number, and payment information.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <span
                        class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center mr-4 text-sm">2</span>
                    How We Use Your Information
                </h2>
                <p>We use the information we collect to:</p>
                <ul class="list-disc pl-12 mt-4 space-y-3">
                    <li>Process your reservations and payments.</li>
                    <li>Communicate with you about your stay.</li>
                    <li>Improve our services and guest experience.</li>
                    <li>Send promotional emails (you may opt-out at any time).</li>
                </ul>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <span
                        class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center mr-4 text-sm">3</span>
                    Data Security
                </h2>
                <p>We implement reasonable security measures to protect your personal information. However, no method of
                    transmission over the internet is 100% secure.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <span
                        class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center mr-4 text-sm">4</span>
                    Cookies
                </h2>
                <p>Our website uses cookies to enhance your browsing experience and analyze site traffic. You can
                    control cookie preferences through your browser settings.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <span
                        class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center mr-4 text-sm">5</span>
                    Third-Party Sharing
                </h2>
                <p>We do not sell your personal information. We may share data with trusted service providers who assist
                    us in operating our website and conducting our business.</p>
            </section>

            <section>
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <span
                        class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center mr-4 text-sm">6</span>
                    Contact Us
                </h2>
                <p>If you have any questions about this Privacy Policy, please contact us at <a
                        href="mailto:privacy@grandaurelia.com"
                        class="text-yellow-600 hover:text-yellow-500 transition-colors">privacy@grandaurelia.com</a>.
                </p>
            </section>

            <div class="pt-12 border-t border-gray-800 text-center">
                <p class="text-sm text-gray-500">Last Updated: December 2025</p>
                <a href="../index.php"
                    class="inline-flex items-center mt-6 text-yellow-600 font-bold hover:text-yellow-500 transition-colors group">
                    <i class="fas fa-arrow-left mr-2 transform group-hover:-translate-x-1 transition-transform"></i>
                    Return to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>