/* ============================================================
   HANDY ADMIN PANEL — admin.js
   Full CRUD for all sections
   ============================================================ */

const ADMIN_API = '../admin/api/index.php';
const PUB_API   = '../api';

// Get current token from localStorage
function getAdminToken() {
    var stored = localStorage.getItem('handy_token');
    if (!stored) return null;
    // If it looks like a JWT (has dots), return as-is
    if (stored && stored.indexOf('.') > 0) {
        return stored;
    }
    try {
        return JSON.parse(stored);
    } catch(e) {
        return null;
    }
}

// ---- API ----
async function aApi(url, opts = {}) {
  const headers = { 'Content-Type': 'application/json' };
  const token = getAdminToken();
  if (token) headers['Authorization'] = 'Bearer ' + token;
  const res = await fetch(url, { headers, ...opts });
  return res.json();
}
async function aPost(url, body) {
  return aApi(url, { method: 'POST', body: JSON.stringify(body) });
}
async function aPostForm(url, fd) {
  const h = {};
  const token = getAdminToken();
  if (token) h['Authorization'] = 'Bearer ' + token;
  const res = await fetch(url, { method: 'POST', headers: h, body: fd });
  return res.json();
}

// ---- Toast ----
function aToast(msg, type = 'info', dur = 3000) {
  const c = document.getElementById('admin-toast');
  const icons = {success:'✅',error:'❌',info:'ℹ️'};
  const el = document.createElement('div');
  el.className = `a-toast a-toast-${type}`;
  el.innerHTML = `<span>${icons[type]}</span><span>${msg}</span>`;
  c.appendChild(el);
  setTimeout(() => { el.style.animation='fadeIn 0.3s ease reverse'; setTimeout(()=>el.remove(),300); }, dur);
}

