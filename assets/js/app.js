/* ============================================================
   HANDY CERTIFICATE SERVICES — MAIN SPA (app.js)
   Vanilla JS · Hash Router · Full User Side
   ============================================================ */

// ---- CONFIG ----
const BASE_API = "api";
const ADMIN_API = "admin/api/index.php";

// ---- STATE ----
const State = {
  user: null,
  token: null,
  currentOrder: null, // card being ordered
  pendingOrder: null, // filled order data before payment
};

// ---- STORAGE HELPERS ----
const Storage = {
  set(k, v) {
    localStorage.setItem("handy_" + k, JSON.stringify(v));
  },
  get(k) {
    try {
      return JSON.parse(localStorage.getItem("handy_" + k));
    } catch {
      return null;
    }
  },
  del(k) {
    localStorage.removeItem("handy_" + k);
  },
};

// ---- INIT AUTH ----
function initAuth() {
  State.token = Storage.get("token");
  State.user = Storage.get("user");
}

function login(user, token) {
  State.user = user;
  State.token = token;
  Storage.set("user", user);
  Storage.set("token", token);
  renderNavbar();
}

function logout() {
  State.user = null;
  State.token = null;
  Storage.del("user");
  Storage.del("token");
  renderNavbar();
  Router.go("home");
  toast("Logged out successfully", "info");
}

// ---- API HELPER ----
async function api(endpoint, options = {}) {
  const headers = { "Content-Type": "application/json" };
  if (State.token) headers["Authorization"] = "Bearer " + State.token;
  const res = await fetch(endpoint, { headers, ...options });
  const data = await res.json();
  return data;
}

async function apiPost(endpoint, body) {
  return api(endpoint, {
    method: "POST",
    body: JSON.stringify(body),
  });
}

async function apiPostForm(endpoint, formData) {
  const headers = {};
  if (State.token) headers["Authorization"] = "Bearer " + State.token;
  const res = await fetch(endpoint, {
    method: "POST",
    headers,
    body: formData,
  });
  return res.json();
}

// ---- TOAST ----
function toast(msg, type = "info", duration = 3500) {
  const container = document.getElementById("toast-container");
  const icons = { success: "✅", error: "❌", info: "ℹ️" };
  const el = document.createElement("div");
  el.className = `toast toast-${type}`;
  el.innerHTML = `<span class="toast-icon">${icons[type] || "ℹ️"}</span><span>${msg}</span>`;
  container.appendChild(el);
  setTimeout(() => {
    el.style.animation = "toastIn 0.3s ease reverse";
    setTimeout(() => el.remove(), 300);
  }, duration);
}

// ---- ROUTER ----
const Router = {
  routes: {},
  register(hash, fn) {
    this.routes[hash] = fn;
  },
  go(hash, data) {
    if (data) Storage.set("routeData", data);
    window.location.hash = "#" + hash;
  },
  handle() {
    const hash = window.location.hash.replace("#", "") || "home";
    // Deactivate all pages
    document
      .querySelectorAll(".page")
      .forEach((p) => p.classList.remove("active"));
    const fn = this.routes[hash];
    if (fn) fn();
    else if (this.routes["home"]) this.routes["home"]();
    // Update nav links
    document.querySelectorAll(".nav-link").forEach((a) => {
      a.classList.toggle("active", a.dataset.route === hash);
    });
  },
};
window.addEventListener("hashchange", () => Router.handle());

// ---- PAGES ----
function showPage(id) {
  document
    .querySelectorAll(".page")
    .forEach((p) => p.classList.remove("active"));
  const p = document.getElementById("page-" + id);
  if (p) {
    p.classList.add("active");
    window.scrollTo(0, 0);
  }
}

// ============================================================
// NAVBAR
// ============================================================
function renderNavbar() {
  const topRight = document.getElementById("navbar-user-area");
  if (!topRight) return;

  if (State.user) {
    const pic = State.user.profile_pic
      ? `<img src="${State.user.profile_pic}" alt="profile">`
      : `<span>${State.user.name.charAt(0).toUpperCase()}</span>`;

    topRight.innerHTML = `
      <div class="profile-badge" onclick="toggleProfileDropdown()">
        <div class="profile-avatar">${pic}</div>
        <span class="profile-name">${State.user.name.split(" ")[0]}</span>
        <span style="color:var(--gold);font-size:0.7rem;">▼</span>
      </div>
      <div class="profile-dropdown" id="profile-dropdown">
        <a href="#profile" onclick="closeDropdown()">👤 My Profile</a>
        <a href="#orders" onclick="closeDropdown()">📋 My Orders</a>
        ${State.user.role === "admin" ? `<a href="admin/index.php" target="_blank">⚙️ Admin Panel</a>` : ""}
        <button onclick="logout()">🚪 Logout</button>
      </div>`;
  } else {
    topRight.innerHTML = `<button class="btn-login" onclick="openLoginModal()">Login / Register</button>`;
  }
}

function toggleProfileDropdown() {
  document.getElementById("profile-dropdown")?.classList.toggle("open");
}
function closeDropdown() {
  document.getElementById("profile-dropdown")?.classList.remove("open");
}
document.addEventListener("click", (e) => {
  if (!e.target.closest(".navbar-user")) closeDropdown();
});

function toggleMobileMenu() {
  document.getElementById("mobile-menu")?.classList.toggle("open");
}

