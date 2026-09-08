# Enhanced E-Ticket 

This version extends the supplied Eticket.zip using procedural PHP, MySQLi, handwritten CSS and vanilla JavaScript. No application framework or npm installation is required.

**Existing XAMPP users: read UPGRADE.md first. Import database/upgrade_v2.sql into your existing database; do not re-import the full seed schema.**

## Included roles and features

| Role | Features |
| --- | --- |
| Passenger | Register/login, profile, transport and hotel booking, cancel eligible bookings, view payment/refund state, send booking-linked complaints/feedback, read and send replies |
| Provider | Register subject to admin approval, schedule and hotel CRUD, cancel future schedules, adjust capacity and block/unblock seats, own booking history, customer feedback/replies, automatically refreshing overview |
| Admin | Approve/reject providers, block/activate users, manage schedules and seats, view/cancel eligible bookings, all complaints/replies, manual payment/refund records, automatically refreshing overview |

## Technology and organization

- Root files are page entrypoints with role checks before HTML.
- controller/: authentication, common session/CSRF validation, CRUD request handling.
- model/: prepared database queries and business functions.
- view/: shared layouts, navigation and table templates.
- assets/: handwritten CSS, vanilla JavaScript and the existing photograph.
- database/eticket_db.sql: complete fresh-install schema and sample data.
- database/upgrade_v2.sql: non-destructive, repeatable upgrade for existing data.

## Run locally

See UPGRADE.md for Bengali step-by-step instructions, demo credentials, the upgrade process and feature walkthrough. For a fresh install: place eticket inside XAMPP htdocs, start Apache/MySQL, import database/eticket_db.sql once into a fresh database, configure config.php and open http://localhost/eticket/. Search the day after SQL import for sample journeys.

We added manual payment/refund records, complaints and feedback, operator cancellation, controlled seat blocking, and 10-second AJAX summary refresh. There is still no real payment gateway, OTP, chatbot, map, AI feature, or PDF ticket generator. No Site deployment is included in this update.

## Security and consistency

Password hashing, session role checks, CSRF tokens, output escaping and prepared MySQLi statements are retained. New actions repeat validation on the server. Providers only access their own services and booking-linked conversations. Transactions and shared row locks protect seat inventory and cancellation. Payment transitions prevent duplicate payment/refund recording and require a confirmed/unpaid or cancelled/paid booking respectively. See UPGRADE.md for exact business rules.

## Mai tasks

1. Authentication, registration/approval and account management.
2. Transport search, booking, inventory and schedule cancellation.
3. Hotel/provider management and customer conversations.
4. Shared UI, passenger dashboard, admin payment ledger and AJAX overview.

## Photo credit

Tea garden near Srimangal, Sylhet, Bangladesh: Xahidur Reza, CC BY 2.0. Cropped for display.
Source: https://commons.wikimedia.org/wiki/File:Tea_Garden_near_Srimangal,_Sylhet,_Bangladesh.jpg
License: https://creativecommons.org/licenses/by/2.0/

See FILES.md for all files and TESTING.md for validation performed.
