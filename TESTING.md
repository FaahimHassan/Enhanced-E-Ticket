# Version 2 validation

## Checks performed

- All 50 PHP files passed actual PHP syntax checking (`php -l`) in a portable PHP 8.5.10 WebAssembly runtime. They also parsed with the tree-sitter PHP grammar.
- All delivered JavaScript files passed Node syntax checks.
- 46 business-logic checks passed by executing the real model function bodies in PHP against an in-memory SQLite test adapter. The test adapter substitutes the database connection/query helpers and removes MySQL `FOR UPDATE` syntax. It is test infrastructure only and is not included in or required by the application.
- The checked behaviors include: provider schedule creation; seat blocking/unblocking; capacity recomputation; ownership denial; duplicate/blocked-seat rejection; booking price snapshots; booked-seat protection; payment role checks; duplicate-payment/refund rejection; booking-linked complaint ownership; provider replies and resolution; passenger follow-up reopening; general-message privacy; schedule cancellation and ticket cancellation; refund eligibility; hotel inventory restoration; passenger booking ownership; repeated-cancellation rejection; filtered payment history; provider-only overview; and hiding cancelled schedules from search.
- Static PHP includes, page links and local asset references were checked.
- The deliverable contains no npm/framework dependencies. The testing tools live outside the application.
- Original config.php is preserved. Existing users/bookings are not replaced by the separate upgrade SQL.

## What these checks do not prove

- No native MySQL/MariaDB server was available here. The fresh schema and upgrade script were reviewed but not imported into an actual MySQL/MariaDB server in this environment.
- The SQLite adapter cannot validate MySQL locking/concurrent-request behavior, MySQL-specific DDL, MySQLi connection behavior or HTTP/session integration.
- No real browser visual or end-to-end tests were run.
- Real payment or refund transfers are not part of this project.

## Local XAMPP acceptance checklist

1. Back up your existing project and database. Import only upgrade_v2.sql for an existing installation. Confirm old users, transport bookings and hotel bookings remain. Re-import the upgrade to confirm it does not duplicate payment records.
2. Register a provider, confirm pending login is rejected, approve through admin and log in. Reject/block another account and check protected access on its next request.
3. Add a schedule/hotel; edit/delete an unbooked listing. Try a different provider's service ID and confirm access is denied.
4. Block a seat and verify a passenger cannot select or submit it. Unblock it and book it. Try removing/blocking a confirmed seat and confirm rejection.
5. Use two passenger sessions to submit the same seat. Exactly one booking must succeed.
6. Send a booking-linked complaint as Passenger. Its Provider and Admin should see it; another Provider should not. Reply, resolve and send a passenger follow-up. Check the status reopens. Test HTML-looking text displays as text.
7. Record payment received as Admin with a reference. Cancel its schedule as Provider. Confirm the passenger sees the cancellation reason, all confirmed tickets become cancelled, and no new bookings are accepted.
8. Open Payments & refunds, filter refund due, record a full refund and try repeating it. Confirm the recorded status appears in the passenger dashboard.
9. Cancel individual eligible transport/hotel bookings through Admin. Check inventory restores once; unpaid cancellations cannot be marked paid/refunded.
10. Open the live overview and create a booking in another session. Counts should update on the next 10-second poll. Block the provider and verify further polling is denied and cleared.
11. Update an existing hotel's price after a new booking and verify the payment record keeps the booked amount.
12. Check the layout on desktop and mobile and use the navigation to reach all role features.

Read UPGRADE.md for Bengali installation steps and the documented simple hotel inventory/cancellation rules.