// ============================================================
// AUTH MODALS
// ============================================================
function openLoginModal() {
  document.getElementById("auth-modal").classList.add("open");
  showAuthTab("login");
}
function closeAuthModal() {
  document.getElementById("auth-modal").classList.remove("open");
}

function showAuthTab(tab) {
  document.getElementById("auth-login").style.display =
    tab === "login" ? "block" : "none";
  document.getElementById("auth-register").style.display =
    tab === "register" ? "block" : "none";
  document
    .querySelectorAll(".auth-tab")
    .forEach((t) => t.classList.toggle("active", t.dataset.tab === tab));
}

async function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById("login-email").value;
  const password = document.getElementById("login-password").value;
  const btn = e.target.querySelector("button[type=submit]");
  btn.disabled = true;
  btn.textContent = "Signing in…";

  const res = await apiPost(`${BASE_API}/auth.php?action=login`, {
    email,
    password,
  });
  btn.disabled = false;
  btn.textContent = "Sign In";

  if (res.success) {
    login(res.user, res.token);
    closeAuthModal();
    toast("Welcome back, " + res.user.name + "!", "success");
    // If pending order redirect
    if (Storage.get("pendingOrderRoute")) {
      const r = Storage.get("pendingOrderRoute");
      Storage.del("pendingOrderRoute");
      Router.go(r);
    }
  } else {
    toast(res.error || "Login failed", "error");
  }
}

async function handleRegister(e) {
  e.preventDefault();
  const name = document.getElementById("reg-name").value;
  const email = document.getElementById("reg-email").value;
  const phone = document.getElementById("reg-phone").value;
  const password = document.getElementById("reg-password").value;
  const btn = e.target.querySelector("button[type=submit]");
  btn.disabled = true;
  btn.textContent = "Creating account…";

  const res = await apiPost(`${BASE_API}/auth.php?action=register`, {
    name,
    email,
    phone,
    password,
  });
  btn.disabled = false;
  btn.textContent = "Create Account";

  if (res.success) {
    login(res.user, res.token);
    closeAuthModal();
    toast("Account created! Welcome, " + res.user.name + "!", "success");
  } else {
    toast(res.error || "Registration failed", "error");
  }
}

// ============================================================
// HOME PAGE
// ============================================================
async function loadHomePage() {
  showPage("home");
  loadSlider();
  loadStats();
  loadHomeCards();
}

// Slider
async function loadSlider() {
  const wrap = document.getElementById("slider-container");
  if (!wrap) return;
  const slides = await api(`${BASE_API}/public.php?ep=sliders`);
  if (!slides || !slides.length) {
    wrap.innerHTML = `<div class="slider-container" style="background:var(--parchment);display:flex;align-items:center;justify-content:center;height:300px;"><div style="text-align:center;"><div style="font-size:3rem;margin-bottom:12px;">📜</div><h2 style="font-family:var(--font-head);color:var(--mahogany)">Welcome to Handy</h2><p style="color:var(--rust)">Your trusted certificate services</p></div></div>`;
    return;
  }

  const fallbackImg = `data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='1200' height='480'><rect fill='%232b1208' width='1200' height='480'/><text x='50%' y='50%' fill='%23c9921a' font-size='48' text-anchor='middle' dominant-baseline='middle' font-family='Georgia'>📜</text></svg>`;

  wrap.innerHTML = `
    <div class="slider-container" id="slider">
      <div class="slider-track" id="slider-track">
        ${slides
          .map(
            (s) => `
          <div class="slide">
            <img src="${s.image}" alt="${s.title || ""}" onerror="this.src='${fallbackImg}'">
            <div class="slide-overlay">
              <div class="slide-content">
                ${s.title ? `<h2 class="slide-title">${s.title}</h2>` : ""}
                ${s.subtitle ? `<p class="slide-subtitle">${s.subtitle}</p>` : ""}
                <a class="slide-btn" href="#cards">Browse Services ✦</a>
              </div>
            </div>
          </div>`,
          )
          .join("")}
      </div>
      <div class="slider-arrows">
        <button class="slider-arrow" onclick="sliderPrev()">❮</button>
        <button class="slider-arrow" onclick="sliderNext()">❯</button>
      </div>
      <div class="slider-dots" id="slider-dots">
        ${slides.map((_, i) => `<div class="slider-dot ${i === 0 ? "active" : ""}" onclick="sliderGo(${i})"></div>`).join("")}
      </div>
    </div>`;

  initSliderAuto(slides.length);
}

let _sliderIdx = 0,
  _sliderTimer = null,
  _sliderTotal = 0;
function initSliderAuto(total) {
  _sliderTotal = total;
  _sliderIdx = 0;
  if (_sliderTimer) clearInterval(_sliderTimer);
  _sliderTimer = setInterval(sliderNext, 5000);
}
function sliderNext() {
  sliderGo((_sliderIdx + 1) % _sliderTotal);
}
function sliderPrev() {
  sliderGo((_sliderIdx - 1 + _sliderTotal) % _sliderTotal);
}
function sliderGo(i) {
  _sliderIdx = i;
  const track = document.getElementById("slider-track");
  const dots = document.querySelectorAll(".slider-dot");
  if (track) track.style.transform = `translateX(-${i * 100}%)`;
  dots.forEach((d, j) => d.classList.toggle("active", j === i));
}

