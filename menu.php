<?php include("includes/header.php"); ?>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=Inter:wght@300;400;500;600;700&display=swap');

  :root {
    --blue:       #1D4ED8;
    --blue-dark:  #1E3A8A;
    --blue-light: #DBEAFE;
    --pink:       #EC4899;
    --pink-dark:  #BE185D;
    --pink-light: #FCE7F3;
    --off-white:  #F8F7FF;
  }

  body { font-family: 'Inter', sans-serif; background: var(--off-white); }

  .font-display { font-family: 'Playfair Display', serif; }

  /* ── Hero Banner ── */
  .menu-hero {
    background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 45%, #EC4899 100%);
    padding: 80px 0 60px;
    position: relative;
    overflow: hidden;
  }
  .menu-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
  }

  /* ── Category Filter Pills ── */
  .filter-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 22px; border-radius: 50px; font-size: 13px;
    font-weight: 600; cursor: pointer; border: 2px solid transparent;
    transition: all 0.25s ease; white-space: nowrap;
  }
  .filter-pill.active {
    background: linear-gradient(135deg, var(--blue), var(--pink));
    color: #fff; border-color: transparent;
  }
  .filter-pill:not(.active) {
    background: #fff; color: #374151;
    border-color: #e5e7eb;
  }
  .filter-pill:not(.active):hover {
    border-color: var(--pink); color: var(--pink);
  }

  /* ── Menu Card ── */
  .menu-card {
    background: #fff; border-radius: 20px;
    overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #f0f0f0;
  }
  .menu-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 50px rgba(29,78,216,0.12);
  }

  /* ── Image Wrapper ── */
  .card-img-wrap {
    position: relative; width: 100%; height: 220px;
    background: linear-gradient(135deg, #dbeafe, #fce7f3);
    overflow: hidden;
  }
  .card-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s ease;
  }
  .menu-card:hover .card-img-wrap img { transform: scale(1.05); }

  /* Placeholder when no image */
  .card-img-wrap .img-placeholder {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: var(--pink); font-size: 13px; font-weight: 600;
    gap: 8px; letter-spacing: 0.5px;
  }
  .card-img-wrap .img-placeholder i { font-size: 40px; opacity: 0.5; }

  /* Badge on image */
  .card-badge {
    position: absolute; top: 14px; left: 14px;
    padding: 5px 12px; border-radius: 50px;
    font-size: 11px; font-weight: 700; letter-spacing: 0.8px;
    text-transform: uppercase;
  }
  .badge-popular {
    background: linear-gradient(135deg, var(--pink), #f43f5e);
    color: #fff;
  }
  .badge-new {
    background: linear-gradient(135deg, var(--blue), #06b6d4);
    color: #fff;
  }
  .badge-veg {
    background: #dcfce7; color: #15803d;
  }

  /* Price tag */
  .price-tag {
    position: absolute; bottom: 14px; right: 14px;
    background: rgba(255,255,255,0.95); backdrop-filter: blur(4px);
    padding: 6px 14px; border-radius: 50px;
    font-size: 15px; font-weight: 700; color: var(--blue-dark);
  }

  /* ── Card Body ── */
  .card-body { padding: 20px 22px 22px; }
  .card-body h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem; color: #111827; margin-bottom: 6px;
  }
  .card-body p { font-size: 13px; color: #6b7280; line-height: 1.65; margin-bottom: 14px; }

  .card-meta {
    display: flex; align-items: center; justify-content: space-between;
    padding-top: 14px; border-top: 1px solid #f3f4f6;
  }
  .card-tags { display: flex; gap: 6px; flex-wrap: wrap; }
  .card-tag {
    font-size: 10px; font-weight: 700; letter-spacing: 0.8px;
    text-transform: uppercase; padding: 4px 10px; border-radius: 50px;
    background: var(--blue-light); color: var(--blue-dark);
  }
  .card-tag.pink { background: var(--pink-light); color: var(--pink-dark); }

  .book-btn {
    font-size: 12px; font-weight: 700; letter-spacing: 0.5px;
    padding: 8px 18px; border-radius: 50px; border: none; cursor: pointer;
    background: linear-gradient(135deg, var(--blue), var(--pink));
    color: #fff; transition: opacity 0.2s, transform 0.2s;
    white-space: nowrap;
  }
  .book-btn:hover { opacity: 0.9; transform: scale(1.03); }

  /* ── Feature Strip ── */
  .feature-strip {
    background: linear-gradient(135deg, var(--blue-dark), var(--blue));
    padding: 24px 0;
  }
  .feature-item {
    display: flex; align-items: center; gap: 10px;
    color: rgba(255,255,255,0.9); font-size: 13px; font-weight: 500;
  }
  .feature-item i { color: var(--pink); font-size: 18px; }

  /* ── Special Packages ── */
  .package-card {
    border-radius: 20px; overflow: hidden;
    position: relative; color: #fff;
  }
  .package-card-img {
    width: 100%; height: 200px;
    background: linear-gradient(135deg, #1e3a8a, #ec4899);
    position: relative; overflow: hidden;
  }
  .package-card-img img {
    width: 100%; height: 100%; object-fit: cover; opacity: 0.6;
  }
  .package-card-img .img-placeholder {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 60px; opacity: 0.3;
  }
  .package-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(14,30,80,0.9) 0%, rgba(14,30,80,0.3) 100%);
    padding: 20px; display: flex; flex-direction: column; justify-content: flex-end;
  }

  /* ── Testimonial ── */
  .testi-card {
    background: #fff; border-radius: 16px; padding: 28px;
    border: 1px solid #f0f0f0; position: relative;
  }
  .testi-card::before {
    content: '\201C';
    font-family: 'Playfair Display', serif;
    font-size: 5rem; color: var(--pink-light);
    position: absolute; top: 10px; left: 20px; line-height: 1;
    pointer-events: none;
  }

  /* ── CTA Banner ── */
  .cta-section {
    background: linear-gradient(135deg, #1D4ED8 0%, #EC4899 100%);
    border-radius: 24px; padding: 60px 40px; text-align: center;
  }
</style>

<!-- ═══════════════════════════════════════
     HERO BANNER
════════════════════════════════════════ -->
<section class="menu-hero">
  <div class="relative z-10 max-w-4xl mx-auto px-6 text-center">
    <span style="display:inline-block; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.25); color:#fff; font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; padding:7px 20px; border-radius:50px; margin-bottom:20px;">
       Our Full Menu 
    </span>
    <h1 class="font-display text-white mb-4" style="font-size: clamp(2.2rem,5vw,3.8rem); font-weight:900; line-height:1.15;">
      Dishes Crafted to Make <br><em style="color:#f9a8d4;">Every Moment</em> Memorable
    </h1>
    <p style="color:rgba(255,255,255,0.82); font-size:17px; max-width:560px; margin:0 auto 32px; line-height:1.7;">
      Browse our curated selection of signature dishes, special packages, and seasonal favourites — all available for your next event.
    </p>
    <div style="display:flex; gap:14px; justify-content:center; flex-wrap:wrap;">
      <a href="book.php" style="background:#fff; color:var(--blue-dark); font-weight:700; font-size:14px; padding:14px 32px; border-radius:50px; text-decoration:none; transition:opacity 0.2s;">
        Book for My Event
      </a>
      <a href="#menu-grid" style="background:rgba(255,255,255,0.12); color:#fff; font-weight:600; font-size:14px; padding:14px 32px; border-radius:50px; text-decoration:none; border:2px solid rgba(255,255,255,0.35); transition:all 0.2s;">
        Browse Menu ↓
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     FEATURE STRIP
════════════════════════════════════════ -->
<div class="feature-strip">
  <div class="max-w-5xl mx-auto px-6">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
      <div class="feature-item"><i class="fas fa-leaf"></i> Fresh Ingredients Daily</div>
      <div class="feature-item"><i class="fas fa-hat-chef"></i> Expert Chefs On-Site</div>
      <div class="feature-item"><i class="fas fa-concierge-bell"></i> Full Service Setup</div>
      <div class="feature-item"><i class="fas fa-phone-alt"></i> 24hr Event Support</div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════
     FILTER PILLS + MENU GRID
════════════════════════════════════════ -->
<section id="menu-grid" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

  <!-- Section heading -->
  <div class="text-center mb-10">
    <p style="color:var(--pink); font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; margin-bottom:8px;">What We Serve</p>
    <h2 class="font-display" style="font-size:2.4rem; color:#111827; font-weight:900;">Explore Our Signature <em style="color:var(--pink);">Menu</em></h2>
  </div>

  <!-- Category Filter -->
  <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-bottom:40px;">
    <button class="filter-pill active" onclick="filterMenu('all', this)"><i class="fas fa-border-all"></i> All Dishes</button>
    <button class="filter-pill" onclick="filterMenu('nigerian', this)"><i class="fas fa-drumstick-bite"></i> Nigerian</button>
    <button class="filter-pill" onclick="filterMenu('continental', this)"><i class="fas fa-globe-africa"></i> Continental</button>
    <button class="filter-pill" onclick="filterMenu('grills', this)"><i class="fas fa-fire"></i> Grills & BBQ</button>
    <button class="filter-pill" onclick="filterMenu('desserts', this)"><i class="fas fa-birthday-cake"></i> Desserts</button>
    <button class="filter-pill" onclick="filterMenu('drinks', this)"><i class="fas fa-wine-glass-alt"></i> Drinks</button>
    <button class="filter-pill" onclick="filterMenu('small-chops', this)"><i class="fas fa-utensils"></i> Small Chops</button>
  </div>

  <!-- ── MENU GRID ── -->
  <div id="menuGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-7">

    <!-- CARD 1 -->
    <div class="menu-card" data-category="nigerian">
      <div class="card-img-wrap">
        <!-- ADD YOUR IMAGE HERE -->
        <img src="assets/images/food3.jpg" alt="Party Jollof Rice"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-utensils"></i>Add Image
        </div>
        <span class="card-badge badge-popular">⭐ Most Ordered</span>
        <span class="price-tag">From ₦3,500/plate</span>
      </div>
      <div class="card-body">
        <h3>Party Jollof Rice</h3>
        <p>Smoky, perfectly seasoned Nigerian party jollof, cooked fireside and served with coleslaw and fried plantain.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Nigerian</span>
            <span class="card-tag pink">Signature</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=jollof-rice'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 2 -->
    <div class="menu-card" data-category="nigerian">
      <div class="card-img-wrap">
        <img src="assets/images/food5.jpg" alt="Egusi Soup"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-bowl-food"></i>Add Image
        </div>
        <span class="price-tag">From ₦4,000/plate</span>
      </div>
      <div class="card-body">
        <h3>Egusi Soup & Swallow</h3>
        <p>Rich, meaty egusi soup loaded with assorted proteins, served with your choice of pounded yam or eba.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Nigerian</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=egusi-soup'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 3 -->
    <div class="menu-card" data-category="grills">
      <div class="card-img-wrap">
        <img src="assets/images/food2.jpg" alt="Peppered Turkey"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-drumstick-bite"></i>Add Image
        </div>
        <span class="card-badge badge-popular">🔥 Fan Favourite</span>
        <span class="price-tag">From ₦6,500/kg</span>
      </div>
      <div class="card-body">
        <h3>Peppered Turkey</h3>
        <p>Slow-marinated whole turkey, oven-roasted and finished in our signature pepper sauce. A showstopper at every event.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Grills</span>
            <span class="card-tag pink">Spicy</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=peppered-turkey'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 4 -->
    <div class="menu-card" data-category="continental">
      <div class="card-img-wrap">
        <img src="assets/images/food4.jpg" alt="Special Fried Rice"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-utensils"></i>Add Image
        </div>
        <span class="card-badge badge-new">✦ New</span>
        <span class="price-tag">From ₦3,200/plate</span>
      </div>
      <div class="card-body">
        <h3>Special Fried Rice</h3>
        <p>Golden wok-fried rice with mixed vegetables, prawns, chicken liver, and a secret blend of spices.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Continental</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=fried-rice'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 5 -->
    <div class="menu-card" data-category="grills">
      <div class="card-img-wrap">
        <img src="assets/images/food6.jpg" alt="Suya Platter"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-fire"></i>Add Image
        </div>
        <span class="price-tag">From ₦5,000/platter</span>
      </div>
      <div class="card-body">
        <h3>Suya Platter</h3>
        <p>Thin-sliced spiced beef skewers served with fresh tomatoes, onions, and suya powder. Crowd-pleasing at every party.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Grills</span>
            <span class="card-tag pink">Signature</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=suya'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 6 -->
    <div class="menu-card" data-category="small-chops">
      <div class="card-img-wrap">
        <img src="assets/images/food4.jpg" alt="Small Chops Platter"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-utensils"></i>Add Image
        </div>
        <span class="card-badge badge-popular">⭐ Event Must-Have</span>
        <span class="price-tag">From ₦2,500/person</span>
      </div>
      <div class="card-body">
        <h3>Small Chops Platter</h3>
        <p>Puff puff, spring rolls, samosa, asun, and mini skewers elegantly arranged and served by our waitstaff.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Small Chops</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=small-chops'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 7 -->
    <div class="menu-card" data-category="desserts">
      <div class="card-img-wrap">
        <img src="assets/images/food2.jpg" alt="Custom Wedding Cake"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-birthday-cake"></i>Add Image
        </div>
        <span class="card-badge badge-new">✦ Custom Order</span>
        <span class="price-tag">From ₦45,000</span>
      </div>
      <div class="card-body">
        <h3>Custom Event Cakes</h3>
        <p>Tiered wedding cakes, birthday cakes, and themed celebration cakes designed to match your event's style.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag pink">Desserts</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=custom-cake'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 8 -->
    <div class="menu-card" data-category="desserts">
      <div class="card-img-wrap">
        <img src="assets/images/food7.jpg" alt="Dessert Bar"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-cookie-bite"></i>Add Image
        </div>
        <span class="price-tag">From ₦1,500/person</span>
      </div>
      <div class="card-body">
        <h3>Dessert Bar Setup</h3>
        <p>A fully styled dessert station with cupcakes, macarons, brownies, and mini tarts — a visual treat and a tasty one.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag pink">Desserts</span>
            <span class="card-tag pink">Setup</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=dessert-bar'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 9 -->
    <div class="menu-card" data-category="drinks">
      <div class="card-img-wrap">
        <img src="assets/images/food2.jpg" alt="Chapman Cocktail"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-wine-glass-alt"></i>Add Image
        </div>
        <span class="card-badge badge-popular">⭐ Top Pick</span>
        <span class="price-tag">From ₦800/glass</span>
      </div>
      <div class="card-body">
        <h3>Chapman & Mocktails</h3>
        <p>Classic Nigerian chapman, fruit punch, zobo, and custom mocktail mixes served fresh throughout your event.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Drinks</span>
            <span class="card-tag pink">Non-Alcoholic</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=drinks'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 10 -->
    <div class="menu-card" data-category="continental">
      <div class="card-img-wrap">
        <img src="assets/images/food3.jpg" alt="Creamy Pasta"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-utensils"></i>Add Image
        </div>
        <span class="card-badge badge-veg">🌿 Veg Option</span>
        <span class="price-tag">From ₦3,000/plate</span>
      </div>
      <div class="card-body">
        <h3>Creamy Pasta</h3>
        <p>Penne or fettuccine in a rich creamy tomato sauce with grilled chicken or vegetables. Available in bulk for large events.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Continental</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=pasta'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 11 -->
    <div class="menu-card" data-category="nigerian">
      <div class="card-img-wrap">
        <img src="assets/images/food5.jpg" alt="Ofada Rice & Stew"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-drumstick-bite"></i>Add Image
        </div>
        <span class="card-badge badge-new">✦ New Addition</span>
        <span class="price-tag">From ₦3,800/plate</span>
      </div>
      <div class="card-body">
        <h3>Ofada Rice & Ayamase</h3>
        <p>Locally grown ofada rice wrapped in banana leaf, served with spicy green pepper ayamase stew and assorted offals.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Nigerian</span>
            <span class="card-tag pink">Spicy</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=ofada'">Book</button>
        </div>
      </div>
    </div>

    <!-- CARD 12 -->
    <div class="menu-card" data-category="grills">
      <div class="card-img-wrap">
        <img src="assets/images/food2.jpg" alt="BBQ Whole Fish"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="img-placeholder" style="display:none;">
          <i class="fas fa-fish"></i>Add Image
        </div>
        <span class="price-tag">From ₦7,000/fish</span>
      </div>
      <div class="card-body">
        <h3>BBQ Whole Fish</h3>
        <p>Fresh whole tilapia or catfish marinated in spices, charcoal-grilled to smoky perfection and served with peppered sauce.</p>
        <div class="card-meta">
          <div class="card-tags">
            <span class="card-tag">Grills</span>
          </div>
          <button class="book-btn" onclick="location.href='book.php?dish=bbq-fish'">Book</button>
        </div>
      </div>
    </div>

  </div><!-- /menuGrid -->
</section>

<!-- ═══════════════════════════════════════
     EVENT PACKAGES
════════════════════════════════════════ -->
<section style="background:#fff; padding:80px 0;">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-12">
      <p style="color:var(--pink); font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; margin-bottom:8px;">Tailored For You</p>
      <h2 class="font-display" style="font-size:2.3rem; font-weight:900; color:#111827;">Event <em style="color:var(--blue-dark);">Packages</em></h2>
      <p style="color:#6b7280; margin-top:10px; font-size:15px; max-width:480px; margin-left:auto; margin-right:auto;">
        Choose a package or let us build a fully custom menu around your event type and guest count.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

      <!-- Package 1 -->
      <div style="border-radius:20px; overflow:hidden; border:2px solid #e5e7eb;">
        <div style="height:200px; background:linear-gradient(135deg,#DBEAFE,#FCE7F3); position:relative; overflow:hidden;">
          <!-- ADD YOUR PACKAGE IMAGE HERE -->
          <img src="images/packages/intimate.jpg" alt="Intimate Gathering"
               style="width:100%;height:100%;object-fit:cover;" onerror="this.style.opacity=0">
          <div style="position:absolute;inset:0;background:rgba(30,58,138,0.6);display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff;gap:8px;">
            <i class="fas fa-heart" style="font-size:36px;color:#f9a8d4;"></i>
            <span style="font-size:13px;font-weight:600;letter-spacing:1px;">INTIMATE PACKAGE</span>
          </div>
        </div>
        <div style="padding:24px;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <h3 class="font-display" style="font-size:1.2rem;font-weight:700;color:#111827;">Intimate Gathering</h3>
            <span style="background:var(--blue-light);color:var(--blue-dark);font-size:12px;font-weight:700;padding:5px 12px;border-radius:50px;">Up to 50 guests</span>
          </div>
          <p style="color:#6b7280;font-size:13px;line-height:1.7;margin-bottom:16px;">
            Perfect for baby showers, birthday dinners, and small family events. Includes 3 dishes, drinks station, and 2 waitstaff.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 20px;display:flex;flex-direction:column;gap:8px;">
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> 3-dish menu of choice</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> Drinks & mocktails</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> 2 professional waitstaff</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> Setup & cleanup included</li>
          </ul>
          <a href="book.php?package=intimate" style="display:block;text-align:center;background:linear-gradient(135deg,var(--blue),var(--pink));color:#fff;font-weight:700;font-size:13px;padding:13px;border-radius:50px;text-decoration:none;">Book This Package</a>
        </div>
      </div>

      <!-- Package 2 — Featured -->
      <div style="border-radius:20px; overflow:hidden; border:3px solid var(--pink); box-shadow:0 20px 50px rgba(236,72,153,0.15); position:relative;">
        <div style="position:absolute;top:0;left:0;right:0;text-align:center;z-index:10;">
          <span style="background:linear-gradient(135deg,var(--pink),var(--blue));color:#fff;font-size:11px;font-weight:700;letter-spacing:1.5px;padding:6px 20px;border-radius:0 0 12px 12px;display:inline-block;">⭐ MOST POPULAR</span>
        </div>
        <div style="height:200px; background:linear-gradient(135deg,#1D4ED8,#EC4899); position:relative; overflow:hidden;">
          <!-- ADD YOUR PACKAGE IMAGE HERE -->
          <img src="assetsimages/food5.jpg" alt="Grand Celebration"
               style="width:100%;height:100%;object-fit:cover;opacity:0.6;" onerror="this.style.opacity=0">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff;gap:8px;">
            <i class="fas fa-glass-cheers" style="font-size:36px;color:#fde68a;"></i>
            <span style="font-size:13px;font-weight:600;letter-spacing:1px;">CELEBRATION PACKAGE</span>
          </div>
        </div>
        <div style="padding:24px;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <h3 class="font-display" style="font-size:1.2rem;font-weight:700;color:#111827;">Grand Celebration</h3>
            <span style="background:var(--pink-light);color:var(--pink-dark);font-size:12px;font-weight:700;padding:5px 12px;border-radius:50px;">Up to 200 guests</span>
          </div>
          <p style="color:#6b7280;font-size:13px;line-height:1.7;margin-bottom:16px;">
            Ideal for weddings, naming ceremonies, and milestone birthdays. Full buffet service with live cooking stations.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 20px;display:flex;flex-direction:column;gap:8px;">
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> Full buffet — 6+ dishes</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> Live grilling station</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> Small chops on arrival</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> Drinks & dessert bar</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--pink);"></i> 6 waitstaff + coordinator</li>
          </ul>
          <a href="book.php?package=celebration" style="display:block;text-align:center;background:linear-gradient(135deg,var(--pink),var(--blue));color:#fff;font-weight:700;font-size:13px;padding:13px;border-radius:50px;text-decoration:none;">Book This Package</a>
        </div>
      </div>

      <!-- Package 3 -->
      <div style="border-radius:20px; overflow:hidden; border:2px solid #e5e7eb;">
        <div style="height:200px; background:linear-gradient(135deg,#1E3A8A,#1D4ED8); position:relative; overflow:hidden;">
          <!-- ADD YOUR PACKAGE IMAGE HERE -->
          <img src="assetsimages/food3.jpg" alt="Corporate Package"
               style="width:100%;height:100%;object-fit:cover;opacity:0.6;" onerror="this.style.opacity=0">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#fff;gap:8px;">
            <i class="fas fa-building" style="font-size:36px;color:#93c5fd;"></i>
            <span style="font-size:13px;font-weight:600;letter-spacing:1px;">CORPORATE PACKAGE</span>
          </div>
        </div>
        <div style="padding:24px;">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
            <h3 class="font-display" style="font-size:1.2rem;font-weight:700;color:#111827;">Corporate Events</h3>
            <span style="background:var(--blue-light);color:var(--blue-dark);font-size:12px;font-weight:700;padding:5px 12px;border-radius:50px;">Any size</span>
          </div>
          <p style="color:#6b7280;font-size:13px;line-height:1.7;margin-bottom:16px;">
            Polished, professional catering for conferences, product launches, and executive dinners. Plated or buffet style.
          </p>
          <ul style="list-style:none;padding:0;margin:0 0 20px;display:flex;flex-direction:column;gap:8px;">
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--blue);"></i> Custom branded setup</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--blue);"></i> Plated or buffet service</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--blue);"></i> Tea, coffee & refreshments</li>
            <li style="font-size:13px;color:#374151;display:flex;gap:8px;align-items:center;"><i class="fas fa-check-circle" style="color:var(--blue);"></i> Invoicing & receipt</li>
          </ul>
          <a href="book.php?package=corporate" style="display:block;text-align:center;background:linear-gradient(135deg,var(--blue-dark),var(--blue));color:#fff;font-weight:700;font-size:13px;padding:13px;border-radius:50px;text-decoration:none;">Get a Quote</a>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     TESTIMONIALS
