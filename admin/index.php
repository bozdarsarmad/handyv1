<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Handy Admin Panel</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Crimson+Pro:wght@400;500;600&family=IM+Fell+English+SC&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>">
</head>
<body>

<div id="admin-toast"></div>

<!-- Auth Guard -->
<div id="admin-auth-guard" style="display:none;position:fixed;inset:0;background:#1a1108;z-index:9999;display:flex;align-items:center;justify-content:center;">
  <div style="background:#241a0c;border:2px solid #5a3d15;border-radius:4px;padding:40px;max-width:400px;width:100%;text-align:center;">
    <div style="font-family:'IM Fell English SC',serif;font-size:1.6rem;color:#e8b84b;letter-spacing:3px;margin-bottom:6px;">✦ HANDY</div>
    <div style="font-size:0.75rem;color:#c9921a;letter-spacing:2px;text-transform:uppercase;margin-bottom:28px;">Admin Panel</div>
    <form id="admin-login-form" onsubmit="adminLogin(event)">
      <div style="margin-bottom:14px;text-align:left;">
        <label style="display:block;font-family:'IM Fell English SC',serif;font-size:0.7rem;color:#c9921a;letter-spacing:1px;margin-bottom:5px;text-transform:uppercase;">Email</label>
        <input type="email" id="adm-email" style="width:100%;padding:9px 12px;background:#1a1108;border:1px solid #5a3d15;border-radius:3px;color:#d4c0a0;font-size:0.9rem;" required placeholder="admin@handy.pk">
      </div>
      <div style="margin-bottom:20px;text-align:left;">
        <label style="display:block;font-family:'IM Fell English SC',serif;font-size:0.7rem;color:#c9921a;letter-spacing:1px;margin-bottom:5px;text-transform:uppercase;">Password</label>
        <input type="password" id="adm-password" style="width:100%;padding:9px 12px;background:#1a1108;border:1px solid #5a3d15;border-radius:3px;color:#d4c0a0;font-size:0.9rem;" required placeholder="••••••••">
      </div>
      <button type="submit" style="width:100%;padding:11px;background:#c9921a;color:#1a1108;border:none;border-radius:3px;font-family:'IM Fell English SC',serif;font-size:0.85rem;letter-spacing:2px;text-transform:uppercase;cursor:pointer;">Sign In ✦</button>
    </form>
    <div id="admin-login-error" style="margin-top:12px;color:#e07070;font-size:0.85rem;display:none;"></div>
  </div>
</div>

