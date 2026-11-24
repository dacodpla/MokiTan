# Moki-Tan

A full-stack e-commerce store for model kits, paints, tools and materials, with a role-based Seller Center for the shop owner.

- **Framework:** Laravel 13 (PHP 8.3)
- **Frontend:** Tailwind CSS 3 + Livewire 4 (single-file components) + Alpine.js
- **Auth:** Laravel Breeze (Blade) with two roles: `customer` and `admin`
- **Database:** MySQL 8 by default (any Laravel-supported driver works)
- **Orders:** every checkout creates a **Pending** order and takes stock; the admin confirms payment (Paid) and ships it (Shipped, with a tracking number)

---

## Requirements

- PHP **8.3+** with the standard Laravel extensions (`pdo_mysql`, `mbstring`, `openssl`, `curl`, `gd`, `fileinfo`, `zip`, `xml`)
- **Composer 2.x**
- **Node.js 20+** and **npm 10+**
- **MySQL 8+** (or MariaDB 10.6+) running on `127.0.0.1:3306`
- On Windows, [Laragon](https://laragon.org/) bundles PHP + MySQL + nginx and works out of the box

---

## Setup

Clone the repo, then from the project root:

```bash
# 1. PHP + JS dependencies
composer install
npm install

# 2. Environment
cp .env.example .env         # (Windows PowerShell: Copy-Item .env.example .env)
php artisan key:generate
```

**3. Database — MySQL.** Make sure your MySQL server is running (in Laragon: **Start All**), then create the schema:

```sql
CREATE DATABASE mokitan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or from the shell (Laragon's mysql binary lives at `C:\laragon\bin\mysql\<version>\bin\mysql.exe`):

```bash
mysql -u root -e "CREATE DATABASE mokitan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then set the connection in `.env` (Laragon defaults shown — adjust for your setup):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mokitan
DB_USERNAME=root
DB_PASSWORD=
```

Finally:

```bash
# 4. Run migrations + seeder (categories + test accounts)
php artisan migrate --seed

# 5. Make uploaded product images publicly served
php artisan storage:link

# 6. Compile assets
npm run build      # production build
# or, for hot reload during development:
# npm run dev
```

Prefer SQLite/Postgres instead? Edit `DB_CONNECTION` and matching `DB_*` values in `.env`, then run `migrate --seed` — no schema changes required.

### Importing an existing `.sql` dump instead

Create the `mokitan` database, import the dump (phpMyAdmin → Import, or `mysql -u root mokitan < dump.sql`), set `.env` as above, run `php artisan storage:link`, and you're done — no `migrate` needed.

### Start the app

```bash
php artisan serve        # then open http://127.0.0.1:8000
npm run dev              # optional, only for hot reload
```

---

## Test accounts

The seeder creates these (it is safe to re-run with `php artisan db:seed`; it never deletes anything):

| Role     | Email                | Password      |
| -------- | -------------------- | ------------- |
| Admin    | `admin@mokitan.com`  | `admin123`    |
| Customer | `budi@mokitan.com`   | `customer123` |
| Customer | `siti@mokitan.com`   | `customer123` |

- The seeder also gives **Siti** one demo order (once at least one product exists), so you can show that Budi gets a **403** on her invoice.
- New sign-ups through `/register` are **always customers**. The `role` column is deliberately not mass-assignable, so a request can never promote itself to admin — admins only come from the seeder.

---

## Who can do what

| Area                                           | Guest               | Customer            | Admin    |
| ---------------------------------------------- | ------------------- | ------------------- | -------- |
| Home, catalog, product pages                   | yes                 | yes                 | yes      |
| Add to cart, cart, checkout, Pesanan Saya      | redirected to login | yes                 | yes      |
| Own invoice / tracking (`/orders/{id}/...`)    | redirected to login | yes (only their own, others get **403**) | any invoice |
| Seller Center (`/admin/...`)                   | redirected to login | **403**             | yes      |

This is enforced by the `auth` middleware plus the `admin` middleware alias ([`AdminMiddleware`](app/Http/Middleware/AdminMiddleware.php), registered in `bootstrap/app.php`), not just by hiding buttons.

---

## How an order works

1. A logged-in customer adds products to the cart. Quantities can never exceed real stock (including what is already in the cart). A product with stock 0 shows **Stok Habis** and its buy button is disabled.
2. At `/checkout` they enter shipping details and pick a courier (JNE Reguler Rp 20.000, J&T Express Rp 18.000, SiCepat BEST Rp 15.000). The recipient name is pre-filled from their account.
3. **Place order** runs [`CheckoutService`](app/Services/CheckoutService.php) in one database transaction: it re-checks stock against locked rows, creates the order as **Pending** with the customer's `user_id`, decrements stock, and empties the cart. If stock is short, nothing is saved and the form shows the error with the input intact.
4. The customer lands on their invoice, and the order appears under **Pesanan Saya** (`/pesanan-saya`).
5. In the Seller Center the admin sets the order to **Paid**, then **Shipped** (a tracking number is required for Shipped). The status badge changes colour everywhere: Pending = yellow, Paid = blue, Shipped = green.

---

## Seller Center (admin)

Log in as the admin and use **Panel Seller Center** in the header (or go to `/admin`).

### Transactions — `/admin/transactions`

- Four live statistic cards: **Total Produk**, **Pesanan Pending**, **Pesanan Paid + Shipped**, **Total Omzet Bersih**.
- **Total Omzet Bersih only adds up Paid and Shipped orders.** Pending orders are never counted.
- Filter tabs: Semua / Pending / Paid / Shipped.
- Per order: change status, enter the tracking number (resi), open the customer's invoice, or **delete** it (with a confirmation). Deleting a *Pending* order returns its stock to the shelf; deleting a Paid/Shipped order does not.

### Products — `/admin/products`

1. **+ New product**: name, brand, **category**, price (whole rupiah, e.g. `430000`), **stock** (min 0), description, image (JPEG/PNG/WebP, up to 4 MB, stored in `storage/app/public/products/`), optional specifications and the *Featured* flag.
2. **Edit** pre-fills everything. Replacing an image deletes the old file; deleting a product deletes its image.
3. **Recommended add-ons** (on the edit page) link products together. Linked products appear on the public product page and feed its **Quick Add** modal, which sorts them by category: *I'm completely new* (kit + every linked tool, material and paint), *Restock on essentials* (kit + linked **Materials**) and *With the paints* (kit + linked **Paints**). Linked items in the **Other** category are never part of a preset; they show up as an *Optional accessories* checklist in the same modal. So give add-ons the right category. In the cart, everything quick-added sits inside its kit's line and expands like a folder.

---

## Storefront routes

| Route                       | What it does                                                         |
| --------------------------- | -------------------------------------------------------------------- |
| `/`                         | Home: featured product, "New in Stock", category tiles               |
| `/catalog`                  | Catalog with live search, category filter, brand filter, sort        |
| `/catalog/{category-slug}`  | Catalog scoped to one category                                       |
| `/detail/{id}`              | Product detail (real stock, 404 for an unknown id), Add to Cart, Quick Add, gift note |
| `/cart`                     | Cart (login required)                                                |
| `/checkout`                 | Shipping + courier, places a Pending order (login required)          |
| `/pesanan-saya`             | The signed-in customer's own orders, newest first                    |
| `/orders/{id}/success`      | Invoice (owner or admin only)                                        |
| `/orders/{id}/track`        | Status timeline Pending → Paid → Shipped (owner or admin only)       |
| `/login`, `/register`       | Auth pages                                                           |
| `/profile`                  | Profile edit                                                         |
| `/admin/...`                | Seller Center (admin only)                                           |

The header adapts to the visitor: guests see **Masuk / Daftar**, customers see **Halo, [Nama]** + **Pesanan Saya** + **Keluar**, admins see **Seller: [Nama]** + **Panel Seller Center** + **Keluar**. Logout is a `POST` form with `@csrf`.

---

## Design system

Tokens live in [`tailwind.config.js`](tailwind.config.js) and [`resources/css/app.css`](resources/css/app.css). Reuse them instead of hard-coding values:

| Token                    | Value                 | Usage                                  |
| ------------------------ | --------------------- | -------------------------------------- |
| `brand`                  | `#bc002d`             | Primary accent (buttons, underlines)   |
| `brand-hover`            | `#a30028`             | Hover state for brand surfaces         |
| `ink` / `ink-alt`        | `#000000` / `#16161d` | Text, deep surfaces                    |
| `surface`                | `#d9d9d9`             | Placeholder / neutral surfaces         |
| `canvas`                 | `#ffffff`             | Page background                        |
| `status-pending-*`       | `#fff3cd` / `#856404` | Pending badge (yellow)                 |
| `status-paid-*`          | `#cfe2ff` / `#084298` | Paid badge (blue), `btn-info`          |
| `status-shipped-*`       | `#d1e7dd` / `#0f5132` | Shipped badge (green), `btn-success`   |

Buttons: `btn-primary` (brand), `btn-success`, `btn-info`, `btn-warning`, `btn-danger`, `btn-outline`, `btn-secondary`, plus `btn-sm`.

**Signature shape:** `.shape-card` gives `border-radius: 10px` on three corners and leaves the **top-right corner square**. Keep using it on new containers and buttons.

**Font:** Instrument Sans (Google Fonts, loaded in `app.css`).

> Tailwind scans `app/Enums` too, because the status badge classes live in the `OrderStatus` enum.

---

## Project structure

Notable paths (everything else is stock Laravel):

```
app/
├── Enums/
│   ├── OrderStatus.php                # Pending / Paid / Shipped + badge classes + revenue statuses
│   └── Courier.php                    # Courier names + flat shipping fees
├── Http/
│   ├── Middleware/AdminMiddleware.php # 403 unless the user is an admin
│   └── Controllers/
│       ├── HomeController.php, ProductController.php, CartController.php, CheckoutController.php
│       ├── OrderController.php        # Pesanan Saya, invoice, tracking (+ ownership check)
│       └── Admin/                     # ProductController (CRUD + image cleanup), TransactionController
├── Models/                            # User (isAdmin), Category, Product, Cart, CartItem, Order, OrderItem, Address
├── Services/CheckoutService.php       # Cart → Pending order, stock check + decrement, in one transaction
└── Support/Money.php                  # Money::idr() — formats "Rp 430.000"

database/
├── migrations/                        # add_role_to_users, add_courier_to_orders are the newest
├── factories/                         # incl. OrderFactory (paid()/shipped() states), UserFactory::admin()
└── seeders/DatabaseSeeder.php         # Idempotent: admin + 2 customers + categories + a demo order

resources/views/
├── components/layouts/                # store.blade.php, admin.blade.php
├── components/store/                  # header, footer, product-card, breadcrumb, status-badge, ...
├── components/{header,catalog,product,cart,checkout,admin}/  # Livewire single-file components (⚡*.blade.php)
├── errors/                            # 403 and 404 pages
├── orders/, products/, catalog/, cart/, checkout/, profile/, auth/
└── admin/{products,transactions}/

tests/Feature/                         # AccessControlTest, CheckoutTest, AdminTransactionTest (+ Breeze tests)
routes/web.php                         # public / auth / auth+admin route groups
```

**Livewire 4 note:** components are single-file — the PHP class and Blade template live together in `⚡name.blade.php` under `resources/views/components/<group>/`. Reference them as `<livewire:group.name />`.

---

## Useful commands

```bash
php artisan test --compact       # run the whole suite (in-memory SQLite, never touches MySQL)
php artisan route:list           # inspect routes + middleware
php artisan db:seed              # safe to re-run: (re)creates accounts + categories, deletes nothing
php artisan optimize:clear       # clear route/config/view caches
php artisan storage:link         # (rerun if you delete public/storage)

npm run dev                      # Vite dev server with hot reload
npm run build                    # production asset build

vendor/bin/pint --dirty          # format changed PHP with Laravel Pint
```

> **Do not run `php artisan migrate:fresh` once you have real products and orders** — it drops every table. Export a backup first (`mysqldump -u root mokitan > backup.sql`) and add new migrations instead of editing old ones.

---

## Troubleshooting

- **"Unable to locate file in Vite manifest"** — run `npm run build` (or start `npm run dev`).
- **"No application encryption key has been specified."** — run `php artisan key:generate`.
- **Uploaded images return 404** — you skipped `php artisan storage:link`.
- **A status badge or button has no colour** — rebuild assets (`npm run build`); Tailwind only generates classes it finds while scanning.
- **I get a 403 on `/admin/...`** — you are signed in as a customer. Log in as `admin@mokitan.com`. If the admin was created some other way, check `users.role = 'admin'`.
- **Admin from the seeder can't log in** — run `php artisan db:seed` (it also resets the seeded passwords).
- **`SQLSTATE[HY000] [2002] Can't connect to MySQL`** — MySQL isn't running. Start it via Laragon (**Start All**) or your service manager.
- **`SQLSTATE[HY000] [1049] Unknown database 'mokitan'`** — you skipped the `CREATE DATABASE mokitan` step in setup.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