════════════════════════════════════════ -->
<section style="padding:80px 0; background:var(--off-white);">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="text-center mb-12">
      <p style="color:var(--pink); font-size:11px; font-weight:700; letter-spacing:3px; text-transform:uppercase; margin-bottom:8px;">Happy Clients</p>
      <h2 class="font-display" style="font-size:2.2rem; font-weight:900; color:#111827;">What Our Guests <em style="color:var(--pink);">Are Saying</em></h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-7">

      <div class="testi-card">
        <div style="position:relative;z-index:1;padding-top:20px;">
          <p style="color:#374151;font-size:14px;line-height:1.75;font-style:italic;margin-bottom:20px;">
            "Odela Events made our wedding reception absolutely magical. The jollof rice alone had guests going back for thirds. Absolutely no complaints — pure perfection."
          </p>
          <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--pink));display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;flex-shrink:0;">FA</div>
            <div>
              <strong style="font-size:14px;color:#111827;display:block;">Fatima & Ahmed</strong>
              <span style="font-size:12px;color:#9ca3af;">Wedding Reception, Lagos</span>
            </div>
          </div>
          <div style="margin-top:12px;color:#f59e0b;font-size:14px;">★★★★★</div>
        </div>
      </div>

      <div class="testi-card" style="border:2px solid var(--pink-light);">
        <div style="position:relative;z-index:1;padding-top:20px;">
          <p style="color:#374151;font-size:14px;line-height:1.75;font-style:italic;margin-bottom:20px;">
            "We booked Odela for our company's annual dinner and the feedback from 300 staff was overwhelmingly positive. Professional, punctual, and delicious. Will definitely use them again."
          </p>
          <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--pink),#f43f5e);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;flex-shrink:0;">CB</div>
            <div>
              <strong style="font-size:14px;color:#111827;display:block;">Chioma Bello</strong>
              <span style="font-size:12px;color:#9ca3af;">Corporate Event, Abuja</span>
            </div>
          </div>
          <div style="margin-top:12px;color:#f59e0b;font-size:14px;">★★★★★</div>
        </div>
      </div>

      <div class="testi-card">
        <div style="position:relative;z-index:1;padding-top:20px;">
          <p style="color:#374151;font-size:14px;line-height:1.75;font-style:italic;margin-bottom:20px;">
            "The small chops were gone in 20 minutes — that tells you everything! The team was friendly, the presentation was stunning, and the cleanup was spotless. 10/10."
          </p>
          <div style="display:flex;gap:12px;align-items:center;">
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dark),var(--blue));display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;flex-shrink:0;">TO</div>
            <div>
              <strong style="font-size:14px;color:#111827;display:block;">Tunde Okafor</strong>
              <span style="font-size:12px;color:#9ca3af;">Birthday Party, Port Harcourt</span>
            </div>
          </div>
          <div style="margin-top:12px;color:#f59e0b;font-size:14px;">★★★★★</div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════
     CTA