<div id="admin-app" style="display:none;">
<div class="admin-layout">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <h1>✦ HANDY</h1>
      <p>Admin Panel</p>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-group">
        <div class="nav-group-title">Overview</div>
        <div class="nav-item active" data-section="dashboard" data-title="Dashboard" onclick="showSection('dashboard')">
          <span class="nav-icon">📊</span> Dashboard
        </div>
      </div>

      <div class="nav-group">
        <div class="nav-group-title">Homepage</div>
        <div class="nav-item" data-section="sliders" data-title="Image Sliders" onclick="showSection('sliders')">
          <span class="nav-icon">🖼️</span> Sliders
        </div>
        <div class="nav-item" data-section="stats" data-title="Home Stats" onclick="showSection('stats')">
          <span class="nav-icon">⭐</span> Stats / Badges
        </div>
      </div>

      <div class="nav-group">
        <div class="nav-group-title">Services</div>
        <div class="nav-item" data-section="categories" data-title="Categories" onclick="showSection('categories')">
          <span class="nav-icon">📁</span> Categories
        </div>
        <div class="nav-item" data-section="cards" data-title="Service Cards" onclick="showSection('cards')">
          <span class="nav-icon">🃏</span> Service Cards
        </div>
      </div>

      <div class="nav-group">
        <div class="nav-group-title">Orders</div>
        <div class="nav-item" data-section="fields" data-title="Order Form Fields" onclick="showSection('fields')">
          <span class="nav-icon">📝</span> Form Fields
        </div>
        <div class="nav-item" data-section="shipping" data-title="Shipping Rates" onclick="showSection('shipping')">
          <span class="nav-icon">🚚</span> Shipping Rates
        </div>
        <div class="nav-item" data-section="orders" data-title="Manage Orders" onclick="showSection('orders')">
          <span class="nav-icon">📦</span> Manage Orders
        </div>
      </div>

      <div class="nav-group">
        <div class="nav-group-title">Payments</div>
        <div class="nav-item" data-section="payments_methods" data-title="Payment Methods" onclick="showSection('payments_methods')">
          <span class="nav-icon">💳</span> Payment Methods
        </div>
      </div>

      <div class="nav-group">
        <div class="nav-group-title">Users</div>
        <div class="nav-item" data-section="users" data-title="Manage Users" onclick="showSection('users')">
          <span class="nav-icon">👥</span> Manage Users
        </div>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-user-avatar" id="admin-avatar">A</div>
        <div class="sidebar-user-info">
          <div class="name" id="admin-name">Admin</div>
          <div class="role">Administrator</div>
        </div>
      </div>
      <button class="btn btn-outline btn-sm" onclick="adminLogout()" style="width:100%;">🚪 Logout</button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="main">

    <!-- Header -->
    <div class="main-header">
      <div style="display:flex;align-items:center;gap:12px;">
        <button onclick="document.getElementById('sidebar').classList.toggle('open')" style="display:none;background:none;border:none;color:var(--gold);font-size:1.3rem;cursor:pointer;" id="mob-toggle">☰</button>
        <h2 id="section-title">Dashboard</h2>
      </div>
      <div class="main-header-actions">
        <a href="../index.php" target="_blank" class="btn btn-outline btn-sm">🌐 View Site</a>
      </div>
    </div>

    <!-- =================== DASHBOARD =================== -->
    <div class="admin-section main-content" id="section-dashboard">
      <div id="dash-content"><div class="spinner"></div></div>

      <!-- Recent orders table -->
      <div class="table-wrap">
        <div class="table-header">
          <h3>Recent Activity</h3>
          <button class="btn btn-outline btn-sm" onclick="showSection('orders')">View All Orders</button>
        </div>
        <p style="padding:20px;color:var(--text);opacity:0.6;font-style:italic;font-size:0.88rem;">Go to Manage Orders for full order management.</p>
      </div>
    </div>

    <!-- =================== SLIDERS =================== -->
    <div class="admin-section main-content" id="section-sliders" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Image Sliders</h3>
          <button class="btn btn-gold btn-sm" onclick="editSlider('{}')">+ Add Slider</button>
        </div>
        <table>
          <thead><tr><th>Image</th><th>Title</th><th>Subtitle</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="sliders-tbody"><tr><td colspan="5" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== STATS =================== -->
    <div class="admin-section main-content" id="section-stats" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Home Stats / Badges</h3>
          <button class="btn btn-gold btn-sm" onclick="editStat('{}')">+ Add Stat</button>
        </div>
        <table>
          <thead><tr><th>Icon</th><th>Label</th><th>Value</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="stats-tbody"><tr><td colspan="5" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== CATEGORIES =================== -->
    <div class="admin-section main-content" id="section-categories" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Service Categories</h3>
          <button class="btn btn-gold btn-sm" onclick="editCat('{}')">+ Add Category</button>
        </div>
        <table>
          <thead><tr><th>Name</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="cats-tbody"><tr><td colspan="4" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== CARDS =================== -->
    <div class="admin-section main-content" id="section-cards" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Service Cards</h3>
          <button class="btn btn-gold btn-sm" onclick="editCard('{}')">+ Add Card</button>
        </div>
        <table>
          <thead><tr><th>Image</th><th>Title / Category</th><th>Prices</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="cards-tbody"><tr><td colspan="5" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== ORDER FIELDS =================== -->
    <div class="admin-section main-content" id="section-fields" style="display:none;">

      <div class="table-wrap" style="margin-bottom:24px;">
        <div class="table-header">
          <h3>🎓 Academic Form Fields</h3>
          <button class="btn btn-gold btn-sm" onclick="editField(JSON.stringify({section:'academic'}))">+ Add Academic Field</button>
        </div>
        <table>
          <thead><tr><th>Label</th><th>Field Name</th><th>Type</th><th>Required</th><th>Actions</th></tr></thead>
          <tbody id="fields-academic-tbody"><tr><td colspan="5" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>

      <div class="table-wrap">
        <div class="table-header">
          <h3>🚚 Shipping Form Fields</h3>
          <button class="btn btn-gold btn-sm" onclick="editField(JSON.stringify({section:'shipping'}))">+ Add Shipping Field</button>
        </div>
        <table>
          <thead><tr><th>Label</th><th>Field Name</th><th>Type</th><th>Required</th><th>Actions</th></tr></thead>
          <tbody id="fields-shipping-tbody"><tr><td colspan="5" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== SHIPPING RATES =================== -->
    <div class="admin-section main-content" id="section-shipping" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Shipping Rates by City</h3>
          <button class="btn btn-gold btn-sm" onclick="editShipping('{}')">+ Add City</button>
        </div>
        <table>
          <thead><tr><th>City</th><th>Rate (Rs.)</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="shipping-tbody"><tr><td colspan="4" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== ORDERS =================== -->
    <div class="admin-section main-content" id="section-orders" style="display:none;">
      <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <button class="btn btn-outline btn-sm" onclick="loadOrders()">All</button>
        <button class="btn btn-outline btn-sm" onclick="filterOrders('pending')">Pending</button>
        <button class="btn btn-outline btn-sm" onclick="filterOrders('processing')">Processing</button>
        <button class="btn btn-outline btn-sm" onclick="filterOrders('completed')">Completed</button>
      </div>
      <div class="table-wrap">
        <div class="table-header"><h3>All Orders</h3></div>
        <table>
          <thead><tr><th>Order #</th><th>Customer</th><th>Certificates</th><th>Total</th><th>Status</th><th>Payment</th><th>Action</th></tr></thead>
          <tbody id="orders-tbody"><tr><td colspan="7" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== PAYMENT METHODS =================== -->
    <div class="admin-section main-content" id="section-payments_methods" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>Payment Methods</h3>
          <button class="btn btn-gold btn-sm" onclick="editPM('{}')">+ Add Method</button>
        </div>
        <table>
          <thead><tr><th>Icon</th><th>Name / Type</th><th>Account</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="pm-tbody"><tr><td colspan="5" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- =================== USERS =================== -->
    <div class="admin-section main-content" id="section-users" style="display:none;">
      <div class="table-wrap">
        <div class="table-header">
          <h3>All Users</h3>
        </div>
        <table>
          <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
          <tbody id="users-tbody"><tr><td colspan="6" class="table-empty">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

  </div><!-- /main -->
