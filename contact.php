<?php include("../header.php"); ?>


    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Send us a Message</h1>
                <p class="text-gray-600 mb-8">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>

                <?php if (isset($_GET['success'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
                        <strong>Success!</strong> Your message has been sent successfully.
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                        <strong>Error!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="process/contact_process.php" method="POST" id="contactForm" class="space-y-6">
                    <!-- Full Name -->
                    <div>
                        <label for="fullname" class="block text-gray-700 font-semibold mb-2">Full Name <span class="text-red-600">*</span></label>
                        <input type="text" id="fullname" name="fullname" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="Your name" required>
                        <span class="error text-red-600 text-sm hidden"></span>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-gray-700 font-semibold mb-2">Email Address <span class="text-red-600">*</span></label>
                        <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="your@email.com" required>
                        <span class="error text-red-600 text-sm hidden"></span>
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject <span class="text-red-600">*</span></label>
                        <input type="text" id="subject" name="subject" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="Message subject" required>
                        <span class="error text-red-600 text-sm hidden"></span>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="message" class="block text-gray-700 font-semibold mb-2">Message <span class="text-red-600">*</span></label>
                        <textarea id="message" name="message" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600" placeholder="Your message here..." required></textarea>
                        <span class="error text-red-600 text-sm hidden"></span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div>
                <div class="bg-blue-50 rounded-lg p-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Information</h2>
                    
                    <div class="space-y-6">
                        <!-- Address -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-blue-600 text-2xl mt-1"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Address</h3>
                                <p class="text-gray-600 mt-1">123 Education Street, Tech City, Nigeria</p>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-phone text-blue-600 text-2xl mt-1"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Phone</h3>
                                <p class="text-gray-600 mt-1">+234 (0) 123 456 7890</p>
                                <p class="text-gray-600">+234 (0) 987 654 3210</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-blue-600 text-2xl mt-1"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Email</h3>
                                <p class="text-gray-600 mt-1">info@wingcommander.edu.ng</p>
                                <p class="text-gray-600">admissions@wingcommander.edu.ng</p>
                            </div>
                        </div>

                        <!-- Hours -->
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-blue-600 text-2xl mt-1"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">Working Hours</h3>
                                <p class="text-gray-600 mt-1">Monday - Friday: 8:00 AM - 5:00 PM</p>
                                <p class="text-gray-600">Saturday: 9:00 AM - 3:00 PM</p>
                                <p class="text-gray-600">Sunday: Closed</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">FAQs</h2>
                    <div class="space-y-4">
                        <details class="border-l-4 border-blue-600 pl-4">
                            <summary class="font-semibold text-gray-900 cursor-pointer">How do I apply?</summary>
                            <p class="text-gray-600 mt-2">Visit our Apply page and fill out the application form with your details. Submit and we will review your application.</p>
                        </details>
                        <details class="border-l-4 border-blue-600 pl-4">
                            <summary class="font-semibold text-gray-900 cursor-pointer">What programs do you offer?</summary>
                            <p class="text-gray-600 mt-2">We offer programs in various trade areas including welding, electrical installation, automotive technology, ICT, plumbing, carpentry, and more.</p>
                        </details>
                        <details class="border-l-4 border-blue-600 pl-4">
                            <summary class="font-semibold text-gray-900 cursor-pointer">How long are the programs?</summary>
                            <p class="text-gray-600 mt-2">Program duration varies from short courses (weeks) to diploma programs (2-3 years), depending on the trade area and department.</p>
                        </details>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('contactForm');
        
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const fullname = document.getElementById('fullname');
            const email = document.getElementById('email');
            const subject = document.getElementById('subject');
            const message = document.getElementById('message');

            // Clear previous errors
            document.querySelectorAll('.error').forEach(el => el.classList.add('hidden'));

            // Validate Full Name
            if (fullname.value.trim().length < 3) {
                fullname.nextElementSibling.textContent = 'Full name must be at least 3 characters';
                fullname.nextElementSibling.classList.remove('hidden');
                isValid = false;
            }

            // Validate Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.nextElementSibling.textContent = 'Please enter a valid email address';
                email.nextElementSibling.classList.remove('hidden');
                isValid = false;
            }

            // Validate Subject
            if (subject.value.trim().length < 3) {
                subject.nextElementSibling.textContent = 'Subject must be at least 3 characters';
                subject.nextElementSibling.classList.remove('hidden');
                isValid = false;
            }

            // Validate Message
            if (message.value.trim().length < 10) {
                message.nextElementSibling.textContent = 'Message must be at least 10 characters';
                message.nextElementSibling.classList.remove('hidden');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>

<?php include 'includes/footer.php'; ?>
