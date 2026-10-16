# Tripdesk — Travel Agency SaaS

Multi-tenant booking, invoicing, profit/loss and payments system for travel agencies (Umrah / tour packages).
Built with **Laravel 13 · Blade · Tailwind 4 · Alpine.js · MySQL**. UI follows the Figma file
*Tripdesk — Travel Agency SaaS*.

## What it does

| Area | Details |
|---|---|
| **SaaS / tenants** | `/register` creates a new agency (parent company) + first sub-brand + admin, 14-day trial. Every table is scoped to the company automatically (`BelongsToCompany`). |
| **Hierarchy** | Parent company (Admin) → Sub-brands (Director) → Teams (Team lead) → Agents. Plus an **Accounts** role. |
| **Who sees what** | Admin & Accounts: everything · Director: own brand · Team lead: own team · Agent: only own bookings (`Booking::scopeVisibleTo`). |
| **Booking wizard** | 1 Passengers → 2 Flight (PNR + editable itinerary) & hotels → 3 Transport, visa, customer pricing → 4 Profit / loss → 5 Payments. Auto-saves a draft from the first click; every section has “Add more”. |
| **Pricing** | Per pax type (2 Adult × £100 + 3 Child × £70 = **£410**). Live totals. |
| **Profit / loss** | Supplier costs for flights, hotels, transport, visa, other — pre-filled from steps 1–3. Margin & %. Internal only. |
| **Invoice** | Branded per sub-brand (logo colour, address, bank details, terms). View / print / save as PDF, **send by email**, status Accepted / Waiting / Rejected. |
| **Payments** | Installments with date, reference, mode, amount and screenshot proof (private storage). Total paid / pending update automatically. |
| **Reports** | Dashboard KPIs + 12-month sales vs purchase, Payments overview, P&L by agent or brand. |
| **Admin** | Team & roles (hierarchy tree, permission matrix, add / edit / disable members, teams), Sub-brands, Suppliers, Settings (currency, booking prefix). |

## Demo logins (after `db:seed`) — password `password`

| Role | Email |
|---|---|
| Admin (parent company) | admin@demo.test |
| Director · Al-Noor Travel | director@demo.test |
| Team lead · Team Ahmed | lead@demo.test |
| Agent · Sara Khan | agent@demo.test |
| Accounts | accounts@demo.test |

Booking **TD-10482** is the exact example from the hand-drawn spec (£410 sale, £330 purchase, £80 margin, £150 paid).

## Run locally

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

## Deploy on the VPS (Docker)

Requirements: Docker + Docker Compose plugin, a domain whose A record points to the VPS IP.

```bash
# on the VPS
git clone <your-repo> tripdesk && cd tripdesk      # or upload & unzip
cp .env.production.example .env
nano .env                                         # set APP_URL, APP_DOMAIN, DB passwords, SMTP
sed -i "s|^APP_KEY=.*|APP_KEY=base64:$(openssl rand -base64 32)|" .env
```

**A. Nothing else on ports 80/443** → Caddy gets the SSL certificate automatically:

```bash
docker compose --profile caddy up -d
```

**B. nginx already serves other sites** → app listens on `127.0.0.1:8080`:

```bash
docker compose up -d
sudo cp deploy/nginx-site.conf /etc/nginx/sites-available/tripdesk   # edit server_name
sudo ln -s /etc/nginx/sites-available/tripdesk /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d app.example.com
```

Migrations and config/route/view caching run automatically on every container start.
Open `https://your-domain/register` to create the first agency, or load the demo data:

```bash
docker compose exec app php artisan db:seed --force
```

**Update after code changes:** `git pull && docker compose up -d --build`
**Backups:** `docker compose exec mysql mysqldump -u root -p"$DB_ROOT_PASSWORD" tripdesk > backup.sql` and the `storage` volume (payment proofs).

## Code map

- `app/Models/Booking.php` — totals, margin, pending, hierarchy scope
- `app/Models/User.php` — roles + permission matrix
- `app/Models/Concerns/BelongsToCompany.php` — tenant isolation
- `app/Http/Controllers/BookingController.php` — 5-step wizard, invoice, email, status
- `app/Http/Controllers/PaymentController.php` — installments + private proof files
- `resources/views/bookings/step1…5.blade.php` — wizard screens
- `resources/views/bookings/invoice.blade.php` — printable + email invoice
- `resources/js/app.js` — Alpine `repeater` used by every “Add more” section
