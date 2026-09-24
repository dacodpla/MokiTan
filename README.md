# Moki-Tan

A full-stack e-commerce storefront for model kits, paints, tools, materials, and accessories. Built as a demo/portfolio app with a mocked payment flow so both the success and failure branches of the user journey can be exercised without any external accounts.

- **Framework:** Laravel 13 (PHP 8.3)
- **Frontend:** Tailwind CSS 3 + Livewire 4 (single-file components) + Alpine.js
- **Auth:** Laravel Breeze (Blade)
- **Database:** MySQL 8 by default (any Laravel-supported driver works)
- **Payment:** Fake gateway with a deterministic failure path

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
# 4. Run migrations + seeder (5 categories + test user)
php artisan migrate --seed

# 5. Make uploaded product images publicly served
php artisan storage:link

# 6. Compile assets
npm run build      # production build
# or, for hot reload during development:
# npm run dev
```

Prefer SQLite/Postgres instead? Edit `DB_CONNECTION` and matching `DB_*` values in `.env`, then run `migrate --seed` — no schema changes required.

### Start the app

In one terminal:

```bash
php artisan serve
```

In another (only if you want hot reload):

```bash
npm run dev
```

Open <http://127.0.0.1:8000>.

### Default credentials

The seeder creates one user for testing:

| Email                | Password   |
| -------------------- | ---------- |
| `test@example.com`   | `password` |

Any logged-in user can also reach the admin panel — there's no role table yet.

---

## What ships in the seed

The database starts **empty of products**. Only the five categories used by the header nav are seeded:

- Kits
- Tools
- Materials
- Paints
- Other

Add your own products through the admin panel (below).

---

## Admin panel

Once logged in, click **Admin** in the site header (or go straight to `/admin`).

### Add a product

1. `/admin/products` → **+ New product**
2. Fill in name, brand, category, price (whole rupiah — e.g. `430000` for Rp 430,000), stock
3. Optionally upload a product image (JPEG/PNG/WebP, ≤ 4 MB — stored under `storage/app/public/products/`)
4. Optionally add specifications as key/value pairs (e.g. `scale` → `1/48`, `pieces` → `214`)
5. Tick **Featured on homepage** to promote it into the hero slot
6. **Create product** — you'll land on the edit page, where you can also link add-ons

### Link "Quick Add" bundles

On the product edit page, the **Recommended add-ons** section lists every other product with a live-syncing checkbox:

- Ticking a checkbox immediately writes to the `product_recommendations` pivot
- The linked products appear on the public product page as "Recommended tools & materials"
- They also become the bundle options inside the **Quick Add** modal for that product

There's a search + category filter to make picking easy when the catalog grows.

### Edit / delete

- **Edit** — same form as create, plus the recommendations panel
- **Delete** — button in the products list; asks for confirmation

---

## Storefront tour

| Route                        | What it does                                                                 |
| ---------------------------- | ----------------------------------------------------------------------------- |
| `/`                          | Home: featured product hero, "New in Stock" grid, "Show me more!", categories |
| `/catalog`                   | Full catalog with live search, category filter, brand filter, sort            |
| `/catalog/{category-slug}`   | Catalog scoped to one category (e.g. `/catalog/kits`)                         |
| `/products/{product-slug}`   | Product detail, Add to Cart, Quick Add bundle, gift-note toggle              |
| `/cart`                      | Cart page with qty +/-, remove, per-item gift wrap toggle                    |
| `/checkout`                  | Shipping form → payment modal                                                 |
| `/orders/{id}/success`       | Success screen with tracking number                                          |
| `/orders/{id}/track`         | Status timeline (Pending → Paid → Shipped → Delivered)                       |
| `/login`, `/register`        | Breeze auth pages, restyled                                                  |
| `/profile`                   | Profile edit + order history                                                 |
| `/admin/*`                   | Admin panel (auth-guarded)                                                   |

---

## Payment demo

The [`FakePaymentGateway`](app/Services/FakePaymentGateway.php) is bound in [`AppServiceProvider`](app/Providers/AppServiceProvider.php). No external accounts are needed. In the payment modal:

- Enter any card number **12–19 digits**
- Expiration in `MM/YY` format
- CVC = 3 digits

**Behaviour:**

| Card ends in | Result                                                          |
| ------------ | ---------------------------------------------------------------- |
| `0000`       | Fails — shows the "Gagal — Coba Lagi" banner, cart preserved    |
| anything else| Succeeds — order created, cart emptied, redirected to success   |

Try `4242 4242 4242 4242` for a success and `4242 4242 4242 0000` for the failure path.

---

## Design system

Tokens are defined in [`tailwind.config.js`](tailwind.config.js) and [`resources/css/app.css`](resources/css/app.css). Reuse them instead of hard-coding values:

| Token         | Value        | Usage                                        |
| ------------- | ------------ | --------------------------------------------- |
| `brand`       | `#bc002d`    | Primary accent (buttons, headings underline) |
| `brand-hover` | `#a30028`    | Hover state for brand-red surfaces           |
| `ink`         | `#000000`    | Primary text                                 |
| `ink-alt`     | `#16161d`    | Softer black (footers, deep surfaces)        |
| `surface`     | `#d9d9d9`    | Placeholder / neutral surfaces               |
| `canvas`      | `#ffffff`    | Page background                              |

**Signature shape:** the `.shape-card` utility sets `border-radius: 10px` on three corners and leaves the **top-right corner square**. Used on product cards, category tiles, the "Show me more!" button, buttons, and the featured image — this is Moki-Tan's asymmetric card motif. Keep applying it to any new container.

**Font:** Instrument Sans (loaded from Google Fonts in `app.css`). Type utilities: `.type-display`, `.type-h1`…`.type-h5`, `.type-body`.

---

## Project structure

Notable paths (everything else is stock Laravel):

```
app/
├── Enums/OrderStatus.php              # Pending → Paid → Shipped → Delivered (+ Failed)
├── Http/Controllers/
│   ├── HomeController.php             # Storefront home
│   ├── ProductController.php          # Product detail
│   ├── CartController.php             # Cart page shell (Livewire inside)
│   ├── CheckoutController.php         # Checkout gate (redirects if cart empty)
│   ├── OrderController.php            # Order success + tracking
│   └── Admin/ProductController.php    # Full admin CRUD
├── Models/                            # Category, Product, Cart, CartItem, Order, OrderItem, Address
├── Services/
│   ├── PaymentGateway.php             # Interface
│   ├── PaymentResult.php              # DTO
│   └── FakePaymentGateway.php         # Deterministic mock (fails on cards ending 0000)
└── Support/Money.php                   # Money::idr() — formats "Rp 430.000"

resources/views/
├── components/
│   ├── layouts/
│   │   ├── store.blade.php            # Storefront layout wrapper
│   │   └── admin.blade.php            # Admin layout wrapper
│   ├── store/                         # header, footer, product-card, category-tile, section-heading
│   ├── header/⚡cart-icon.blade.php   # Livewire SFC — reactive cart badge
│   ├── catalog/⚡browser.blade.php    # Livewire SFC — filters / search / pagination
│   ├── product/                       # ⚡add-to-cart, ⚡quick-add, ⚡gift-toggle (Livewire SFCs)
│   ├── cart/⚡page.blade.php          # Livewire SFC — cart with qty & remove
│   ├── checkout/                      # ⚡personal-data, ⚡payment (Livewire SFCs)
│   └── admin/⚡recommendations.blade.php  # Livewire SFC — link products live
├── home.blade.php, catalog/, products/, cart/, checkout/, orders/
└── admin/products/                    # index, create, edit, _form partial

public/img/
├── logo/                              # MokiTan.svg (dark) + MokiTanLT.svg (light)
├── categories/                        # Background SVGs per category slug
└── products/                          # (Optional) fallback placeholders

routes/web.php                          # All routes (storefront + admin + Breeze auth)
```

**Livewire 4 note:** components are single-file — PHP class + Blade template together in one `⚡name.blade.php` file under `resources/views/components/<group>/`. Reference them from other views as `<livewire:group.name />`.

---

## Useful commands

```bash
php artisan test                 # run the PHPUnit test suite
php artisan route:list           # inspect all routes
php artisan migrate:fresh --seed # wipe DB and reseed the 5 categories + test user
php artisan storage:link         # (rerun if you delete public/storage)

npm run dev                      # Vite dev server with hot reload
npm run build                    # production asset build

vendor/bin/pint --dirty          # format changed PHP with Laravel Pint
```

---

## Troubleshooting

- **"Unable to locate file in Vite manifest"** — run `npm run build` (or start `npm run dev`).
- **"No application encryption key has been specified."** — run `php artisan key:generate`.
- **Uploaded images return 404** — you skipped `php artisan storage:link`.
- **Login redirects to `/dashboard` and 500s** — the Breeze `AuthenticatedSessionController` has been changed to redirect to `home` in this app; if you re-published Breeze, re-apply that edit.
- **Cart items pointing at deleted products** — the pivot cascades on delete; if you get stale data, `php artisan migrate:fresh --seed`.
- **`SQLSTATE[HY000] [2002] Can't connect to MySQL`** — MySQL isn't running. Start it via Laragon (**Start All**) or your service manager. Verify with `mysql -u root -e "SELECT VERSION();"`.
- **`SQLSTATE[HY000] [1049] Unknown database 'mokitan'`** — you skipped the `CREATE DATABASE mokitan` step in setup.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
