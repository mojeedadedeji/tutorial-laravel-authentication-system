# 5050 Logistics Limited

A full-stack shipment management platform for **5050 Logistics Limited** — a logistics company moving freight across Nigeria and beyond. Built with Laravel 13, PHP 8.4, MySQL, and Bootstrap 5.

**Live:** https://fiftyfiftylogistics.com

## Features

### Public site
- **Landing page** — services, coverage, testimonials, FAQ, WhatsApp chat button
- **Get a quote** — instant freight estimate calculator
- **Track shipment** — public tracking by reference number (no login required)
- **Blog** — published posts with public index and detail pages

### Customer dashboard (authenticated)
- **Dashboard** — KPI cards, shipment charts, recent activity
- **Shipments** — create, view, edit, delete, update status, export CSV, printable invoices
- **Payment methods** — save cards (Visa/Mastercard) and wallets (Paystack, OPay)
- **Pay invoice** — pay for a shipment using a saved payment method
- **Profile & password management**, account deletion

### Admin panel (`/admin`, admin users only)
- **Dashboard** — platform-wide stats (users, shipments, revenue, fleet, posts)
- **All shipments** — search/filter, update any customer's shipment status
- **User management** — search users, grant/revoke admin
- **Fleet management** — vehicle CRUD (type, plate, driver, status), assign to shipments
- **Blog CMS** — create/edit/publish posts

## Tech stack

- **Backend:** Laravel 13, PHP 8.4, Eloquent ORM
- **Database:** MySQL 8 (SQLite for tests)
- **Frontend:** Blade templates, Bootstrap 5.3, Chart.js
- **Testing:** PHPUnit feature tests (81 tests)

## Getting started

### Prerequisites
- PHP 8.4+
- Composer
- MySQL 8 (or MAMP)

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then:

```bash
php artisan migrate --seed
php artisan serve
```

Visit `http://127.0.0.1:8000`.

### Local network access

To open the site from another device on the same Wi-Fi:

```bash
php artisan serve --host=0.0.0.0 --port=8080
```

Then visit `http://<your-mac-ip>:8080` on the other device.

## Testing

```bash
php artisan test
```

## Project structure highlights

```
app/
├── Http/Controllers/     # Auth, Dashboard, Shipment, Tracking, Quote,
│                         # PaymentMethod, Payment, Admin, Vehicle, Post
├── Models/               # User, Shipment, PaymentMethod, Payment,
│                         # Vehicle, Post
└── Notifications/        # ShipmentStatusUpdated
resources/views/
├── welcome.blade.php     # Public landing page
├── shipments/            # Customer shipment views
├── admin/                # Admin panel views
└── blog/                 # Public blog views
database/
├── migrations/           # Schema incl. vehicles, posts, payments, is_admin
└── seeders/              # Demo data
```

## Payment methods

Payment methods support three types: **card** (Visa, Mastercard), **paystack**, and **opay**. Invoice payment currently records a simulated payment — real Paystack/OPay gateway charging requires API keys.

## License

This project is proprietary software for 5050 Logistics Limited.