════════════════════════════════════════ -->
<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
  <div class="cta-section">
    <i class="fas fa-utensils text-white mb-4" style="font-size:48px;opacity:0.7;display:block;"></i>
    <h2 class="font-display text-white mb-4" style="font-size:2.2rem;font-weight:900;">
      Seen Something You Love?
    </h2>
    <p style="color:rgba(255,255,255,0.85);font-size:17px;max-width:480px;margin:0 auto 32px;line-height:1.7;">
      Tell us your event date, guest count, and favourite dishes — we'll create a custom proposal just for you within 24 hours.
    </p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="book.php" style="background:#fff;color:var(--blue-dark);font-weight:700;font-size:15px;padding:15px 36px;border-radius:50px;text-decoration:none;">
        Book Now
      </a>
      <a href="contact.php" style="background:rgba(255,255,255,0.12);color:#fff;font-weight:600;font-size:15px;padding:15px 36px;border-radius:50px;text-decoration:none;border:2px solid rgba(255,255,255,0.4);">
        Ask a Question
      </a>
    </div>
  </div>
</section>

<!-- ── Filter Script ── -->
<script>
function filterMenu(category, clickedPill) {
    document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
    clickedPill.classList.add('active');

    document.querySelectorAll('#menuGrid .menu-card').forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>