// ---- Modal ----
function openModal(id) { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }
document.addEventListener('click', e => { if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open'); });

// ---- Navigation ----
function showSection(section) {
  document.querySelectorAll('.admin-section').forEach(s => s.style.display = 'none');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const el = document.getElementById('section-' + section);
  if (el) el.style.display = 'block';
  const nav = document.querySelector(`[data-section="${section}"]`);
  if (nav) nav.classList.add('active');
  document.getElementById('section-title').textContent = nav?.dataset.title || section;
  // Load data
  const loaders = {
    dashboard: loadDashboard, sliders: loadSliders, stats: loadStats,
    categories: loadCategories, cards: loadCards, fields: loadFields,
    payments_methods: loadPaymentMethods, orders: loadOrders, shipping: loadShipping,
    users: loadUsers,
  };
  if (loaders[section]) loaders[section]();
  // Mobile close
  document.querySelector('.sidebar')?.classList.remove('open');
}

// ============================================================
// DASHBOARD
// ============================================================
async function loadDashboard() {
  const res = await aApi(`${ADMIN_API}?section=dashboard`);
  const s = res.stats || {};
  document.getElementById('dash-content').innerHTML = `
    <div class="dash-grid">
      <div class="dash-card"><div class="dash-card-icon">📋</div><div class="dash-card-value">${s.total_orders||0}</div><div class="dash-card-label">Total Orders</div></div>
      <div class="dash-card"><div class="dash-card-icon">⏳</div><div class="dash-card-value">${s.pending_orders||0}</div><div class="dash-card-label">Pending Orders</div></div>
      <div class="dash-card"><div class="dash-card-icon">💰</div><div class="dash-card-value">${s.paid_orders||0}</div><div class="dash-card-label">Paid Orders</div></div>
      <div class="dash-card"><div class="dash-card-icon">👥</div><div class="dash-card-value">${s.total_users||0}</div><div class="dash-card-label">Registered Users</div></div>
      <div class="dash-card"><div class="dash-card-icon">📈</div><div class="dash-card-value">Rs. ${Number(s.total_revenue||0).toLocaleString()}</div><div class="dash-card-label">Total Revenue</div></div>
    </div>`;
}

// ============================================================
// SLIDERS
// ============================================================
async function loadSliders() {
  const res = await aApi(`${ADMIN_API}?section=sliders&action=list`);
  const rows = res.success ? (res.data || []) : [];
  const tbody = document.getElementById('sliders-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="5" class="table-empty">No sliders yet</td></tr>` :
    rows.map(r => `
      <tr>
        <td><img src="${r.image}" class="img-preview" onerror="this.style.display='none'"></td>
        <td>${r.title||'—'}</td>
        <td>${r.subtitle||'—'}</td>
        <td><span class="badge ${r.is_active?'badge-success':'badge-danger'}">${r.is_active?'Active':'Hidden'}</span></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="editSlider(${JSON.stringify(r)})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteSlider(${r.id})">Delete</button>
        </td>
      </tr>`).join('');
}

function editSlider(rJson) {
  const r = JSON.parse(rJson);
  document.getElementById('slider-form').reset();
  document.getElementById('slider-id').value       = r.id || '';
  document.getElementById('slider-title').value    = r.title || '';
  document.getElementById('slider-subtitle').value = r.subtitle || '';
  document.getElementById('slider-order').value    = r.sort_order || 0;
  document.getElementById('slider-active').className = 'toggle ' + (r.is_active ? 'on' : '');
  document.getElementById('slider-active').dataset.val = r.is_active ? '1' : '0';
  document.getElementById('slider-preview').innerHTML = r.image ? `<img src="${r.image}" style="max-height:80px;border-radius:4px;margin-top:8px;">` : '';
  openModal('slider-modal');
}

async function saveSlider(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.set('is_active', document.getElementById('slider-active').dataset.val || '1');
  const res = await aPostForm(`${ADMIN_API}?section=sliders&action=save`, fd);
  if (res.success) { aToast('Slider saved!', 'success'); closeModal('slider-modal'); loadSliders(); }
  else aToast(res.error || 'Failed', 'error');
}

async function deleteSlider(id) {
  if (!confirm('Delete this slider?')) return;
  const res = await aPost(`${ADMIN_API}?section=sliders&action=delete`, { id });
  if (res.success) { aToast('Deleted', 'info'); loadSliders(); }
}

// ============================================================
// STATS
// ============================================================
async function loadStats() {
  const res = await aApi(`${ADMIN_API}?section=stats&action=list`); const rows = res.success ? (res.data || []) : [];
  const tbody = document.getElementById('stats-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="5" class="table-empty">No stats yet</td></tr>` :
    rows.map(r => `
      <tr>
        <td style="font-size:1.4rem;">${r.icon}</td>
        <td>${r.label}</td>
        <td>${r.value}</td>
        <td><span class="badge ${r.is_active?'badge-success':'badge-danger'}">${r.is_active?'Active':'Hidden'}</span></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="editStat(${JSON.stringify(r)})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteStat(${r.id})">Delete</button>
        </td>
      </tr>`).join('');
}

function editStat(rJson) {
  const r = JSON.parse(rJson);
  document.getElementById('stat-id').value    = r.id || '';
  document.getElementById('stat-icon').value  = r.icon || '⭐';
  document.getElementById('stat-label').value = r.label || '';
  document.getElementById('stat-value').value = r.value || '';
  document.getElementById('stat-order').value = r.sort_order || 0;
  document.getElementById('stat-active').className = 'toggle ' + (r.is_active ? 'on' : '');
  document.getElementById('stat-active').dataset.val = r.is_active ? '1' : '0';
  openModal('stat-modal');
}

async function saveStat() {
  const d = {
    id:         document.getElementById('stat-id').value || null,
    icon:       document.getElementById('stat-icon').value,
    label:      document.getElementById('stat-label').value,
    value:      document.getElementById('stat-value').value,
    sort_order: document.getElementById('stat-order').value,
    is_active:  document.getElementById('stat-active').dataset.val || '1',
  };
  const res = await aPost(`${ADMIN_API}?section=stats&action=save`, d);
  if (res.success) { aToast('Saved!', 'success'); closeModal('stat-modal'); loadStats(); }
  else aToast(res.error || 'Failed', 'error');
}

async function deleteStat(id) {
  if (!confirm('Delete?')) return;
  await aPost(`${ADMIN_API}?section=stats&action=delete`, { id });
  loadStats();
}

// ============================================================
// CATEGORIES
// ============================================================
async function loadCategories() {
  const res = await aApi(`${ADMIN_API}?section=categories&action=list`); const rows = res.success ? (res.data || []) : [];
  const tbody = document.getElementById('cats-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="4" class="table-empty">No categories</td></tr>` :
    rows.map(r => `
      <tr>
        <td>${r.name}</td>
        <td>${r.sort_order}</td>
        <td><span class="badge ${r.is_active?'badge-success':'badge-danger'}">${r.is_active?'Active':'Hidden'}</span></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="editCat(${JSON.stringify(r)})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteCat(${r.id})">Delete</button>
        </td>
      </tr>`).join('');
}

function editCat(rJson) {
  const r = JSON.parse(rJson);
  document.getElementById('cat-id').value     = r.id || '';
  document.getElementById('cat-name').value   = r.name || '';
  document.getElementById('cat-order').value  = r.sort_order || 0;
  document.getElementById('cat-active').className  = 'toggle ' + (r.is_active ? 'on' : '');
  document.getElementById('cat-active').dataset.val = r.is_active ? '1' : '0';
  openModal('cat-modal');
}

async function saveCat() {
  const d = { id: document.getElementById('cat-id').value||null, name: document.getElementById('cat-name').value, sort_order: document.getElementById('cat-order').value, is_active: document.getElementById('cat-active').dataset.val||'1' };
  const res = await aPost(`${ADMIN_API}?section=categories&action=save`, d);
  if (res.success) { aToast('Saved!', 'success'); closeModal('cat-modal'); loadCategories(); }
  else aToast(res.error || 'Failed', 'error');
}

async function deleteCat(id) {
  if (!confirm('Delete category? This may affect cards.')) return;
  await aPost(`${ADMIN_API}?section=categories&action=delete`, { id });
  loadCategories();
}

// ============================================================
// CARDS
// ============================================================
let _adminCats = [];
async function loadCards() {
  const [rows, cats] = await Promise.all([
    aApi(`${ADMIN_API}?section=cards&action=list`),
    aApi(`${ADMIN_API}?section=categories&action=list`),
  ]);
  _adminCats = cats || [];
  const tbody = document.getElementById('cards-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="5" class="table-empty">No cards</td></tr>` :
    rows.map(r => {
      const prices = r.prices || {};
      const priceStr = Object.entries(prices).map(([k,v]) => `${k}: Rs.${v}`).join(', ');
      return `
        <tr>
          <td><img src="${r.image||''}" class="img-preview" onerror="this.style.display='none'"></td>
          <td><strong>${r.title}</strong><div style="font-size:0.78rem;opacity:0.6;">${r.category_name||'—'}</div></td>
          <td style="font-size:0.8rem;max-width:200px;">${priceStr}</td>
          <td><span class="badge ${r.is_active?'badge-success':'badge-danger'}">${r.is_active?'Active':'Hidden'}</span></td>
          <td>
            <button class="btn btn-outline btn-sm" onclick="editCard(${JSON.stringify(r)})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="deleteCard(${r.id})">Delete</button>
          </td>
        </tr>`;
    }).join('');
}

function editCard(rJson) {
  const r = JSON.parse(rJson);
  const catSelect = document.getElementById('card-category');
  catSelect.innerHTML = `<option value="">— Select Category —</option>` +
    _adminCats.map(c => `<option value="${c.id}" ${c.id == r.category_id ? 'selected' : ''}>${c.name}</option>`).join('');
  document.getElementById('card-id').value    = r.id || '';
  document.getElementById('card-title').value = r.title || '';
  document.getElementById('card-desc').value  = r.description || '';
  document.getElementById('card-order').value = r.sort_order || 0;
  document.getElementById('card-active').className  = 'toggle ' + (r.is_active ? 'on' : '');
  document.getElementById('card-active').dataset.val = r.is_active ? '1' : '0';
  // Prices
  const prices = r.prices || {};
  renderPriceRows(prices);
  document.getElementById('card-img-preview').innerHTML = r.image ? `<img src="${r.image}" style="max-height:70px;border-radius:3px;">` : '';
  openModal('card-modal');
}

let _priceRows = {};
function renderPriceRows(prices) {
  _priceRows = {...prices};
  refreshPriceRows();
}
function refreshPriceRows() {
  const wrap = document.getElementById('price-rows');
  wrap.innerHTML = Object.entries(_priceRows).map(([k, v]) => `
    <div style="display:flex;gap:8px;margin-bottom:6px;">
      <input class="form-input" style="flex:2;" placeholder="Certificate name" value="${k}" onchange="updatePriceKey(this, '${k}')">
      <input type="number" class="form-input" style="flex:1;" placeholder="Price" value="${v}" onchange="updatePriceVal('${k}', this.value)">
      <button type="button" class="btn btn-danger btn-sm" onclick="removePriceRow('${k}')">✕</button>
    </div>`).join('');
  document.getElementById('prices-json').value = JSON.stringify(_priceRows);
}
function addPriceRow() { const k = 'Certificate ' + (Object.keys(_priceRows).length + 1); _priceRows[k] = 0; refreshPriceRows(); }
function removePriceRow(k) { delete _priceRows[k]; refreshPriceRows(); }
function updatePriceKey(inp, oldKey) { const v = _priceRows[oldKey]; delete _priceRows[oldKey]; _priceRows[inp.value] = v; }
function updatePriceVal(k, v) { _priceRows[k] = Number(v); document.getElementById('prices-json').value = JSON.stringify(_priceRows); }

async function saveCard(e) {
  e.preventDefault();
  document.getElementById('prices-json').value = JSON.stringify(_priceRows);
  const fd = new FormData(e.target);
  fd.set('is_active', document.getElementById('card-active').dataset.val || '1');
  const res = await aPostForm(`${ADMIN_API}?section=cards&action=save`, fd);
  if (res.success) { aToast('Card saved!', 'success'); closeModal('card-modal'); loadCards(); }
  else aToast(res.error || 'Failed', 'error');
}

async function deleteCard(id) {
  if (!confirm('Delete this card?')) return;
  await aPost(`${ADMIN_API}?section=cards&action=delete`, { id });
  loadCards();
}

// ============================================================
// ORDER FIELDS
// ============================================================
async function loadFields() {
  const res = await aApi(`${ADMIN_API}?section=fields&action=list`); const rows = res.success ? (res.data || []) : [];
  ['academic','shipping'].forEach(sec => {
    const tbody = document.getElementById(`fields-${sec}-tbody`);
    const secRows = rows.filter(r => r.section === sec);
    tbody.innerHTML = !secRows.length ? `<tr><td colspan="5" class="table-empty">No fields</td></tr>` :
      secRows.map(r => `
        <tr>
          <td>${r.label}</td>
          <td><code style="background:var(--surface);padding:2px 6px;border-radius:2px;font-size:0.78rem;">${r.field_name}</code></td>
          <td><span class="badge badge-info">${r.field_type}</span></td>
          <td><span class="badge ${r.is_required?'badge-warning':'badge-secondary'}">${r.is_required?'Required':'Optional'}</span></td>
          <td>
            <button class="btn btn-outline btn-sm" onclick="editField(${JSON.stringify(r)})">Edit</button>
            <button class="btn btn-danger btn-sm" onclick="deleteField(${r.id})">Delete</button>
          </td>
        </tr>`).join('');
  });
}

function editField(rJson) {
  const r = JSON.parse(rJson);
  document.getElementById('field-id').value          = r.id || '';
  document.getElementById('field-section').value     = r.section || 'academic';
  document.getElementById('field-type').value        = r.field_type || 'text';
  document.getElementById('field-label').value       = r.label || '';
  document.getElementById('field-name').value        = r.field_name || '';
  document.getElementById('field-placeholder').value = r.placeholder || '';
  document.getElementById('field-required').className  = 'toggle ' + (r.is_required ? 'on' : '');
  document.getElementById('field-required').dataset.val = r.is_required ? '1' : '0';
  document.getElementById('field-order').value       = r.sort_order || 0;
  const opts = Array.isArray(r.options) ? r.options.join('\n') : '';
  document.getElementById('field-options').value     = opts;
  openModal('field-modal');
}

async function saveField() {
  const opts = document.getElementById('field-options').value.trim();
  const d = {
    id:           document.getElementById('field-id').value || null,
    section:      document.getElementById('field-section').value,
    field_type:   document.getElementById('field-type').value,
    label:        document.getElementById('field-label').value,
    field_name:   document.getElementById('field-name').value,
    placeholder:  document.getElementById('field-placeholder').value,
    is_required:  document.getElementById('field-required').dataset.val || '0',
    sort_order:   document.getElementById('field-order').value,
    is_active:    1,
    options:      opts ? opts.split('\n').map(o => o.trim()).filter(Boolean) : null,
  };
  const res = await aPost(`${ADMIN_API}?section=fields&action=save`, d);
  if (res.success) { aToast('Field saved!', 'success'); closeModal('field-modal'); loadFields(); }
  else aToast(res.error || 'Failed', 'error');
}

async function deleteField(id) {
  if (!confirm('Delete field?')) return;
  await aPost(`${ADMIN_API}?section=fields&action=delete`, { id });
  loadFields();
}

// ============================================================
// PAYMENT METHODS
// ============================================================
async function loadPaymentMethods() {
  const res = await aApi(`${ADMIN_API}?section=payment_methods&action=list`); const rows = res.success ? (res.data || []) : [];
  const tbody = document.getElementById('pm-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="5" class="table-empty">No payment methods</td></tr>` :
    rows.map(r => `
      <tr>
        <td style="font-size:1.4rem;">${r.logo_icon}</td>
        <td><strong>${r.name}</strong><div style="font-size:0.78rem;opacity:0.6;">${r.type}</div></td>
        <td style="font-size:0.82rem;">${r.account_title||''}<br>${r.account_number||''}</td>
        <td><span class="badge ${r.is_active?'badge-success':'badge-danger'}">${r.is_active?'Active':'Hidden'}</span></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="editPM(${JSON.stringify(r)})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deletePM(${r.id})">Delete</button>
        </td>
      </tr>`).join('');
}

function editPM(rJson) {
  const r = JSON.parse(rJson);
  document.getElementById('pm-id').value           = r.id || '';
  document.getElementById('pm-name').value         = r.name || '';
  document.getElementById('pm-type').value         = r.type || 'other';
  document.getElementById('pm-account').value      = r.account_number || '';
  document.getElementById('pm-title').value        = r.account_title || '';
  document.getElementById('pm-instructions').value = r.instructions || '';
  document.getElementById('pm-icon').value         = r.logo_icon || '💳';
  document.getElementById('pm-order').value        = r.sort_order || 0;
  document.getElementById('pm-active').className  = 'toggle ' + (r.is_active ? 'on' : '');
  document.getElementById('pm-active').dataset.val = r.is_active ? '1' : '0';
  openModal('pm-modal');
}

async function savePM() {
  const d = {
    id: document.getElementById('pm-id').value || null,
    name: document.getElementById('pm-name').value,
    type: document.getElementById('pm-type').value,
    account_number: document.getElementById('pm-account').value,
    account_title: document.getElementById('pm-title').value,
    instructions: document.getElementById('pm-instructions').value,
    logo_icon: document.getElementById('pm-icon').value,
    sort_order: document.getElementById('pm-order').value,
    is_active: document.getElementById('pm-active').dataset.val || '1',
  };
  const res = await aPost(`${ADMIN_API}?section=payment_methods&action=save`, d);
  if (res.success) { aToast('Saved!', 'success'); closeModal('pm-modal'); loadPaymentMethods(); }
  else aToast(res.error || 'Failed', 'error');
}

async function deletePM(id) {
  if (!confirm('Delete?')) return;
  await aPost(`${ADMIN_API}?section=payment_methods&action=delete`, { id });
  loadPaymentMethods();
}

// ============================================================
// ORDERS
// ============================================================
async function loadOrders() {
  const res = await aApi(`${ADMIN_API}?section=orders&action=list`); const rows = res.success ? (res.data || []) : [];
  const tbody = document.getElementById('orders-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="7" class="table-empty">No orders</td></tr>` :
    rows.map(r => `
      <tr>
        <td><strong>${r.order_number}</strong></td>
        <td>${r.user_name||'—'}<div style="font-size:0.75rem;opacity:0.6;">${r.user_phone||''}</div></td>
        <td style="font-size:0.82rem;">${(r.selected_certificates||[]).join(', ')}</td>
        <td>Rs. ${Number(r.total).toLocaleString()}</td>
        <td><span class="badge badge-${r.order_status==='completed'?'success':r.order_status==='cancelled'?'danger':'warning'}">${r.order_status}</span></td>
        <td><span class="badge badge-${r.payment_status==='paid'?'success':r.payment_status==='unpaid'?'danger':'warning'}">${r.payment_status}</span></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="viewOrder(${JSON.stringify(r)})">View</button>
        </td>
      </tr>`).join('');
}

function viewOrder(rJson) {
  const r = JSON.parse(rJson);
  const snap = r.card_snapshot || {};
  document.getElementById('order-detail-wrap').innerHTML = `
    <div style="margin-bottom:16px;">
      <div style="font-family:var(--font-s);color:var(--gold);font-size:0.7rem;letter-spacing:1px;margin-bottom:4px;">ORDER</div>
      <div style="font-size:1.1rem;color:var(--gold-lt);">${r.order_number}</div>
    </div>
    <div class="form-row" style="margin-bottom:16px;">
      <div><label class="form-label">Customer</label><div>${r.user_name}</div><div style="font-size:0.8rem;">${r.user_email}</div></div>
      <div><label class="form-label">Amount</label><div style="color:var(--gold-lt);font-size:1.1rem;">Rs. ${Number(r.total).toLocaleString()}</div></div>
    </div>
    <div style="margin-bottom:16px;"><label class="form-label">Certificates</label><div>${(r.selected_certificates||[]).join(', ')}</div></div>
    <div style="margin-bottom:16px;"><label class="form-label">Shipping</label><div>${JSON.stringify(r.shipping_info||{},null,2).replace(/{|}|"/g,'').trim()}</div></div>
    <div class="form-row">
      <div>
        <label class="form-label">Order Status</label>
        <select class="form-select" id="upd-order-status">
          ${['pending','confirmed','processing','completed','cancelled'].map(s=>`<option value="${s}" ${s===r.order_status?'selected':''}>${s}</option>`).join('')}
        </select>
      </div>
      <div>
        <label class="form-label">Payment Status</label>
        <select class="form-select" id="upd-payment-status">
          ${['unpaid','pending','paid','refunded'].map(s=>`<option value="${s}" ${s===r.payment_status?'selected':''}>${s}</option>`).join('')}
        </select>
      </div>
    </div>
    <button class="btn btn-gold" style="margin-top:16px;" onclick="updateOrderStatus(${r.id})">✦ Update Status</button>`;
  openModal('order-detail-modal');
}

async function updateOrderStatus(id) {
  const d = { id, order_status: document.getElementById('upd-order-status').value, payment_status: document.getElementById('upd-payment-status').value };
  const res = await aPost(`${ADMIN_API}?section=orders&action=update_status`, d);
  if (res.success) { aToast('Order updated!', 'success'); closeModal('order-detail-modal'); loadOrders(); }
  else aToast(res.error || 'Failed', 'error');
}

// ============================================================
// SHIPPING RATES
// ============================================================
async function loadShipping() {
  const res = await aApi(`${ADMIN_API}?section=shipping&action=list`); const rows = res.success ? (res.data || []) : [];
  const tbody = document.getElementById('shipping-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="4" class="table-empty">No rates</td></tr>` :
    rows.map(r => `
      <tr>
        <td>${r.city}</td>
        <td>Rs. ${Number(r.rate).toLocaleString()}</td>
        <td><span class="badge ${r.is_active?'badge-success':'badge-danger'}">${r.is_active?'Active':'Inactive'}</span></td>
        <td>
          <button class="btn btn-outline btn-sm" onclick="editShipping(${JSON.stringify(r)})">Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteShipping(${r.id})">Delete</button>
        </td>
      </tr>`).join('');
}

function editShipping(rJson) {
  const r = JSON.parse(rJson);
  document.getElementById('ship-id').value     = r.id || '';
  document.getElementById('ship-city').value   = r.city || '';
  document.getElementById('ship-rate').value   = r.rate || 0;
  document.getElementById('ship-active').className  = 'toggle ' + (r.is_active ? 'on' : '');
  document.getElementById('ship-active').dataset.val = r.is_active ? '1' : '0';
  openModal('ship-modal');
}

async function saveShipping() {
  const d = { id: document.getElementById('ship-id').value||null, city: document.getElementById('ship-city').value, rate: document.getElementById('ship-rate').value, is_active: document.getElementById('ship-active').dataset.val||'1' };
  const res = await aPost(`${ADMIN_API}?section=shipping&action=save`, d);
  if (res.success) { aToast('Saved!', 'success'); closeModal('ship-modal'); loadShipping(); }
}

async function deleteShipping(id) {
  if (!confirm('Delete?')) return;
  await aPost(`${ADMIN_API}?section=shipping&action=delete`, { id });
  loadShipping();
}

// ============================================================
// USERS
// ============================================================
async function loadUsers() {
  const res = await aApi(`${ADMIN_API}?section=users&action=list`);
  const rows = (res.success && Array.isArray(res.data)) ? res.data : [];
  const tbody = document.getElementById('users-tbody');
  tbody.innerHTML = !rows.length ? `<tr><td colspan="6" class="table-empty">No users yet</td></tr>` :
    rows.map(r => `
      <tr>
        <td><strong>${r.name || '—'}</strong></td>
        <td>${r.email || '—'}</td>
        <td>${r.phone || '—'}</td>
        <td><span class="badge ${r.role === 'admin' ? 'badge-warning' : 'badge-success'}">${r.role || 'user'}</span></td>
        <td style="font-size:0.82rem;">${r.created_at ? new Date(r.created_at).toLocaleDateString() : '—'}</td>
        <td>
          ${r.role !== 'admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${r.id})">Delete</button>` : '—'}
        </td>
      </tr>`).join('');
}

async function deleteUser(id) {
  if (!confirm('Delete this user? This will also delete all their orders.')) return;
  await aPost(`${ADMIN_API}?section=users&action=delete`, { id });
  aToast('User deleted', 'success');
  loadUsers();
}

// ---- Toggle helper ----
function toggleSwitch(id) {
  const el = document.getElementById(id);
  const isOn = el.classList.contains('on');
  el.classList.toggle('on');
  el.dataset.val = isOn ? '0' : '1';
}

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  showSection('dashboard');
});
