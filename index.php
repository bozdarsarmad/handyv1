<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Handy — Certificate Services</title>
  <meta name="description" content="Fast, reliable certificate and document services across Sind. Matric, Intermediate, University certificates delivered to your door.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400&family=Crimson+Pro:ital,wght@0,400;0,500;0,600;1,400&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📜</text></svg>">
</head>
<body>

<!-- LOADING SCREEN -->
<div id="loading-screen">
  <div class="loading-logo">✦ HANDY ✦</div>
  <div style="font-family:'Crimson Pro',serif;color:#c9921a;letter-spacing:3px;font-size:0.85rem;margin-top:-10px;">CERTIFICATE SERVICES</div>
  <div class="loading-bar"><div class="loading-bar-fill"></div></div>
</div>

<!-- TOAST -->
<div id="toast-container"></div>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav id="navbar">
  <div class="navbar-top">
    <a class="navbar-logo" href="#home">
      <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="36" height="36" rx="6" fill="#c9921a" opacity="0.2"/>
        <text x="18" y="26" font-size="20" text-anchor="middle" fill="#e8b84b">📜</text>
      </svg>
      <div>
        <div>HANDY</div>
        <div class="logo-tagline">Certificate Services</div>
      </div>
    </a>
    <div class="navbar-user" id="navbar-user-area">
      <!-- Rendered by JS -->
    </div>
    <button class="nav-hamburger" onclick="toggleMobileMenu()">☰</button>
  </div>

  <!-- Desktop Nav Links -->
  <div class="navbar-bottom" id="navbar-bottom">
    <a href="#home"  class="nav-link" data-route="home">🏠 Home</a>
    <a href="#cards" class="nav-link" data-route="cards" onclick="document.querySelector('.section-wrap')?.scrollIntoView({behavior:'smooth'});return false;">📋 Services</a>
    <a href="#orders" class="nav-link" data-route="orders">📦 My Orders</a>
    <a href="#profile" class="nav-link" data-route="profile">👤 Profile</a>
  </div>

  <!-- Mobile Menu -->
  <div class="mobile-menu" id="mobile-menu">
    <a href="#home"    onclick="toggleMobileMenu()">🏠 Home</a>
    <a href="#orders"  onclick="toggleMobileMenu()">📦 My Orders</a>
    <a href="#profile" onclick="toggleMobileMenu()">👤 Profile</a>
  </div>
</nav>

<!-- ============================================================
     APP — PAGES
     ============================================================ -->