// Stats
async function loadStats() {
  const wrap = document.getElementById("stats-grid");
  if (!wrap) return;
  const stats = await api(`${BASE_API}/public.php?ep=stats`);
  if (!stats || !stats.length) {
    wrap.closest(".stats-strip").style.display = "none";
    return;
  }
  wrap.innerHTML = stats
    .map(
      (s) => `
    <div class="stat-card">
      <div class="stat-icon">${s.icon}</div>
      <div class="stat-value">${s.value}</div>
      <div class="stat-label">${s.label}</div>
    </div>`,
    )
    .join("");
}

// Cards / Categories
let _allCards = [],
  _categories = [];
async function loadHomeCards() {
  const tabsWrap = document.getElementById("category-tabs");
  const cardsWrap = document.getElementById("cards-grid");
  if (!cardsWrap) return;

  cardsWrap.innerHTML = '<div class="spinner"></div>';
  tabsWrap.innerHTML = "";

  const [cats, cards] = await Promise.all([
    api(`${BASE_API}/public.php?ep=categories`),
    api(`${BASE_API}/public.php?ep=cards`),
  ]);

  _categories = cats || [];
  _allCards = cards || [];

  // Tabs
  tabsWrap.innerHTML = `
    <button class="cat-tab active" data-cat="all" onclick="filterCards('all', this)">All Services</button>
    ${_categories.map((c) => `<button class="cat-tab" data-cat="${c.id}" onclick="filterCards('${c.id}', this)">${c.name}</button>`).join("")}`;

  renderCards(_allCards);
}

function filterCards(catId, btn) {
  document
    .querySelectorAll(".cat-tab")
    .forEach((t) => t.classList.remove("active"));
  btn.classList.add("active");
  const filtered =
    catId === "all"
      ? _allCards
      : _allCards.filter((c) => String(c.category_id) === String(catId));
  renderCards(filtered);
}

