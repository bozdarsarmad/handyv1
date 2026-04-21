# 📜 Handy — Certificate Services Platform

A full-stack retro-styled web application for academic certificate services across Sind, Pakistan. Built with **Vanilla JS SPA** (frontend) and **PHP + MySQL** (backend).

---

## 🗂️ Project Structure

```
handy/
├── index.php                  # Main SPA entry point
├── .htaccess                  # Apache rewrite rules
│
├── api/
│   ├── config.php             # DB config, JWT helpers, response utilities
│   ├── auth.php               # Login, register, profile, profile pic upload
│   ├── public.php             # Public data: sliders, stats, cards, fields, shipping, payments
│   └── orders.php             # Create order, my orders, submit payment
│
├── admin/
│   ├── index.php              # Admin panel SPA
│   └── api/
│       └── index.php          # Admin CRUD API (all sections)
│
├── assets/
│   ├── css/
│   │   └── style.css          # Retro user-side styles
│   └── js/
│       └── app.js             # Full SPA router + all page logic
│
├── admin/assets/
│   ├── css/admin.css          # Admin panel styles
│   └── js/admin.js            # Admin panel JS (all CRUD)
│
├── uploads/                   # Auto-created on upload
│   ├── profiles/              # User profile pictures
│   ├── sliders/               # Slider & card images
│   └── payments/              # Payment proof screenshots
│
└── database/
    └── schema.sql             # Full database schema + seed data
```

---

## ⚙️ Requirements

| Requirement | Version |
|---|---|
| PHP | 7.4 or higher |
| MySQL / MariaDB | 5.7 / 10.3 or higher |
| Apache | with `mod_rewrite` enabled |
| XAMPP / WAMP / cPanel | Any |

---

## 🚀 Installation Guide

### Step 1 — Copy Files
Place the entire `handy/` folder inside your web root:
- **XAMPP**: `C:/xampp/htdocs/handy/`
- **WAMP**: `C:/wamp64/www/handy/`
- **cPanel**: Upload to `public_html/handy/` or your domain root

### Step 2 — Create Database
1. Open **phpMyAdmin** → Create a new database named `handy_db`
2. Select `handy_db` → Click **Import**
3. Choose `database/schema.sql` → Click **Go**

This will create all tables and insert sample data including:
- Admin user: `admin@handy.pk` / `admin123` *(change immediately!)*
- Sample categories, cards, sliders, stats, payment methods, shipping rates

### Step 3 — Configure Database
Edit `api/config.php`:

```php
define('DB_HOST', 'localhost');   // Your DB host
define('DB_USER', 'root');        // Your DB username
define('DB_PASS', '');            // Your DB password
define('DB_NAME', 'handy_db');    // Database name
define('BASE_URL', 'http://localhost/handy');  // ← Change this!
```

### Step 4 — Configure Upload Permissions
Make sure the `uploads/` folder is **writable** by PHP:

```bash
# Linux/Mac
chmod -R 755 uploads/

# Or via cPanel → File Manager → Right-click uploads → Permissions → 755
```

### Step 5 — Enable Apache mod_rewrite
In `httpd.conf` or your VirtualHost, ensure:
```apache
AllowOverride All
```

### Step 6 — Access the Site
- **User Site**: `http://localhost/handy/`
- **Admin Panel**: `http://localhost/handy/admin/`

---

## 🔐 Admin Login

| Field | Value |
|---|---|
| Email | `admin@handy.pk` |
| Password | `admin123` |

> ⚠️ **Change the admin password immediately** after first login via your database or by modifying the seed data.

---

## 👤 User Features

| Feature | Description |
|---|---|
| **Register / Login** | JWT-based auth with email + password |
| **Profile Picture** | Upload rounded profile pic (like Facebook) |
| **Browse Services** | Filter by category (Matric / Intermediate / University) |
| **Image Slider** | Auto-playing retro homepage slider |
| **Order Service** | Select certificates, fill academic + shipping info |
| **Dynamic Forms** | All form fields come from admin panel |
| **Subtotal** | Auto-calculated based on selected certs + city shipping |
| **Checkout** | Pay via EasyPaisa, JazzCash, Bank Transfer |
| **Upload Proof** | Upload payment screenshot |
| **My Orders** | View all orders with status |
| **Pay Unpaid** | Pay for unpaid orders directly from orders page |

---

## 🛠️ Admin Panel Features

