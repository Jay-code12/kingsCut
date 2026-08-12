# King's Cut Saloon — Customer Portal & Dashboard (PHP OOP)

A working PHP (OOP, no framework) implementation of the public site and the
Customer Dashboard described in the Salon Membership Management System MVP
PRD: Home, Services, Membership & Plans, Contact, plus a logged-in dashboard
with Overview, Wallet, Attendance, Family & Guest IDs, and Payments.

This is the **customer-facing** half of the system. The Admin / Super Admin
console is a separate build.

## Requirements

- PHP 8.1+ with extensions: `pdo_mysql`, `mbstring`
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` (an `.htaccess` is included), or any server that
  can route all requests through `index.php`

## Setup

1. **Import the database**
   ```bash
   mysql -u root -p < sql/kings_cut_saloon.sql
   ```
   This creates the `kings_cut_saloon` database, all tables, and seed data
   (a demo customer, an admin + super admin, 4 plans, 9 services, and one
   active subscription with sample wallet/attendance/payment history).

2. **Configure the database connection**
   Edit `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'kings_cut_saloon');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
   If the app lives in a sub-folder (e.g. `https://yoursite.com/saloon/`),
   set `define('BASE_PATH', '/saloon');` as well.

3. **Point your web server at this folder** (Apache + `.htaccess` works out
   of the box), or run it locally with PHP's built-in server:
   ```bash
   php -S localhost:8000 index.php
   ```
   then visit `http://localhost:8000/`.

4. **Make sure `assets/uploads/work/` is writable** by whatever user your
   web server runs as — that's where Admin's "Our Work" image uploads get
   saved. It ships with two sample images already in it, so if those show
   up fine on `/work` but a new upload fails with a permissions error,
   this folder is what to check (`chmod 755` is usually enough; the app
   never needs `777`).

## Demo login

**Customer portal** (`/login`)

| Email                        | Password      |
|-------------------------------|--------------|
| alex.morgan@example.com       | password123  |

This account already has an active **Single — Yearly** subscription
(`KC-0417-SG`) *and* an active **Couple — Monthly** subscription
(`KC-0592-CP`) — so you can see the plan filter/switcher in action
immediately — plus a ₦18,400 wallet balance, attendance history across
both plans, secondary IDs, and payment history.

**Admin console** (`/admin/login`)

| Role        | Email                          | Password      |
|-------------|----------------------------------|--------------|
| Admin       | tunde@kingscutsaloon.com        | password123  |
| Super Admin | samuel@kingscutsaloon.com       | password123  |

You can also register a brand-new account from `/register` and subscribe to
any plan from `/membership` to see the full signup → subscribe → dashboard
flow.

## What's implemented

- **Public pages**: Home, Services (grouped by category), Membership & Plans
  (with a live Monthly/3-Month/6-Month/Yearly price toggle), Contact (saves
  to `contact_messages`)
- **Auth**: register, login, logout — sessions, `password_hash`/
  `password_verify`, CSRF-protected forms
- **Membership subscribe**: simulates the PRD's "online payment activates
  the subscription automatically" — generates a unique Membership ID
  (e.g. `KC-0417-SG`) and QR token, and logs a payment record
- **Multiple plans per customer**: a customer can own more than one active
  subscription at once (the demo account does — a Single and a Couple
  plan). Wallet, Attendance, Family & Guest IDs, and Payments all have a
  **Plan filter** dropdown so you can view one plan at a time or "All
  Plans" combined; Overview has a plan **switcher** since the ticket/visit
  stats shown are always for one plan at a time (wallet balance is shared
  across every plan).