function escapeHtml(str) {
  if (!str) return "";
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function renderCards(cards) {
  const wrap = document.getElementById("cards-grid");
  if (!cards || !cards.length) {
    wrap.innerHTML = `<div class="empty-state" style="grid-column:1/-1;"><div class="empty-icon">📭</div><h3>No services found</h3><p>Check back later</p></div>`;
    return;
  }
  wrap.innerHTML = cards
    .map((card) => {
      const prices = card.prices || {};
      const priceRows = Object.entries(prices)
        .map(
          ([name, amt]) =>
            `<div class="price-row"><span class="price-name">${escapeHtml(name)}</span><span class="price-amount">Rs. ${amt.toLocaleString()}</span></div>`,
        )
        .join("");
      return `
    <div class="service-card">
      <div class="card-img-wrap">
        <img src="${card.image || ""}" alt="${escapeHtml(card.title)}" onerror="this.parentElement.innerHTML='<div class=\\'img-placeholder\\'>📜</div>'">
        ${card.category_name ? `<span class="card-category-badge">${escapeHtml(card.category_name)}</span>` : ""}
      </div>
      <div class="card-body">
        <h3 class="card-title">${escapeHtml(card.title)}</h3>
        <p class="card-desc">${escapeHtml(card.description) || ""}</p>
        <div class="card-prices">
          <div class="card-prices-label">✦ Pricing</div>
          ${priceRows}
        </div>
        
        <button class="btn-order" data-id="${card.id}">
        Order Now ✦
        </button>
      </div>
    </div>`;
    })
    .join("");
  
  wrap.querySelectorAll('.btn-order').forEach(btn => {
    btn.addEventListener('click', () => startOrder(btn.dataset.id));
  });
}

///
///
///
///


function startOrder(cardId) {
  const card = _allCards.find(c => String(c.id) === String(cardId));
  
  if (!card) {
    toast("Error: Service not found", "error");
    return;
  }

  if (!State.user) {
    Storage.set('pendingOrderRoute', 'order');
    Storage.set('pendingCard', card);
    openLoginModal();
    return;
  }

  State.currentOrder = card;
  Storage.set('pendingCard', card);
  Router.go('order');
}
// ============================================================
// ORDER PAGE
// ============================================================
// function startOrder(cardJson) {
//   const card = JSON.parse(cardJson);
//   if (!State.user) {
//     Storage.set('pendingOrderRoute', 'order');
//     Storage.set('pendingCard', card);
//     openLoginModal();
//     return;
//   }
//   State.currentOrder = card;
//   Storage.set('pendingCard', card);
//   Router.go('order');
// }

async function loadOrderPage() {
  showPage("order");
  const card = State.currentOrder || Storage.get("pendingCard");
  if (!card) {
    Router.go("home");
    return;
  }
  if (!State.user) {
    Storage.set("pendingOrderRoute", "order");
    openLoginModal();
    return;
  }

  State.currentOrder = card;
  const wrap = document.getElementById("order-content");
  wrap.innerHTML = '<div class="spinner"></div>';

  try {
    const [academicFields, shippingFields, shippingRates] = await Promise.all([
      api(`${BASE_API}/public.php?ep=fields&section=academic`),
      api(`${BASE_API}/public.php?ep=fields&section=shipping`),
      api(`${BASE_API}/public.php?ep=shipping`),
    ]);

    if (!academicFields || !shippingFields) {
      throw new Error("Failed to load form fields");
    }

    const prices = card.prices || {};
  const certOptions = Object.entries(prices)
    .map(
      ([name, amt]) => `
    <div class="cert-option" id="cert-${name.replace(/\s/g, "-")}" onclick="toggleCert('${name}', ${amt})">
      <input type="checkbox" value="${name}" data-price="${amt}">
      <div class="cert-option-check" id="check-${name.replace(/\s/g, "-")}"></div>
      <div class="cert-option-name">${name}</div>
      <div class="cert-option-price">Rs. ${amt.toLocaleString()}</div>
    </div>`,
    )
    .join("");

  const fallback = `data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='200'><rect fill='%23ede0c4' width='400' height='200'/><text x='50%' y='50%' font-size='48' text-anchor='middle' dominant-baseline='middle'>📜</text></svg>`;

  wrap.innerHTML = `
    <div class="order-header">
      <h2>Place Your Order</h2>
      <p style="color:var(--border);font-style:italic;font-size:0.9rem;">Fill in all required details carefully</p>
    </div>

    <div class="selected-service-preview">
      <img class="service-thumb" src="${card.image || ""}" alt="${card.title}" onerror="this.src='${fallback}'">
      <div class="service-preview-info">
        <h3>${card.title}</h3>
        <p>${card.description || ""}</p>
        ${card.category_name ? `<span class="badge badge-processing" style="margin-top:6px;display:inline-block;">${card.category_name}</span>` : ""}
      </div>
    </div>

    <div class="cert-selector">
      <h3>✦ Select Certificates</h3>
      <div class="cert-grid" id="cert-grid">${certOptions}</div>
    </div>

    <div class="form-section">
      <div class="form-section-header">
        <span class="section-icon">🎓</span>
        <h3>Academic Information</h3>
      </div>
      <div class="form-section-body">
        ${renderDynamicFields(academicFields, "academic")}
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-header">
        <span class="section-icon">🚚</span>
        <h3>Shipping Information</h3>
      </div>
      <div class="form-section-body">
        ${renderDynamicFields(shippingFields, "shipping", shippingRates)}
      </div>
    </div>

    <div class="order-subtotal" id="order-subtotal">
      <div class="subtotal-row"><span>Selected Certificates</span><span id="subtotal-certs">Rs. 0</span></div>
      <div class="subtotal-row"><span>Shipping Cost</span><span id="subtotal-shipping">—</span></div>
      <div class="subtotal-row"><span class="subtotal-total">Total</span><span class="subtotal-total" id="subtotal-total">Rs. 0</span></div>
    </div>

    <button class="btn btn-primary btn-full" style="font-size:1rem;padding:14px;" onclick="proceedToCheckout()">
      ✦ Proceed to Checkout
    </button>`;

  // Attach shipping city listener
  const citySelect = document.querySelector('[data-field="city"]');
  if (citySelect) {
    citySelect.addEventListener("change", () =>
      updateShippingCost(shippingRates),
    );
  }
  updateSubtotal();
  } catch (err) {
    console.error("Order Page Error:", err);
    wrap.innerHTML = `<p style="color:var(--rust);text-align:center;padding:40px;">Failed to load order form. Please try again.</p>`;
  }
}

function renderDynamicFields(fields, section, shippingRates) {
  if (!fields || !fields.length)
    return '<p style="color:var(--border);font-style:italic;">No fields configured</p>';
  return fields
    .map((f) => {
      const req = f.is_required ? '<span class="required"> *</span>' : "";
      const id = `field_${section}_${f.field_name}`;
      let input = "";

      if (f.field_type === "select") {
        const opts = f.options || (f.field_name === "city" ? [] : []);
        input = `<select class="form-input form-select" id="${id}" data-field="${f.field_name}" ${f.is_required ? "required" : ""}>
        <option value="">— Select —</option>
        ${opts.map((o) => `<option value="${o}">${o}</option>`).join("")}
      </select>`;
      } else if (f.field_type === "radio" && f.options) {
        input = `<div class="radio-group">${f.options
          .map(
            (o) => `
        <label class="radio-option">
          <input type="radio" name="${id}" value="${o}" ${f.is_required ? "required" : ""}>
          ${o}
        </label>`,
          )
          .join("")}</div>`;
      } else if (f.field_type === "checkbox" && f.options) {
        input = `<div class="checkbox-group">${f.options
          .map(
            (o) => `
        <label class="check-option">
          <input type="checkbox" name="${id}[]" value="${o}">
          ${o}
        </label>`,
          )
          .join("")}</div>`;
      } else if (f.field_type === "textarea") {
        input = `<textarea class="form-input form-textarea" id="${id}" data-field="${f.field_name}" placeholder="${f.placeholder || ""}" ${f.is_required ? "required" : ""}></textarea>`;
      } else {
        input = `<input type="${f.field_type}" class="form-input" id="${id}" data-field="${f.field_name}" placeholder="${f.placeholder || ""}" ${f.is_required ? "required" : ""}>`;
      }

      return `<div class="form-group">${'<label class="form-label" for="' + id + '">' + f.label + req + "</label>" + input}</div>`;
    })
    .join("");
}

// Selected certs
const _selectedCerts = {};
function toggleCert(name, price) {
  const key = name.replace(/\s/g, "-");
  const el = document.getElementById("cert-" + key);
  const ch = document.getElementById("check-" + key);
  if (_selectedCerts[name]) {
    delete _selectedCerts[name];
    el.classList.remove("selected");
    ch.textContent = "";
  } else {
    _selectedCerts[name] = price;
    el.classList.add("selected");
    ch.textContent = "✓";
  }
  updateSubtotal();
}

function updateSubtotal() {
  const certsTotal = Object.values(_selectedCerts).reduce(
    (a, b) => a + Number(b),
    0,
  );
  const shipEl = document.getElementById("subtotal-shipping");
  const shipCost = shipEl ? parseInt(shipEl.dataset.cost || 0) : 0;
  const total = certsTotal + shipCost;
  const cs = document.getElementById("subtotal-certs");
  const tt = document.getElementById("subtotal-total");
  if (cs) cs.textContent = "Rs. " + certsTotal.toLocaleString();
  if (tt) tt.textContent = "Rs. " + total.toLocaleString();
}

function updateShippingCost(rates) {
  const cityEl = document.querySelector('[data-field="city"]');
  const shipEl = document.getElementById("subtotal-shipping");
  if (!cityEl || !shipEl) return;
  const city = cityEl.value;
  const rate = (rates || []).find((r) => r.city === city);
  const cost = rate ? rate.rate : 0;
  shipEl.textContent = city ? "Rs. " + Number(cost).toLocaleString() : "—";
  shipEl.dataset.cost = cost;
  updateSubtotal();
}

function proceedToCheckout() {
  if (Object.keys(_selectedCerts).length === 0) {
    toast("Please select at least one certificate", "error");
    return;
  }

  // Collect academic info
  const academic = {};
  document.querySelectorAll("#order-content [data-field]").forEach((el) => {
    const key = el.dataset.field;
    const section = el.closest(".form-section");
    if (
      section &&
      section.querySelector("h3").textContent.includes("Academic")
    ) {
      academic[key] = el.value;
    }
  });
  // Check required academic
  let allFilled = true;
  document.querySelectorAll(".form-section").forEach((sec) => {
    sec.querySelectorAll("[required]").forEach((input) => {
      if (!input.value.trim()) {
        input.style.borderColor = "var(--rust)";
        allFilled = false;
      } else {
        input.style.borderColor = "";
      }
    });
  });
  if (!allFilled) {
    toast("Please fill all required fields", "error");
    return;
  }

  // Collect all field values
  const academicInfo = {},
    shippingInfo = {};
  document.querySelectorAll(".form-section").forEach((sec) => {
    const isShipping = sec.querySelector("h3").textContent.includes("Shipping");
    sec.querySelectorAll("[data-field]").forEach((el) => {
      const key = el.dataset.field;
      (isShipping ? shippingInfo : academicInfo)[key] = el.value;
    });
    // Radio
    sec.querySelectorAll("[type=radio]:checked").forEach((r) => {
      const key = r.name.split("_").pop();
      (isShipping ? shippingInfo : academicInfo)[key] = r.value;
    });
  });

  State.pendingOrder = {
    card: State.currentOrder,
    selectedCerts: Object.keys(_selectedCerts),
    academicInfo,
    shippingInfo,
  };
  Storage.set("pendingOrder", State.pendingOrder);
  Router.go("checkout");
}

// ============================================================
// CHECKOUT PAGE
// ============================================================
async function loadCheckoutPage() {
  showPage("checkout");
  const order = State.pendingOrder || Storage.get("pendingOrder");
  if (!order) {
    Router.go("home");
    return;
  }
  if (!State.user) {
    Storage.set("pendingOrderRoute", "checkout");
    openLoginModal();
    return;
  }

  const wrap = document.getElementById("checkout-content");
  wrap.innerHTML = '<div class="spinner"></div>';

  const methods = await api(`${BASE_API}/public.php?ep=payment_methods`);
  const card = order.card || {};
  const prices = card.prices || {};
  const certsTotal = order.selectedCerts.reduce(
    (sum, c) => sum + (prices[c] || 0),
    0,
  );

  // Get shipping cost
  const city = order.shippingInfo?.city || "";
  const allRates = await api(`${BASE_API}/public.php?ep=shipping`);
  const rateObj = allRates.find((r) => r.city === city);
  const shipCost = rateObj ? Number(rateObj.rate) : 0;
  const total = certsTotal + shipCost;

  wrap.innerHTML = `
    <div class="checkout-header">
      <h2>✦ Payment & Checkout</h2>
      <p style="color:var(--border);font-style:italic;">Review your order and complete payment</p>
    </div>

    <div class="order-summary-box">
      <h3>✦ Order Summary</h3>
      <div class="subtotal-row"><span>Service</span><span>${card.title || ""}</span></div>
      ${order.selectedCerts.map((c) => `<div class="subtotal-row"><span>${c}</span><span>Rs. ${(prices[c] || 0).toLocaleString()}</span></div>`).join("")}
      <div class="subtotal-row"><span>Shipping to ${city || "—"}</span><span>Rs. ${shipCost.toLocaleString()}</span></div>
      <div class="subtotal-row" style="margin-top:8px;padding-top:8px;border-top:2px solid var(--border);">
        <span class="subtotal-total">Total</span>
        <span class="subtotal-total">Rs. ${total.toLocaleString()}</span>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-header"><span class="section-icon">💳</span><h3>Select Payment Method</h3></div>
      <div class="form-section-body">
        <div class="payment-methods-list" id="payment-methods-list">
          ${
            !methods || !methods.length
              ? '<p style="color:var(--border);font-style:italic;">No payment methods configured</p>'
              : methods
                  .map(
                    (m, i) => `
            <div class="payment-method-card ${i === 0 ? "selected" : ""}" id="pm-${m.id}" onclick="selectPaymentMethod(${m.id})">
              <input type="radio" name="payment_method" value="${m.id}" ${i === 0 ? "checked" : ""}>
              <div class="pm-icon">${m.logo_icon}</div>
              <div class="pm-info">
                <div class="pm-name">${m.name}</div>
                <div class="pm-account">${m.account_title ? `${m.account_title} · ` : ""}${m.account_number || ""}</div>
                ${m.instructions ? `<div class="pm-instructions">${m.instructions}</div>` : ""}
              </div>
            </div>`,
                  )
                  .join("")
          }
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-header"><span class="section-icon">🧾</span><h3>Transaction Details</h3></div>
      <div class="form-section-body">
        <div class="form-group">
          <label class="form-label">Transaction ID / Reference Number</label>
          <input type="text" class="form-input" id="txn-id" placeholder="Enter transaction ID after payment">
        </div>
        <div class="form-group payment-proof-section">
          <label class="form-label">Upload Payment Proof (Screenshot)</label>
          <div class="file-upload-area" onclick="document.getElementById('proof-file').click()">
            <input type="file" id="proof-file" accept="image/*" onchange="previewProof(this)">
            <div id="proof-preview">
              <div class="upload-icon">📷</div>
              <div class="upload-text">Click to upload payment screenshot</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <button class="btn btn-primary btn-full" style="font-size:1rem;padding:14px;" onclick="submitPayment(${total}, ${methods?.[0]?.id || 0})">
      ✦ Confirm & Place Order
    </button>`;

  window._checkoutTotal = total;
  window._checkoutMethodId = methods?.[0]?.id || 0;
}

function selectPaymentMethod(id) {
  document
    .querySelectorAll(".payment-method-card")
    .forEach((el) => el.classList.remove("selected"));
  document.getElementById("pm-" + id)?.classList.add("selected");
  document.querySelector(`input[value="${id}"]`)?.click();
  window._checkoutMethodId = id;
}

function previewProof(input) {
  if (!input.files?.[0]) return;
  const url = URL.createObjectURL(input.files[0]);
  document.getElementById("proof-preview").innerHTML =
    `<img src="${url}" style="max-height:150px;margin:0 auto;border-radius:4px;">`;
}

async function submitPayment(total, defaultMethodId) {
  const order = State.pendingOrder || Storage.get("pendingOrder");
  if (!order) return;

  const methodId = window._checkoutMethodId || defaultMethodId;
  const txnId = document.getElementById("txn-id")?.value || "";
  const proofFile = document.getElementById("proof-file")?.files?.[0];

  // 1. Create order
  const btn = document.querySelector("#checkout-content .btn-primary");
  btn.disabled = true;
  btn.textContent = "Creating order…";

  const orderRes = await apiPost(`${BASE_API}/orders.php?action=create`, {
    card_id: order.card.id,
    selected_certificates: order.selectedCerts,
    academic_info: order.academicInfo,
    shipping_info: order.shippingInfo,
  });

  if (!orderRes.success) {
    btn.disabled = false;
    btn.textContent = "✦ Confirm & Place Order";
    toast(orderRes.error || "Failed to create order", "error");
    return;
  }

  const orderId = orderRes.order_id;

  // 2. Submit payment
  btn.textContent = "Submitting payment…";
  const formData = new FormData();
  formData.append("order_id", orderId);
  formData.append("method_id", methodId);
  formData.append("transaction_id", txnId);
  if (proofFile) formData.append("proof", proofFile);

  const payRes = await apiPostForm(
    `${BASE_API}/orders.php?action=pay`,
    formData,
  );

  btn.disabled = false;
  btn.textContent = "✦ Confirm & Place Order";

  if (payRes.success || orderRes.success) {
    Storage.del("pendingOrder");
    Storage.del("pendingCard");
    State.pendingOrder = null;
    toast(
      "Order placed successfully! Order #" + orderRes.order_number,
      "success",
      5000,
    );
    Router.go("orders");
  } else {
    toast(payRes.error || "Payment submission failed", "error");
  }
}

// ============================================================
// MY ORDERS PAGE
// ============================================================
async function loadOrdersPage() {
  showPage("orders");
  if (!State.user) {
    openLoginModal();
    return;
  }
  const wrap = document.getElementById("orders-content");
  wrap.innerHTML = '<div class="spinner"></div>';
  const res = await api(`${BASE_API}/orders.php?action=my_orders`, {
    headers: {
      Authorization: "Bearer " + State.token,
      "Content-Type": "application/json",
    },
  });
  const orders = res.orders || [];
  if (!orders.length) {
    wrap.innerHTML = `<div class="empty-state"><div class="empty-icon">📭</div><h3>No orders yet</h3><p>Place your first order to get started</p><br><a class="btn btn-gold" href="#home">Browse Services</a></div>`;
    return;
  }
  wrap.innerHTML = `<div class="orders-list">${orders.map(renderOrderCard).join("")}</div>`;
}

function renderOrderCard(o) {
  const snap = o.card_snapshot || {};
  const certs = (o.selected_certificates || []).join(", ");
  return `
    <div class="order-card" onclick="viewOrderDetail(${o.id})">
      <div class="order-card-header">
        <div>
          <div class="order-number">📋 ${o.order_number}</div>
          <div class="order-date">${new Date(o.created_at).toLocaleDateString("en-PK", { day: "numeric", month: "short", year: "numeric" })}</div>
        </div>
        <div class="order-badges">
          <span class="badge badge-${o.order_status}">${o.order_status}</span>
          <span class="badge badge-${o.payment_status}">${o.payment_status}</span>
          ${o.payment_status_detail ? `<span class="badge badge-${o.payment_status_detail}">proof: ${o.payment_status_detail}</span>` : ""}
        </div>
      </div>
      <div class="order-card-body">
        <div>${snap.title || "Certificate Service"}</div>
        <div style="margin-top:4px;">${certs}</div>
        <div style="margin-top:4px;">📍 ${o.shipping_info?.city || ""}</div>
        <div class="order-total">Rs. ${Number(o.total).toLocaleString()}</div>
        ${o.payment_status === "unpaid" ? `<button class="btn btn-gold" style="margin-top:10px;padding:7px 18px;font-size:0.75rem;" onclick="event.stopPropagation();payExisting(${o.id})">💳 Pay Now</button>` : ""}
      </div>
    </div>`;
}

async function viewOrderDetail(orderId) {
  const res = await api(`${BASE_API}/orders.php?action=get&id=${orderId}`, {
    headers: { Authorization: "Bearer " + State.token },
  });
  if (!res.success) {
    toast("Order not found", "error");
    return;
  }
  const o = res.order;
  const snap = o.card_snapshot || {};
  const prices = snap.prices || {};
  const certs = o.selected_certificates || [];

  const detail = document.getElementById("order-detail-body");
  detail.innerHTML = `
    <div style="margin-bottom:16px;">
      <div style="font-family:var(--font-stamp);color:var(--border);font-size:0.75rem;text-transform:uppercase;letter-spacing:1px;">Order Number</div>
      <div style="font-family:var(--font-stamp);font-size:1.1rem;color:var(--mahogany);">${o.order_number}</div>
    </div>
    <div class="order-badges" style="margin-bottom:16px;">
      <span class="badge badge-${o.order_status}">${o.order_status}</span>
      <span class="badge badge-${o.payment_status}">${o.payment_status}</span>
    </div>
    <div class="order-summary-box" style="margin-bottom:16px;">
      <h3>✦ Service Details</h3>
      <div class="subtotal-row"><span>Service</span><span>${snap.title || "—"}</span></div>
      ${certs.map((c) => `<div class="subtotal-row"><span>${c}</span><span>Rs. ${(prices[c] || 0).toLocaleString()}</span></div>`).join("")}
      <div class="subtotal-row"><span>Shipping</span><span>Rs. ${Number(o.shipping_cost).toLocaleString()}</span></div>
      <div class="subtotal-row"><strong>Total</strong><strong>Rs. ${Number(o.total).toLocaleString()}</strong></div>
    </div>
    <div class="order-summary-box" style="margin-bottom:16px;">
      <h3>✦ Shipping Info</h3>
      ${Object.entries(o.shipping_info || {})
        .map(
          ([k, v]) =>
            `<div class="subtotal-row"><span style="text-transform:capitalize;">${k.replace(/_/g, " ")}</span><span>${v}</span></div>`,
        )
        .join("")}
    </div>
    ${o.payment_status === "unpaid" ? `<button class="btn btn-gold btn-full" onclick="closeModal('order-detail-modal');payExisting(${o.id})">💳 Pay Now</button>` : ""}
    ${o.proof_image ? `<div style="margin-top:12px;"><div style="font-family:var(--font-stamp);font-size:0.75rem;color:var(--border);margin-bottom:6px;">PAYMENT PROOF</div><img src="${o.proof_image}" style="max-width:100%;border-radius:4px;border:2px solid var(--border);"></div>` : ""}`;

  openModal("order-detail-modal");
}

async function payExisting(orderId) {
  // Load methods and show payment form
  const methods = await api(`${BASE_API}/public.php?ep=payment_methods`);
  const orderRes = await api(
    `${BASE_API}/orders.php?action=get&id=${orderId}`,
    {
      headers: { Authorization: "Bearer " + State.token },
    },
  );
  if (!orderRes.success) return;
  const o = orderRes.order;

  const body = document.getElementById("pay-existing-body");
  body.innerHTML = `
    <p style="margin-bottom:16px;color:var(--rust);">Complete payment for order <strong>${o.order_number}</strong> — Total: <strong>Rs. ${Number(o.total).toLocaleString()}</strong></p>
    <div class="payment-methods-list">
      ${methods
        .map(
          (m, i) => `
        <div class="payment-method-card ${i === 0 ? "selected" : ""}" id="pex-pm-${m.id}" onclick="selectExPM(${m.id})">
          <input type="radio" name="pex_method" value="${m.id}" ${i === 0 ? "checked" : ""}>
          <div class="pm-icon">${m.logo_icon}</div>
          <div class="pm-info">
            <div class="pm-name">${m.name}</div>
            <div class="pm-account">${m.account_title || ""} · ${m.account_number || ""}</div>
            ${m.instructions ? `<div class="pm-instructions">${m.instructions}</div>` : ""}
          </div>
        </div>`,
        )
        .join("")}
    </div>
    <div class="form-group" style="margin-top:16px;">
      <label class="form-label">Transaction ID</label>
      <input type="text" class="form-input" id="pex-txn" placeholder="Enter transaction ID">
    </div>
    <div class="form-group">
      <label class="form-label">Payment Screenshot</label>
      <div class="file-upload-area" onclick="document.getElementById('pex-proof').click()">
        <input type="file" id="pex-proof" accept="image/*" onchange="previewExProof(this)">
        <div id="pex-preview"><div class="upload-icon">📷</div><div class="upload-text">Upload screenshot</div></div>
      </div>
    </div>
    <button class="btn btn-primary btn-full" style="margin-top:8px;" onclick="submitExistingPayment(${orderId}, ${methods[0]?.id || 0})">✦ Submit Payment</button>`;

  window._exMethodId = methods[0]?.id || 0;
  openModal("pay-existing-modal");
}

function selectExPM(id) {
  document
    .querySelectorAll('[id^="pex-pm-"]')
    .forEach((el) => el.classList.remove("selected"));
  document.getElementById("pex-pm-" + id)?.classList.add("selected");
  window._exMethodId = id;
}
function previewExProof(input) {
  if (!input.files?.[0]) return;
  document.getElementById("pex-preview").innerHTML =
    `<img src="${URL.createObjectURL(input.files[0])}" style="max-height:120px;margin:0 auto;">`;
}

async function submitExistingPayment(orderId, defMethod) {
  const methodId = window._exMethodId || defMethod;
  const txnId = document.getElementById("pex-txn")?.value || "";
  const proofFile = document.getElementById("pex-proof")?.files?.[0];
  const formData = new FormData();
  formData.append("order_id", orderId);
  formData.append("method_id", methodId);
  formData.append("transaction_id", txnId);
  if (proofFile) formData.append("proof", proofFile);
  const res = await apiPostForm(`${BASE_API}/orders.php?action=pay`, formData);
  if (res.success) {
    toast("Payment submitted!", "success");
    closeModal("pay-existing-modal");
    loadOrdersPage();
  } else {
    toast(res.error || "Failed", "error");
  }
}

// ============================================================
// PROFILE PAGE
// ============================================================
async function loadProfilePage() {
  showPage("profile");
  if (!State.user) {
    openLoginModal();
    return;
  }
  renderProfilePage();
}

function renderProfilePage() {
  const u = State.user;
  const pic = u.profile_pic
    ? `<img src="${u.profile_pic}" alt="profile">`
    : `<div style="width:100%;height:100%;background:var(--rust);display:flex;align-items:center;justify-content:center;font-size:2.5rem;color:var(--cream);font-family:var(--font-stamp);">${u.name.charAt(0).toUpperCase()}</div>`;

  document.getElementById("profile-content").innerHTML = `
    <div class="profile-wrap">
      <div class="profile-header-card">
        <div class="profile-avatar-large" onclick="document.getElementById('pic-file').click()">
          ${pic}
          <div class="avatar-overlay">📷</div>
          <input type="file" id="pic-file" accept="image/*" style="display:none;" onchange="uploadProfilePic(this)">
        </div>
        <div class="profile-header-info">
          <h2>${u.name}</h2>
          <p>📧 ${u.email}</p>
          ${u.phone ? `<p>📱 ${u.phone}</p>` : ""}
          <button class="btn btn-gold" style="margin-top:12px;padding:7px 18px;font-size:0.78rem;" onclick="openEditProfile()">✏️ Edit Profile</button>
        </div>
      </div>
    </div>`;
}

async function uploadProfilePic(input) {
  if (!input.files?.[0]) return;
  const fd = new FormData();
  fd.append("profile_pic", input.files[0]);
  toast("Uploading…", "info", 1500);
  const res = await apiPostForm(`${BASE_API}/auth.php?action=upload_pic`, fd);
  if (res.success) {
    State.user.profile_pic = res.profile_pic;
    Storage.set("user", State.user);
    renderNavbar();
    renderProfilePage();
    toast("Profile picture updated!", "success");
  } else {
    toast(res.error || "Upload failed", "error");
  }
}

function openEditProfile() {
  const u = State.user;
  document.getElementById("edit-name").value = u.name || "";
  document.getElementById("edit-phone").value = u.phone || "";
  openModal("edit-profile-modal");
}

async function saveProfile(e) {
  e.preventDefault();
  const name = document.getElementById("edit-name").value;
  const phone = document.getElementById("edit-phone").value;
  const res = await apiPost(`${BASE_API}/auth.php?action=update`, {
    name,
    phone,
  });
  if (res.success) {
    State.user = { ...State.user, name, phone };
    Storage.set("user", State.user);
    renderNavbar();
    renderProfilePage();
    closeModal("edit-profile-modal");
    toast("Profile updated!", "success");
  } else {
    toast(res.error || "Update failed", "error");
  }
}

// ============================================================
// MODAL HELPERS
// ============================================================
function openModal(id) {
  document.getElementById(id)?.classList.add("open");
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove("open");
}
// Close on overlay click
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("modal-overlay"))
    e.target.classList.remove("open");
});

// ============================================================
// INIT
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  initAuth();

  // Register routes
  Router.register("home", loadHomePage);
  Router.register("order", loadOrderPage);
  Router.register("checkout", loadCheckoutPage);
  Router.register("orders", loadOrdersPage);
  Router.register("profile", loadProfilePage);

  renderNavbar();

  // Hide loading screen
  setTimeout(() => {
    document.getElementById("loading-screen")?.classList.add("hidden");
  }, 800);

  // Handle initial route
  Router.handle();
});