<div id="app">

  <!-- =================== HOME PAGE =================== -->
  <div id="page-home" class="page">

    <!-- Slider -->
    <div id="slider-container">
      <div class="spinner" style="margin-top:60px;"></div>
    </div>

    <!-- Intro -->
    <div class="home-intro">
      <div class="ornamental-border">
        <h1>Welcome to Handy</h1>
        <p>Your most trusted source for fast, affordable & verified academic certificate services across Sind. From Karachi to Sukkur, we deliver your documents to your doorstep.</p>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-strip">
      <div class="stats-grid" id="stats-grid">
        <div class="spinner"></div>
      </div>
    </div>

    <!-- Services -->
    <div class="section-wrap" id="services-section">
      <div class="section-header">
        <h2>Our Certificate Services</h2>
        <div class="ornament-line">✦</div>
        <p>Select a service below and order your certificates with ease</p>
      </div>

      <div class="category-tabs" id="category-tabs">
        <!-- Rendered by JS -->
      </div>

      <div class="cards-grid" id="cards-grid">
        <div class="spinner"></div>
      </div>
    </div>

    <!-- How it works -->
    <div style="background:var(--parchment);padding:48px 24px;border-top:2px solid var(--border);">
      <div class="section-wrap" style="padding:0;">
        <div class="section-header">
          <h2>How It Works</h2>
          <div class="ornament-line">✦</div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;max-width:900px;margin:0 auto;text-align:center;">
          <div style="padding:24px;">
            <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
            <h3 style="font-family:var(--font-head);color:var(--mahogany);margin-bottom:8px;">1. Browse Services</h3>
            <p style="font-size:0.9rem;color:#5a4030;">Choose from Matric, Intermediate or University certificate services</p>
          </div>
          <div style="padding:24px;">
            <div style="font-size:2.5rem;margin-bottom:12px;">📝</div>
            <h3 style="font-family:var(--font-head);color:var(--mahogany);margin-bottom:8px;">2. Fill Order Form</h3>
            <p style="font-size:0.9rem;color:#5a4030;">Enter your academic info and shipping address</p>
          </div>
          <div style="padding:24px;">
            <div style="font-size:2.5rem;margin-bottom:12px;">💳</div>
            <h3 style="font-family:var(--font-head);color:var(--mahogany);margin-bottom:8px;">3. Make Payment</h3>
            <p style="font-size:0.9rem;color:#5a4030;">Pay via EasyPaisa, JazzCash or Bank Transfer</p>
          </div>
          <div style="padding:24px;">
            <div style="font-size:2.5rem;margin-bottom:12px;">🚚</div>
            <h3 style="font-family:var(--font-head);color:var(--mahogany);margin-bottom:8px;">4. Receive Delivery</h3>
            <p style="font-size:0.9rem;color:#5a4030;">Get your documents delivered in 24–48 hours</p>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /page-home -->

  <!-- =================== ORDER PAGE =================== -->
  <div id="page-order" class="page">
    <div class="order-page-wrap">
      <div id="order-content">
        <div class="spinner"></div>
      </div>
    </div>
  </div>

  <!-- =================== CHECKOUT PAGE =================== -->
  <div id="page-checkout" class="page">
    <div class="checkout-wrap">
      <div id="checkout-content">
        <div class="spinner"></div>
      </div>
    </div>
  </div>

  <!-- =================== ORDERS PAGE =================== -->
  <div id="page-orders" class="page">
    <div class="section-wrap">
      <div class="section-header">
        <h2>My Orders</h2>
        <div class="ornament-line">✦</div>
        <p>Track your orders and payment status</p>
      </div>
      <div id="orders-content">
        <div class="spinner"></div>
      </div>
    </div>
  </div>

  <!-- =================== PROFILE PAGE =================== -->
  <div id="page-profile" class="page">
    <div id="profile-content">
      <div class="spinner"></div>
    </div>
  </div>

</div><!-- /app -->

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <h3>✦ HANDY</h3>
      <p>Your trusted partner for academic certificate services across Sind. Fast processing, authentic documents, doorstep delivery.</p>
    </div>
    <div class="footer-section">
      <h4>Services</h4>
      <ul>
        <li><a href="#home">Matric Certificates</a></li>
        <li><a href="#home">Intermediate Certificates</a></li>
        <li><a href="#home">University Degrees</a></li>
      </ul>
    </div>
    <div class="footer-section">
      <h4>Account</h4>
      <ul>
        <li><a href="#orders">My Orders</a></li>
        <li><a href="#profile">My Profile</a></li>
        <li><a href="javascript:void(0)" onclick="openLoginModal()">Login / Register</a></li>
      </ul>
    </div>
    <div class="footer-section">
      <h4>Contact</h4>
      <ul>
        <li><a href="tel:+923001234567">📱 0300-1234567</a></li>
        <li><a href="mailto:info@handy.pk">📧 info@handy.pk</a></li>
        <li><a href="#">📍 Karachi, Sind</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>✦ &copy; <?= date('Y') ?> Handy Certificate Services. All rights reserved. ✦</p>
  </div>
</footer>

<!-- ============================================================
     MODALS
     ============================================================ -->

