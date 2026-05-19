<?php include("includes/header.php"); ?>

<!-- Image Slider -->
<div class="slider-container relative w-full h-96 md:h-[500px] flex items-center justify-center">
    <!-- Slide 1 -->
    <div class="slider-item active w-full h-full flex items-center justify-center text-white" style="background: url('assets/images/food2.jpg') no-repeat center center; background-size: cover;">
        <div class="text-center px-6">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Taste the Extraordinary</h2>
            <p class="text-lg md:text-2xl">Premium Catering & Event Dining Experiences</p>
            <a href="book.php" class="inline-block mt-6 bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-pink-50 transition">Book Us for Your Event</a>
        </div>
    </div>
    <!-- Slide 2 -->
    <div class="slider-item w-full h-full flex items-center justify-center text-white" style="background: url('assets/images/food3.jpg') no-repeat center center; background-size: cover;"> 
        <div class="text-center px-6">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Every Dish, A Masterpiece</h2>
            <p class="text-lg md:text-2xl">We Cook with Passion — You Celebrate in Style</p>
            <a href="menu.php" class="inline-block mt-6 bg-white text-pink-600 px-8 py-3 rounded-full font-semibold hover:bg-blue-50 transition">View Our Menu</a>
        </div>
    </div>
    <!-- Slide 3 -->
    <div class="slider-item w-full h-full flex items-center justify-center text-white" style="background: url('assets/images/food5.jpg') no-repeat center center; background-size: cover;">
        <div class="text-center px-6">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Weddings. Birthdays. Corporates.</h2>
            <p class="text-lg md:text-2xl">No Event Too Big, No Detail Too Small</p>
            <a href="contact.php" class="inline-block mt-6 bg-white text-blue-700 px-8 py-3 rounded-full font-semibold hover:bg-pink-50 transition">Get a Quote</a>
        </div>
    </div>

    <!-- Slider Controls -->
    <button onclick="prevSlide()" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-40 text-white p-3 hover:bg-pink-600 transition z-10">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button onclick="nextSlide()" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-40 text-white p-3 hover:bg-pink-600 transition z-10">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Slider Indicators -->
    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
        <span class="slider-dot w-3 h-3 bg-white rounded-full cursor-pointer opacity-100" onclick="currentSlide(0)"></span>
        <span class="slider-dot w-3 h-3 bg-white rounded-full cursor-pointer opacity-50" onclick="currentSlide(1)"></span>
        <span class="slider-dot w-3 h-3 bg-white rounded-full cursor-pointer opacity-50" onclick="currentSlide(2)"></span>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <!-- About Section -->
    <section class="mb-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <p class="text-pink-500 font-semibold tracking-widest text-sm uppercase mb-2">Who We Are</p>
                <h2 class="text-4xl font-bold text-blue-900 mb-4">Nigeria's Most Trusted Event Caterers</h2>
                <p class="text-gray-700 text-lg leading-relaxed mb-4">
                    Odela Events is a premier catering brand dedicated to delivering unforgettable dining experiences at every occasion. From intimate gatherings to grand celebrations, we bring world-class cuisine directly to your event.
                </p>
                <p class="text-gray-700 text-lg leading-relaxed mb-6">
                    Our team of expert chefs and event coordinators work closely with you to craft a menu that perfectly matches your taste, culture, and vision — because every event deserves extraordinary food.
                </p>
                <a href="book.php" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-full font-semibold hover:bg-pink-600 transition">
                    Book Us Today
                </a>
            </div>
            <div class="rounded-2xl p-8 text-center" style="background: linear-gradient(135deg, #dbeafe 0%, #fce7f3 100%);">
                <i class="fas fa-utensils text-6xl text-pink-500 mb-4"></i>
                <h3 class="text-2xl font-bold text-blue-900 mb-2">Food Made with Love</h3>
                <p class="text-gray-700">Every dish we prepare is crafted with the finest ingredients, professional expertise, and heartfelt passion for great food.</p>
            </div>
        </div>
    </section>

    <!-- Our Specialties Section -->
    <section class="mb-16">
        <p class="text-pink-500 font-semibold tracking-widest text-sm uppercase text-center mb-2">What We Serve</p>
        <h2 class="text-4xl font-bold text-blue-900 mb-12 text-center">Our Signature Dishes & Services</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#dbeafe;">
                    <i class="fas fa-drumstick-bite text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Nigerian Delicacies</h3>
                <p class="text-gray-600">Authentic jollof rice, egusi soup, peppered turkey, and all your favourite local dishes prepared to perfection.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#fce7f3;">
                    <i class="fas fa-birthday-cake text-3xl text-pink-500"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Desserts & Pastries</h3>
                <p class="text-gray-600">Elegant cakes, custom dessert bars, and sweet treats crafted to sweeten every celebration.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#dbeafe;">
                    <i class="fas fa-wine-glass-alt text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Drinks & Cocktails</h3>
                <p class="text-gray-600">Refreshing mocktails, fruit punches, and premium drink packages to keep your guests hydrated and happy.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#fce7f3;">
                    <i class="fas fa-globe-africa text-3xl text-pink-500"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Continental Cuisine</h3>
                <p class="text-gray-600">International favourites including grills, pasta, rice dishes and fusion menus for cosmopolitan events.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#dbeafe;">
                    <i class="fas fa-concierge-bell text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Buffet Setup</h3>
                <p class="text-gray-600">Professionally arranged and served buffet stations with live cooking options for large events.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#fce7f3;">
                    <i class="fas fa-heart text-3xl text-pink-500"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Wedding Catering</h3>
                <p class="text-gray-600">Make your big day unforgettable with bespoke wedding menus tailored to your love story and guest list.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#dbeafe;">
                    <i class="fas fa-building text-3xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Corporate Events</h3>
                <p class="text-gray-600">Polished, professional catering for conferences, product launches, office parties, and board dinners.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6 hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="rounded-xl p-4 mb-4 text-center" style="background:#fce7f3;">
                    <i class="fas fa-child text-3xl text-pink-500"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Kids' Parties</h3>
                <p class="text-gray-600">Fun, colourful, and delicious menus designed to delight little guests and make parents proud.</p>
            </div>

        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="mb-16 rounded-2xl p-8" style="background: linear-gradient(135deg, #eff6ff 0%, #fdf2f8 100%);">
        <p class="text-pink-500 font-semibold tracking-widest text-sm uppercase text-center mb-2">Our Promise</p>
        <h2 class="text-4xl font-bold text-blue-900 mb-12 text-center">Why Clients Choose Odela Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

            <div class="text-center">
                <div class="text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #1D4ED8, #ec4899);">
                    <i class="fas fa-hat-chef text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Expert Chefs</h3>
                <p class="text-gray-700">Our culinary team brings years of professional training and a true passion for creating outstanding food.</p>
            </div>

            <div class="text-center">
                <div class="text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #1D4ED8, #ec4899);">
                    <i class="fas fa-leaf text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Fresh Ingredients</h3>
                <p class="text-gray-700">We source only the freshest, highest-quality ingredients to guarantee taste and food safety at every event.</p>
            </div>

            <div class="text-center">
                <div class="text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #1D4ED8, #ec4899);">
                    <i class="fas fa-calendar-check text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">On-Time Delivery</h3>
                <p class="text-gray-700">We respect your schedule. Food is always ready, served, and cleaned up exactly when it should be.</p>
            </div>

            <div class="text-center">
                <div class="text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #1D4ED8, #ec4899);">
                    <i class="fas fa-sliders-h text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Custom Menus</h3>
                <p class="text-gray-700">Every menu is tailored to your event type, guest count, dietary needs, and personal preferences.</p>
            </div>

            <div class="text-center">
                <div class="text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #1D4ED8, #ec4899);">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">5-Star Experience</h3>
                <p class="text-gray-700">From setup to service to cleanup — we deliver a seamless, elegant catering experience every time.</p>
            </div>

            <div class="text-center">
                <div class="text-white rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #1D4ED8, #ec4899);">
                    <i class="fas fa-tags text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-blue-900 mb-2">Affordable Packages</h3>
                <p class="text-gray-700">Premium quality doesn't have to break the bank. We offer flexible packages for every budget.</p>
            </div>

        </div>
    </section>

    <!-- CTA Section -->
    <section class="rounded-2xl p-12 text-center text-white mb-16" style="background: linear-gradient(135deg, #1D4ED8 0%, #ec4899 100%);">
        <i class="fas fa-utensils text-5xl mb-4 opacity-80"></i>
        <h2 class="text-4xl font-bold mb-4">Ready to Make Your Event Unforgettable?</h2>
        <p class="text-xl mb-8 opacity-90">Book Odela Events today and let us handle the food while you enjoy every moment.</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="book.php" class="inline-block bg-white text-blue-600 px-8 py-3 rounded-full font-semibold hover:bg-pink-50 transition">
                Book Now
            </a>
            <a href="contact.php" class="inline-block border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-pink-600 transition">
                Contact Us
            </a>
        </div>
    </section>

</div>

<script src="https://cdn.tailwindcss.com"></script>

<!-- Slider Script — unchanged -->
<script>
    let currentSlideIndex = 0;
    const slideInterval = 5000;

    function showSlide(n) {
        const slides = document.querySelectorAll('.slider-item');
        const dots = document.querySelectorAll('.slider-dot');

        if (n >= slides.length) currentSlideIndex = 0;
        if (n < 0) currentSlideIndex = slides.length - 1;

        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.style.opacity = '0.5');

        slides[currentSlideIndex].classList.add('active');
        dots[currentSlideIndex].style.opacity = '1';
    }

    function nextSlide() { currentSlideIndex++; showSlide(currentSlideIndex); }
    function prevSlide() { currentSlideIndex--; showSlide(currentSlideIndex); }
    function currentSlide(n) { currentSlideIndex = n; showSlide(currentSlideIndex); }

    setInterval(nextSlide, slideInterval);
</script>

<style>
    .slider-item { display: none; position: absolute; width: 100%; height: 100%; }
    .slider-item.active { display: flex; }
</style>

<?php include 'includes/footer.php'; ?>