</div><!-- /admin-layout -->
</div><!-- /admin-app -->

<!-- ============================================================
     MODALS
     ============================================================ -->

<!-- SLIDER MODAL -->
<div class="modal-overlay" id="slider-modal">
  <div class="modal">
    <div class="modal-header"><h3>🖼️ Slider</h3><button class="modal-close" onclick="closeModal('slider-modal')">✕</button></div>
    <div class="modal-body">
      <form id="slider-form" onsubmit="saveSlider(event)">
        <input type="hidden" id="slider-id" name="id">
        <div class="form-group">
          <label class="form-label">Slider Image</label>
          <div class="file-drop" onclick="document.getElementById('slider-img-input').click()">
            <input type="file" name="image" id="slider-img-input" accept="image/*" onchange="previewImg(this,'slider-preview')">
            <div>📷 Click to upload image</div>
            <div style="font-size:0.78rem;opacity:0.6;margin-top:4px;">Recommended: 1200×480px</div>
          </div>
          <div id="slider-preview" style="margin-top:8px;"></div>
        </div>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input type="text" name="title" id="slider-title" class="form-input" placeholder="Slide title text">
        </div>
        <div class="form-group">
          <label class="form-label">Subtitle</label>
          <input type="text" name="subtitle" id="slider-subtitle" class="form-input" placeholder="Subtitle / tagline">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" id="slider-order" class="form-input" value="0">
          </div>
          <div class="form-group">
            <label class="form-label">Active</label>
            <button type="button" class="toggle on" id="slider-active" onclick="toggleSwitch('slider-active')"></button>
          </div>
        </div>
        <div class="modal-footer" style="padding:0;border:none;margin-top:4px;justify-content:flex-end;display:flex;gap:10px;">
          <button type="button" class="btn btn-outline" onclick="closeModal('slider-modal')">Cancel</button>
          <button type="submit" class="btn btn-gold">Save Slider</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- STAT MODAL -->
