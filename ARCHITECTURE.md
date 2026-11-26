# Architecture

Reflexology Manager is a Laravel application for running branch operations at reflexology and wellness centers. It connects bookings, room and therapist assignment, point-of-sale (POS) transactions, inventory, purchasing, finance, attendance, and reporting. This repository contains the admin web application and the customer-facing API, not the mobile app itself.

## Application map

| Area | Entry points |
| --- | --- |
| Routing | [Application bootstrap](bootstrap/app.php) registers browser routes, `/api` routes, and the console schedule. Admin routes are split by module in [`routes/admin`](routes/admin). |
| Admin web UI | [Admin controllers](app/Http/Controllers/Admin) render [Blade views](resources/views) and serve JSON to interactive screens. The [shared layout](resources/views/layouts/admin.blade.php) loads compiled OneUI/Bootstrap assets, and screens such as the [room monitor](resources/views/admin/monitor_room/index.blade.php) load Vue 2 and Axios directly from [`public/js`](public/js). This is not a single-page app. [`vite.config.js`](vite.config.js) defines a separate asset build. |
| Customer API | [`routes/api/user.php`](routes/api/user.php) and [`routes/api/cart.php`](routes/api/cart.php) cover customer accounts, bookings, vouchers, cart, and notifications. Protected mobile routes use Passport; browser staff sessions use a separate admin guard. |
| Database | [Eloquent models](app/Models) map to a MySQL-compatible schema. The [baseline migration](database/migrations/2026_08_24_000000_create_ref_manager_schema.php) and [SQL dump](database/schema/mysql-schema.sql) describe the starting schema. Yajra DataTables handles server-side results for many admin lists. |
| Shared logic and jobs | [`app/Libs`](app/Libs) contains helpers for stock, journals, payments, and notifications. [`app/Jobs`](app/Jobs) contains reminders, payment-expiry processing, monitor updates, room and therapist availability, commissions, and import work. |

[`composer.json`](composer.json) requires Laravel 13 and PHP `^8.3`. [`composer.lock`](composer.lock) records the PHP dependency versions; [`package.json`](package.json) declares the frontend build dependencies.

## Follow a transaction

- **Scheduled bookings:** [`BookingController`](app/Http/Controllers/Admin/BookingController.php) stores the booking header and details and handles payment proof, assignment, cancellation, and finalization. Assigning a treatment creates an ongoing POS transaction for that detail. Finalization instead creates a completed transaction with an invoice and payment record.
- **Live POS:** [`MonitorRoomController`](app/Http/Controllers/Admin/MonitorRoomController.php) starts treatments, updates room and therapist assignments, and records payment for ongoing transactions. Its separate checkout action creates a new completed sale. Depending on the flow, POS changes can also affect stock, voucher use, member points, and accounting journals.
- **Background work:** [`routes/console.php`](routes/console.php) schedules jobs. The room-monitor payment flow also queues delayed room and therapist status changes. A scheduler and queue worker play different roles: the scheduler dispatches due jobs; the worker processes jobs on the configured queue.
- **External services:** The API includes a DOKU payment webhook. Firebase Cloud Messaging (FCM) sends customer notifications. Treat both as integration boundaries: stored business state and external delivery can fail independently.

See [Operations](OPERATIONS.md) for deployment and recovery and [Security](SECURITY.md) for access and data-handling boundaries.