<!-- AUTH MODAL -->
<div class="modal-overlay" id="auth-modal">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <h3>✦ Account Access</h3>
      <button class="modal-close" onclick="closeAuthModal()">✕</button>
    </div>
    <div class="modal-body" style="padding-top:16px;">
      <div style="display:flex;gap:0;margin-bottom:20px;border:2px solid var(--border);border-radius:var(--radius);overflow:hidden;">
        <button class="auth-tab active" data-tab="login"    onclick="showAuthTab('login')"    style="flex:1;padding:10px;background:var(--mahogany);color:var(--gold-lt);font-family:var(--font-stamp);letter-spacing:1px;border:none;cursor:pointer;font-size:0.85rem;">Sign In</button>
        <button class="auth-tab"        data-tab="register" onclick="showAuthTab('register')" style="flex:1;padding:10px;background:var(--parchment);color:var(--mahogany);font-family:var(--font-stamp);letter-spacing:1px;border:none;cursor:pointer;font-size:0.85rem;">Register</button>
      </div>
      <style>
        .auth-tab.active { background:var(--mahogany)!important; color:var(--gold-lt)!important; }
        .auth-tab:not(.active) { background:var(--parchment)!important; color:var(--mahogany)!important; }
      </style>

      <!-- Login Form -->
      <div id="auth-login">
        <form onsubmit="handleLogin(event)">
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" id="login-email" class="form-input" placeholder="you@email.com" required>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" id="login-password" class="form-input" placeholder="••••••••" required>
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">Sign In</button>
        </form>
        <p style="text-align:center;margin-top:14px;font-size:0.88rem;color:var(--border);">No account? <a href="javascript:void(0)" onclick="showAuthTab('register')" style="color:var(--mahogany);">Register here</a></p>
      </div>

      <!-- Register Form -->
      <div id="auth-register" style="display:none;">
        <form onsubmit="handleRegister(event)">
          <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" id="reg-name" class="form-input" placeholder="Your full name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address <span class="required">*</span></label>
            <input type="email" id="reg-email" class="form-input" placeholder="you@email.com" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" id="reg-phone" class="form-input" placeholder="03XX-XXXXXXX">
          </div>
          <div class="form-group">
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="password" id="reg-password" class="form-input" placeholder="Min 6 characters" required minlength="6">
          </div>
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">Create Account</button>
        </form>
        <p style="text-align:center;margin-top:14px;font-size:0.88rem;color:var(--border);">Already registered? <a href="javascript:void(0)" onclick="showAuthTab('login')" style="color:var(--mahogany);">Sign in</a></p>
      </div>

    </div>
  </div>
</div>

<!-- ORDER DETAIL MODAL -->
<div class="modal-overlay" id="order-detail-modal">
  <div class="modal" style="max-width:540px;">
    <div class="modal-header">
      <h3>✦ Order Details</h3>
      <button class="modal-close" onclick="closeModal('order-detail-modal')">✕</button>
    </div>
    <div class="modal-body" id="order-detail-body"></div>
  </div>
</div>

<!-- PAY EXISTING MODAL -->
<div class="modal-overlay" id="pay-existing-modal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-header">
      <h3>✦ Complete Payment</h3>
      <button class="modal-close" onclick="closeModal('pay-existing-modal')">✕</button>
    </div>
    <div class="modal-body" id="pay-existing-body"></div>
  </div>
</div>

<!-- EDIT PROFILE MODAL -->
<div class="modal-overlay" id="edit-profile-modal">
  <div class="modal" style="max-width:440px;">
    <div class="modal-header">
      <h3>✏️ Edit Profile</h3>
      <button class="modal-close" onclick="closeModal('edit-profile-modal')">✕</button>
    </div>
    <div class="modal-body">
      <form onsubmit="saveProfile(event)">
        <div class="form-group">
          <label class="form-label">Full Name <span class="required">*</span></label>
          <input type="text" id="edit-name" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" id="edit-phone" class="form-input" placeholder="03XX-XXXXXXX">
        </div>
        <div class="modal-footer" style="padding:0;border:none;margin-top:12px;">
          <button type="button" class="btn btn-outline" onclick="closeModal('edit-profile-modal')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>