<div class="modal-overlay" id="stat-modal">
  <div class="modal">
    <div class="modal-header"><h3>⭐ Stat Badge</h3><button class="modal-close" onclick="closeModal('stat-modal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="stat-id">
      <div class="form-group">
        <label class="form-label">Icon (Emoji)</label>
        <input type="text" id="stat-icon" class="form-input" placeholder="⭐" value="⭐">
      </div>
      <div class="form-group">
        <label class="form-label">Label</label>
        <input type="text" id="stat-label" class="form-input" placeholder="e.g. Fast Delivery All Over Sind">
      </div>
      <div class="form-group">
        <label class="form-label">Value</label>
        <input type="text" id="stat-value" class="form-input" placeholder="e.g. 24-48 Hours or 15,000+">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" id="stat-order" class="form-input" value="0">
        </div>
        <div class="form-group">
          <label class="form-label">Active</label>
          <button type="button" class="toggle on" id="stat-active" onclick="toggleSwitch('stat-active')"></button>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeModal('stat-modal')">Cancel</button>
        <button class="btn btn-gold" onclick="saveStat()">Save Stat</button>
      </div>
    </div>
  </div>
</div>

<!-- CATEGORY MODAL -->
<div class="modal-overlay" id="cat-modal">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header"><h3>📁 Category</h3><button class="modal-close" onclick="closeModal('cat-modal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="cat-id">
      <div class="form-group">
        <label class="form-label">Category Name <span style="color:#c9921a;">*</span></label>
        <input type="text" id="cat-name" class="form-input" placeholder="e.g. Matric, Intermediate">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" id="cat-order" class="form-input" value="0">
        </div>
        <div class="form-group">
          <label class="form-label">Active</label>
          <button type="button" class="toggle on" id="cat-active" onclick="toggleSwitch('cat-active')"></button>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeModal('cat-modal')">Cancel</button>
        <button class="btn btn-gold" onclick="saveCat()">Save Category</button>
      </div>
    </div>
  </div>
</div>

<!-- CARD MODAL -->
<div class="modal-overlay" id="card-modal">
  <div class="modal" style="max-width:600px;">
    <div class="modal-header"><h3>🃏 Service Card</h3><button class="modal-close" onclick="closeModal('card-modal')">✕</button></div>
    <div class="modal-body">
      <form id="card-form" onsubmit="saveCard(event)">
        <input type="hidden" id="card-id" name="id">
        <input type="hidden" id="prices-json" name="prices">
        <div class="form-row">
          <div class="form-group" style="grid-column:1/-1;">
            <label class="form-label">Card Image</label>
            <div class="file-drop" onclick="document.getElementById('card-img-input').click()">
              <input type="file" name="image" id="card-img-input" accept="image/*" onchange="previewImg(this,'card-img-preview')">
              📷 Click to upload card image
            </div>
            <div id="card-img-preview" style="margin-top:8px;"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Category <span style="color:#c9921a;">*</span></label>
            <select id="card-category" name="category_id" class="form-select">
              <option value="">— Select —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" id="card-order" class="form-input" value="0">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Card Title <span style="color:#c9921a;">*</span></label>
          <input type="text" name="title" id="card-title" class="form-input" placeholder="e.g. Matric Certificates" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" id="card-desc" class="form-input form-textarea" placeholder="Brief description of this service"></textarea>
        </div>

        <!-- PRICES -->
        <div class="form-group">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <label class="form-label" style="margin:0;">Pricing (Certificate → Price)</label>
            <button type="button" class="btn btn-outline btn-sm" onclick="addPriceRow()">+ Add</button>
          </div>
          <div id="price-rows"></div>
          <div style="font-size:0.78rem;opacity:0.6;margin-top:6px;">e.g. "Pass Certificate" → 500</div>
        </div>

        <div class="form-group">
          <label class="form-label">Active</label>
          <button type="button" class="toggle on" id="card-active" onclick="toggleSwitch('card-active')"></button>
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
          <button type="button" class="btn btn-outline" onclick="closeModal('card-modal')">Cancel</button>
          <button type="submit" class="btn btn-gold">Save Card</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- FIELD MODAL -->
<div class="modal-overlay" id="field-modal">
  <div class="modal" style="max-width:560px;">
    <div class="modal-header"><h3>📝 Form Field</h3><button class="modal-close" onclick="closeModal('field-modal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="field-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Section <span style="color:#c9921a;">*</span></label>
          <select id="field-section" class="form-select">
            <option value="academic">Academic</option>
            <option value="shipping">Shipping</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Field Type <span style="color:#c9921a;">*</span></label>
          <select id="field-type" class="form-select">
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="email">Email</option>
            <option value="tel">Phone</option>
            <option value="select">Dropdown (Select)</option>
            <option value="radio">Radio Buttons</option>
            <option value="checkbox">Checkboxes</option>
            <option value="textarea">Text Area</option>
            <option value="date">Date Picker</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Label (Display) <span style="color:#c9921a;">*</span></label>
          <input type="text" id="field-label" class="form-input" placeholder="e.g. Roll Number">
        </div>
        <div class="form-group">
          <label class="form-label">Field Name (Code) <span style="color:#c9921a;">*</span></label>
          <input type="text" id="field-name" class="form-input" placeholder="e.g. roll_number">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Placeholder</label>
        <input type="text" id="field-placeholder" class="form-input" placeholder="Hint text inside input">
      </div>
      <div class="form-group">
        <label class="form-label">Options (for select/radio/checkbox) — one per line</label>
        <textarea id="field-options" class="form-input form-textarea" placeholder="Option 1&#10;Option 2&#10;Option 3" style="min-height:80px;"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" id="field-order" class="form-input" value="0">
        </div>
        <div class="form-group">
          <label class="form-label">Required?</label>
          <button type="button" class="toggle" id="field-required" onclick="toggleSwitch('field-required')"></button>
        </div>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeModal('field-modal')">Cancel</button>
        <button class="btn btn-gold" onclick="saveField()">Save Field</button>
      </div>
    </div>
  </div>
</div>

<!-- PAYMENT METHOD MODAL -->
<div class="modal-overlay" id="pm-modal">
  <div class="modal">
    <div class="modal-header"><h3>💳 Payment Method</h3><button class="modal-close" onclick="closeModal('pm-modal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="pm-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Name <span style="color:#c9921a;">*</span></label>
          <input type="text" id="pm-name" class="form-input" placeholder="e.g. EasyPaisa">
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select id="pm-type" class="form-select">
            <option value="easypaisa">EasyPaisa</option>
            <option value="jazzcash">JazzCash</option>
            <option value="bank">Bank Transfer</option>
            <option value="other">Other</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Account Title</label>
          <input type="text" id="pm-title" class="form-input" placeholder="Account holder name">
        </div>
        <div class="form-group">
          <label class="form-label">Account Number / IBAN</label>
          <input type="text" id="pm-account" class="form-input" placeholder="03XX-XXXXXXX or IBAN">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Payment Instructions</label>
        <textarea id="pm-instructions" class="form-input form-textarea" placeholder="e.g. Send payment and upload screenshot as proof"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Logo / Icon (Emoji)</label>
          <input type="text" id="pm-icon" class="form-input" value="💳" placeholder="💳">
        </div>
        <div class="form-group">
          <label class="form-label">Sort Order</label>
          <input type="number" id="pm-order" class="form-input" value="0">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Active</label>
        <button type="button" class="toggle on" id="pm-active" onclick="toggleSwitch('pm-active')"></button>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeModal('pm-modal')">Cancel</button>
        <button class="btn btn-gold" onclick="savePM()">Save Method</button>
      </div>
    </div>
  </div>
</div>

<!-- SHIPPING RATE MODAL -->
<div class="modal-overlay" id="ship-modal">
  <div class="modal" style="max-width:420px;">
    <div class="modal-header"><h3>🚚 Shipping Rate</h3><button class="modal-close" onclick="closeModal('ship-modal')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="ship-id">
      <div class="form-group">
        <label class="form-label">City Name <span style="color:#c9921a;">*</span></label>
        <input type="text" id="ship-city" class="form-input" placeholder="e.g. Karachi">
      </div>
      <div class="form-group">
        <label class="form-label">Delivery Rate (Rs.) <span style="color:#c9921a;">*</span></label>
        <input type="number" id="ship-rate" class="form-input" placeholder="e.g. 150" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">Active</label>
        <button type="button" class="toggle on" id="ship-active" onclick="toggleSwitch('ship-active')"></button>
      </div>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:8px;">
        <button class="btn btn-outline" onclick="closeModal('ship-modal')">Cancel</button>
        <button class="btn btn-gold" onclick="saveShipping()">Save Rate</button>
      </div>
    </div>
  </div>
</div>

<!-- ORDER DETAIL MODAL -->
<div class="modal-overlay" id="order-detail-modal">
  <div class="modal" style="max-width:580px;">
    <div class="modal-header"><h3>📦 Order Detail</h3><button class="modal-close" onclick="closeModal('order-detail-modal')">✕</button></div>
    <div class="modal-body" id="order-detail-wrap"></div>
  </div>
</div>

<script>
// ---- Admin Auth ----
async function adminLogin(e) {
  e.preventDefault();
  const email = document.getElementById('adm-email').value;
  const pass  = document.getElementById('adm-password').value;
  const errEl = document.getElementById('admin-login-error');
  errEl.style.display = 'none';

  const res = await fetch('../api/auth.php?action=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password: pass }),
  }).then(r => r.json());

  if (res.success && res.user.role === 'admin') {
    localStorage.setItem('handy_token', res.token);
    localStorage.setItem('handy_user', JSON.stringify(res.user));
    document.getElementById('admin-auth-guard').style.display = 'none';
    document.getElementById('admin-app').style.display = 'block';
    document.getElementById('admin-name').textContent = res.user.name;
    document.getElementById('admin-avatar').textContent = res.user.name.charAt(0).toUpperCase();
    loadDashboard();
  } else {
    errEl.textContent = res.error || 'Access denied. Admin only.';
    errEl.style.display = 'block';
  }
}