### Homepage Management
- **Sliders** — Add/edit/delete image sliders with title and subtitle
- **Stats Badges** — Manage "Fast Delivery", "Total Users", rating badges etc.

### Service Management
- **Categories** — Add/remove/reorder categories (Matric, Intermediate, University)
- **Service Cards** — Full card management: image, title, description, dynamic pricing rows

### Order Form Builder
- **Form Fields** — Add any field type: text, number, email, phone, select, radio, checkbox, textarea, date
- Two sections: **Academic** and **Shipping**
- Set field as required/optional
- For select/radio/checkbox — enter options line by line

### Orders
- View all orders with full details
- Update **order status**: pending → confirmed → processing → completed → cancelled
- Update **payment status**: unpaid → pending → paid → refunded
- View academic info, shipping info, selected certificates

### Payments
- **CRUD payment methods**: EasyPaisa, JazzCash, Bank, or custom
- Set account number, account title, instructions, icon
- Activate/deactivate methods

### Shipping
- Set delivery rates per city
- Add new cities, update rates, activate/deactivate

---

## 🎨 Design System

The site uses a **retro vintage Pakistan 1960s** aesthetic:

| Element | Value |
|---|---|
| Primary Font | Playfair Display (serif) |
| Body Font | Crimson Pro |
| Accent Font | IM Fell English SC |
| Primary Color | Mahogany `#6b1f0a` |
| Accent Color | Gold `#c9921a` |
| Background | Cream / Parchment |
| Theme | Retro vintage, warm, stamp-paper feel |

---

## 📡 API Endpoints

### Public (No Auth)
```
GET  api/public.php?ep=sliders
GET  api/public.php?ep=stats
GET  api/public.php?ep=categories
GET  api/public.php?ep=cards
GET  api/public.php?ep=cards&category_id=1
GET  api/public.php?ep=card&id=1
GET  api/public.php?ep=fields
GET  api/public.php?ep=fields&section=academic
GET  api/public.php?ep=shipping
GET  api/public.php?ep=payment_methods
```

### Auth
```
POST api/auth.php?action=register       { name, email, password, phone }
POST api/auth.php?action=login          { email, password }
GET  api/auth.php?action=me             [Auth required]
POST api/auth.php?action=update         [Auth required] { name, phone }
POST api/auth.php?action=upload_pic     [Auth required] FormData: profile_pic
```

### Orders
```
POST api/orders.php?action=create       [Auth required]
GET  api/orders.php?action=my_orders    [Auth required]
GET  api/orders.php?action=get&id=N     [Auth required]
POST api/orders.php?action=pay          [Auth required] FormData
```

### Admin API (Admin Auth required)
```
GET/POST  admin/api/index.php?section=dashboard
GET/POST  admin/api/index.php?section=sliders&action=list|save|delete
GET/POST  admin/api/index.php?section=stats&action=list|save|delete
GET/POST  admin/api/index.php?section=categories&action=list|save|delete
GET/POST  admin/api/index.php?section=cards&action=list|save|delete
GET/POST  admin/api/index.php?section=fields&action=list|save|delete
GET/POST  admin/api/index.php?section=payment_methods&action=list|save|delete
GET/POST  admin/api/index.php?section=orders&action=list|update_status
GET/POST  admin/api/index.php?section=shipping&action=list|save|delete
```

---

## 🔒 Security Notes

1. **Change the admin password** in the database after setup
2. **Change `JWT_SECRET`** in `api/config.php` to a long random string
3. The `uploads/` folder is protected from PHP execution via `.htaccess`
4. All admin routes require a valid admin JWT token
5. For production, use **HTTPS**
6. Consider adding rate limiting to auth endpoints

---

## 🐛 Troubleshooting

| Issue | Solution |
|---|---|
| Blank page | Check PHP error logs; ensure `mod_rewrite` is enabled |
| DB connection error | Verify `api/config.php` credentials |
| Uploads not saving | Set `uploads/` folder to `chmod 755` |
| 404 on API calls | Ensure `.htaccess` is working and `AllowOverride All` is set |
| Admin login fails | Verify `handy_db` schema was imported correctly |
| Images not showing | Check `BASE_URL` in `api/config.php` matches your domain |

---

## 📝 License

This project is built for **Handy Certificate Services**. All rights reserved.

---

*Built with ❤️ using Vanilla JS + PHP — Retro vintage design inspired by classic Urdu newspapers and stamp-paper aesthetics of Pakistan.*
