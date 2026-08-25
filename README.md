<div align="center">
  <img src="./public/images/logo-only.png" alt="Reflexology Manager logo" width="140">
  <h1>Reflexology Manager</h1>
  <p>A Laravel-based operations platform for reflexology and wellness centers.</p>

  <p>
    <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13">
    <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.3 or newer">
    <img src="https://img.shields.io/badge/Database-MariaDB%20%2F%20MySQL-4479A1?logo=mysql&logoColor=white" alt="MariaDB or MySQL">
    <img src="https://img.shields.io/badge/UI-Blade%20%2B%20Bootstrap-7952B3?logo=bootstrap&logoColor=white" alt="Blade and Bootstrap">
  </p>
</div>

## Overview

Reflexology Manager brings daily branch operations, treatment bookings, point-of-sale transactions, inventory, purchasing, finance, accounting, staff attendance, and customer support into one application.

## What the app manages

<table>
  <thead>
    <tr>
      <th>Area</th>
      <th>Capabilities</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Operations and POS</strong></td>
      <td>Treatment transactions, voucher sales, package transactions, cashier workflows, room monitoring, therapist assignment, kitchen support, and booking notes.</td>
    </tr>
    <tr>
      <td><strong>Booking</strong></td>
      <td>Scheduled bookings, payment proof review, approval and rejection, room and therapist assignment, finalization, cancellation, and conversion into POS transactions.</td>
    </tr>
    <tr>
      <td><strong>Master data</strong></td>
      <td>Branches, rooms, treatments, packages, therapists, employees, positions, suppliers, members, vouchers, coupons, and banners.</td>
    </tr>
    <tr>
      <td><strong>Inventory</strong></td>
      <td>Items, categories, units, warehouses, stock cards, inventory balances, transfers, stock in, inventory in, and inventory out.</td>
    </tr>
    <tr>
      <td><strong>Purchasing</strong></td>
      <td>Internal requests, purchase requests, purchase orders, goods receiving, and supplier invoices.</td>
    </tr>
    <tr>
      <td><strong>Finance and accounting</strong></td>
      <td>Chart of accounts, bank cash in and out, journals, ledgers, trial balance, profit and loss, balance sheet, and cash flow reporting.</td>
    </tr>
    <tr>
      <td><strong>Reports</strong></td>
      <td>Transaction, daily recap, gross profit, therapist, voucher, payment, commission, inventory, purchasing, and user point reports.</td>
    </tr>
    <tr>
      <td><strong>Attendance and HR</strong></td>
      <td>Attendance imports, employee and partner attendance, salary workflows, and commission history.</td>
    </tr>
    <tr>
      <td><strong>Customer support</strong></td>
      <td>Customer accounts, memberships, points, reviews, notifications, vouchers, birthday promotions, and mobile app support.</td>
    </tr>
  </tbody>
</table>

## Technical profile

| Layer | Technologies |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+ |
| Admin authentication | Laravel session guard with a dedicated `admin` guard |
| API authentication | Laravel Passport OAuth2 tokens |
| Authorization | Spatie Laravel Permission roles and permissions |
| Database | MariaDB or MySQL |
| Admin UI | Blade, OneUI, Bootstrap, Font Awesome, jQuery |
| Interactive screens | Vue components, Axios, DataTables, Select2-style controls |


## Portfolio focus

This project demonstrates:

- A multi-module Laravel application with a shared admin shell.
- Multi-guard authentication for staff and customer-facing APIs.
- Role and permission-based access control.
- Transactional workflows that update related operational records.
- Queued notifications and scheduled background jobs.
- Server-side DataTables endpoints for large admin lists.
- PDF reports and spreadsheet import/export flows.
- Inventory and accounting flows connected to operational transactions.
- A mobile-support API for customer accounts, bookings, vouchers, points, and notifications.