function adminLogout() {
  localStorage.removeItem('handy_token');
  localStorage.removeItem('handy_user');
  location.reload();
}

// ---- Image preview helper ----
function previewImg(input, previewId) {
  const prev = document.getElementById(previewId);
  if (!prev || !input.files?.[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  prev.innerHTML = `<img src="${url}" style="max-height:80px;border-radius:3px;">`;
}

// ---- Filter orders ----
async function filterOrders(status) {
  const rows = await aApi(`${ADMIN_API}?section=orders&action=list&status=${status}`);
  const tbody = document.getElementById('orders-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="7" class="table-empty">No ${status} orders</td></tr>` :
    rows.map(r => `
      <tr>
        <td><strong>${r.order_number}</strong></td>
        <td>${r.user_name||'—'}</td>
        <td style="font-size:0.82rem;">${(r.selected_certificates||[]).join(', ')}</td>
        <td>Rs. ${Number(r.total).toLocaleString()}</td>
        <td><span class="badge badge-${r.order_status==='completed'?'success':r.order_status==='cancelled'?'danger':'warning'}">${r.order_status}</span></td>
        <td><span class="badge badge-${r.payment_status==='paid'?'success':r.payment_status==='unpaid'?'danger':'warning'}">${r.payment_status}</span></td>
        <td><button class="btn btn-outline btn-sm" onclick="viewOrder(${JSON.stringify(JSON.stringify(r))})">View</button></td>
      </tr>`).join('');
}

// ---- Check auth on load ----
window.addEventListener('DOMContentLoaded', () => {
  const token = localStorage.getItem('handy_token');
  const user  = localStorage.getItem('handy_user');
  if (token && user) {
    const u = JSON.parse(user);
    if (u.role === 'admin') {
      document.getElementById('admin-auth-guard').style.display = 'none';
      document.getElementById('admin-app').style.display = 'block';
      document.getElementById('admin-name').textContent = u.name;
      document.getElementById('admin-avatar').textContent = u.name.charAt(0).toUpperCase();
      document.getElementById('mob-toggle').style.display = 'block';
      setTimeout(() => { if (typeof loadDashboard === 'function') loadDashboard(); }, 100);
      return;
    }
  }
  document.getElementById('admin-auth-guard').style.display = 'flex';
  document.getElementById('admin-app').style.display = 'none';
});
</script>

<script src="assets/js/admin.js"></script>
</body>
</html>