- **Real QR codes with a share popup**: every secondary/guest ID (and the
  primary ticket) has a "Share QR" / "View / Share" button that opens a
  modal with:
  - A real, scannable QR code (rendered client-side via
    [qrcodejs](https://github.com/davidshimjs/qrcodejs), loaded from cdnjs)
    encoding a public link back to that ID
  - Copy Link, WhatsApp, X/Twitter, Facebook, and native share-sheet
    buttons (native share auto-hides on desktop browsers without
    `navigator.share`)
  - A "send to a guest's email" field
  - Every share action is logged to `id_shares` (who shared what, via
    which channel, to whom) for a lightweight audit trail
  - The shared link opens `/id/{token}` — a public, unauthenticated page
    that shows only the label, code, plan, status, and QR (no wallet
    balance, email, or phone number)
- **Customer Dashboard**:
  - **Overview** — plan switcher (if you have more than one), wallet
    balance, visits this month, active secondary ID count, membership
    ticket with Share QR, weekly attendance strip
  - **Wallet** — balance (shared across all plans), a demo top-up (no real
    payment gateway wired up), transaction history filterable by plan
  - **Attendance** — monthly calendar strip + check-in history, filterable
    by plan
  - **Family & Guest IDs** — generate Temporary/Permanent secondary IDs for
    a specific plan (enforced against that plan's `max_secondary_ids`
    limit), filterable list, View/Share QR, revoke; ownership is checked
    server-side against every plan the customer owns, not just one
  - **Payments** — subscription + service payment ledger, filterable by
    plan

## Business rules enforced in code

- Wallet balance can never go negative (`Wallet::debit` checks the balance
  before deducting, inside a transaction)
- Secondary ID generation is capped at the plan's `max_secondary_ids`,
  checked against the specific plan it's being added to
- A secondary ID can only be revoked (or shared) by a subscription the
  logged-in customer actually owns — checked against *all* of their
  subscriptions, not just one
- Every subscription gets its own unique Membership ID + QR token, matching
  the PRD's "users can own multiple subscriptions, each with a unique
  Membership ID and QR"
- The public `/id/{token}` share page never exposes wallet balance, email,
  or phone number — only what's safe for a guest to see
- A reservation's `customer_id` link is taken only from the server-side
  session, never from submitted form data — a guest can't claim to be a
  logged-in customer just by adding a hidden field
- A reservation's total is recalculated from the database (current session
  pricing + current service prices) on submit — the live estimate shown in
  the browser is for the customer's benefit only and is never trusted
- Uploaded work-gallery images are validated by their actual file content
  (`finfo_file`), not the filename extension or browser-supplied MIME
  type, before being accepted
- A service category can't be deleted while it still has services in it,
  so the public Services page can't end up with orphaned entries

## Enabling outgoing email (optional)

The "send to a guest's email" feature calls PHP's `mail()`. Most local dev
setups (including a stock XAMPP install) have no mail transfer agent
configured, so it will log the share but tell you the email wasn't actually
delivered. To make it send for real on XAMPP:

- **Windows**: install/enable **Mercury Mail** (bundled with older XAMPP)
  or point `php.ini`'s `[mail function]` section at an SMTP relay (e.g. a
  Gmail app password) using a helper like `sendmail.exe`
  ([fakemail/sendmail for Windows](https://github.com/mailhog/mhsendmail)
  is a common approach)
- **Linux/Mac**: install and configure `sendmail` or `msmtp`, and set
  `sendmail_path` in `php.ini` accordingly

This isn't required for anything else in the app to work — every share/email
action tells you plainly whether it actually sent.

## Admin Console

Reachable at `/admin/login` — **completely separate login from the customer
portal** (different session, different table, different auth class). Two
roles, matching the PRD:

**Admin** (`tunde@kingscutsaloon.com` / `password123`) — day-to-day operations:
- **Sales Overview**
- **Reservations** — see every booking request (session, location, headcount,
  services picked, contact info, estimated total), filter by status, and
  mark each one Pending / Confirmed / Cancelled with an optional note
- **Our Work** — upload gallery photos or add YouTube links, delete items

**Super Admin** (`samuel@kingscutsaloon.com` / `password123`) — everything
Admin has, plus catalog and pricing control:
- **Membership Plans** — edit each plan's name, tagline, discount %, max
  secondary IDs, and per-duration price; optionally set a strike-through
  "was" price per duration (only accepted if it's actually higher than
  the real price — otherwise it's silently ignored rather than showing a
  nonsense "discount"). Add brand-new plans too.
- **Services & Categories** — add/edit/delete categories (a category with
  services in it can't be deleted, to avoid orphaning services) and
  services, including the same optional strike-through price.
- **Booking Sessions** — set the base fee and per-person rate for each of
  the 8 session combinations (Morning/Afternoon/Evening/Whole Day ×
  VIP Office/VIP Outside), and toggle any combination off if it's not
  bookable right now — deactivated combinations disappear from the
  public Reserve page immediately.
- Every change here is **live immediately** on the public Services,
  Membership, and Reserve pages — same tables, same models.

### Sales Overview

Revenue analytics grouped by **Hour** (today), **Day** (last 30), **Week**
(last 12), **Month** (last 12), or **Year** (last 5) — a Chart.js bar chart
plus KPI cards (today / this month / this year / all-time / active
members) and a revenue-by-plan breakdown. Backed by `SalesReport`, which
sums the `payments` table (status = paid) and fills in zero-value gaps so
the chart never has a broken axis.

## Reservations (VIP session booking)

`/reserve` — a public booking form (works for guests and logged-in
customers, who get their name/email/phone pre-filled):
- Choose a session: **Morning / Afternoon / Evening / Whole Day**, each
  available as **VIP Office** or **VIP Outside** — pricing (base fee +
  per-person rate) is pulled live from what Super Admin set
- Number of people, and a date (today or later)
- Select any number of services from the full catalog
- Contact info: full name, phone, email, optional notes
- A running estimate updates client-side as selections change; the
  authoritative total is recalculated server-side from the database
  (never trusts the number shown in the browser)

The submission is saved as `pending` and shows up immediately in the
Admin Console's **Reservations** page for follow-up — nothing here
auto-confirms or charges anything; it's a request the front desk
actions manually, matching the "we'll contact you" flow described.

## Our Work (image + YouTube gallery)

Public page at `/work`, plus a 4-item preview strip on the homepage.
Super Admin/Admin can:
- **Upload images** (JPG/PNG/WEBP/GIF, 5MB max) — validated by actually
  inspecting the file's real MIME type (`finfo`), not just trusting the
  extension or the browser-supplied content type, so a disguised file
  (e.g. a `.php` file renamed to `.jpg`) is rejected before it ever
  touches disk
- **Add YouTube videos** by pasting any standard YouTube URL (watch
  links, `youtu.be` short links, or embed links, with or without extra
  query params) — the video ID is extracted and embedded responsively
- **Delete** either type; deleting an image also removes the file from
  disk, not just the database row

## Password reset via OTP

`/forgot-password` → enter an email → if an account exists, a 6-digit code
is emailed (via the same `Mailer` used for receipts/welcome emails) and the
customer lands on `/reset-password`. Security details:
- The code is hashed (SHA-256) before storage — the plain code only ever
  exists in the email itself
- Expires after 10 minutes
- Locks after 5 wrong attempts (`password_resets.attempts`)
- Single-use — generating a new code invalidates any earlier unused one
  for that account
- The "which email" question is never answered directly (same message
  whether the account exists or not), so the flow can't be used to find
  out who has an account

## Transactional emails

Three branded HTML email templates (table-based layout, inline CSS —
built to survive real-world email clients, not just a browser), all
rendered through the shared `Mailer` shell:
- **Welcome** — sent right after registration
- **OTP code** — sent from the password reset flow above
- **Payment receipt** — sent after a membership purchase (wired into
  `MembershipController::subscribe()`; reusable anywhere else a payment
  is recorded)

Like the QR-share email, these use PHP's `mail()` and will report (not
fail silently) if there's no mail transport configured locally — see
"Enabling outgoing email" above.

## Project structure

```
config.php                 Bootstrap: DB config, session, autoloader, helpers
index.php                  Front controller / routes
.htaccess                  Clean-URL rewriting for Apache

app/Core/                  Database, Router, Auth, AdminAuth, View, Mailer
app/Models/                Customer, Admin, Plan, Subscription, Wallet,
                            ServiceCatalog, SecondaryId, Attendance, Payment,
                            Share, PasswordReset, SalesReport, SessionPricing,
                            Reservation, WorkItem
app/Controllers/           Home, Service, Membership, Contact, Auth, Dashboard,
                            Public (QR share pages), Reservation, Work
app/Controllers/Admin/     AdminAuth, AdminDashboard, AdminPlan, AdminService,
                            AdminSession, AdminReservation, AdminWork

views/                     PHP templates (plain, no template engine)
views/layout/              Shared header/footer for site, auth, dashboard, admin
views/auth/                Login, register, forgot-password, reset-password
views/dashboard/           Overview, wallet, attendance, family, payments
views/admin/               Admin login, sales dashboard, plans, services,
                            sessions, reservations, work
views/reserve.php          Public booking form
views/work.php             Public work gallery
views/emails/              (Mailer builds emails inline — see app/Core/Mailer.php)

assets/css/style.css       Shared brand styling (espresso/brass/burgundy)
assets/uploads/work/       Uploaded gallery images live here (writable)
sql/kings_cut_saloon.sql   Full schema + seed data (see below)
```

## About the SQL file

`sql/kings_cut_saloon.sql` creates the entire schema in one import,
including `admins`, `authorization_codes`, and `audit_log` for future admin
features (like the attendance check-in scanner and per-member billing shown
in the earlier UI/UX mockups, which aren't wired up yet — this build covers
Sales Overview, Plans, and Services management specifically). The seed data
spreads sample payments across the last ~30 months using relative
`DATE_SUB(NOW(), ...)` expressions specifically so the Sales Overview chart
has realistic data in every view (Hour/Day/Week/Month/Year) regardless of
when you import it.
