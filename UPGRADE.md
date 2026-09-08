# Version 2 — আপনার existing XAMPP project update করুন

এই update আপনার Eticket.zip-এর ওপর করা হয়েছে। পুরোনো Provider/Admin features রাখা হয়েছে এবং screenshot-এর নতুন কাজগুলো যোগ হয়েছে। **এটি localhost-এর PHP/MySQL project; আগের hosted browser preview এই update পায়নি।**

## Existing project থাকলে

1. XAMPP-এ Apache/MySQL চালু রাখুন। phpMyAdmin থেকে আপনার `eticket_db` database **Export → Quick → SQL** করে backup রাখুন। পুরোনো project folder-ও copy করে রাখুন।
2. Update করার সময় booking/registration বন্ধ রাখুন; অন্য browser tabs বন্ধ করুন।
3. নতুন ZIP extract করুন। ভিতরের `eticket` folder-এর **contents** দিয়ে আপনার existing project folder-এর files replace করুন, যেমন `C:\xampp\htdocs\eticket\`। একই folder-এর মধ্যে আবার `eticket` folder বসাবেন না।
4. আপনার database credentials পরিবর্তিত থাকলে পুরোনো `config.php` রাখুন, অথবা নতুন `config.php`-এ সেই credentials বসান। একই database ব্যবহার করতে হবে।
5. Browser-এ `http://localhost/phpmyadmin/` খুলুন। `eticket_db` select করে **Import** থেকে **শুধু** `database/upgrade_v2.sql` import করুন। অন্য database name ব্যবহার করলে SQL-এর প্রথম `USE eticket_db;` line-এ সেই name দিন।
6. Existing database-এর জন্য `database/eticket_db.sql` import করবেন না। সেটি fresh installation-এর schema ও demo data।
7. `http://localhost/eticket/` খুলে **Ctrl + F5** দিন। Logout করে আবার login করুন। নতুন navigation links দেখা যাবে।

`upgrade_v2.sql` users/bookings delete বা reset করে না। এটি নতুন tables/columns যোগ করে। কোনো partial import হলে একই upgrade আবার চালানো যায়; existing columns ও payment rows duplicate হয় না। পুরোনো bookings-এর জন্য payment record `unpaid` দিয়ে শুরু হয়—আগে টাকা দেওয়া হয়েছিল ধরে নেয় না।

## নতুন করে install করলে

1. `eticket` folder রাখুন `C:\xampp\htdocs\eticket\`-এ।
2. XAMPP-এ Apache ও MySQL Start করুন।
3. phpMyAdmin থেকে **নতুন/খালি database setup-এর জন্য** `database/eticket_db.sql` import করুন। আলাদা করে upgrade import প্রয়োজন নেই।
4. Browser: `http://localhost/eticket/`।

## কোন role কোথায় যাবে?

সব role একই `login.php` দিয়ে email/phone + password ব্যবহার করে login করবে। Session-এর role অনুযায়ী dashboard খুলবে।

| Role | Demo email | Password | Dashboard |
| --- | --- | --- | --- |
| Admin | admin@eticket.test | password | admin_dashboard.php |
| Provider | provider@eticket.test | password | provider_dashboard.php |
| Passenger | passenger@eticket.test | password | passenger_dashboard.php |
| Pending provider | pending@eticket.test | password | Admin approval ছাড়া login হবে না |

Existing database upgrade করলে আপনার existing users/passwords অপরিবর্তিত থাকবে। উপরের demo accounts কেবল আগে seed import করা থাকলে পাওয়া যাবে।

## Provider registration পরীক্ষা

1. Logout করুন। Navbar-এর **Provider registration** খুলুন।
2. Business/provider name, email, phone ও password দিয়ে register করুন।
3. Account `pending` হবে; এখন login করা যাবে না।
4. Admin হিসেবে login → **Providers** → নতুন provider-এর **Approve** চাপুন।
5. Logout → নতুন provider-এর email/password দিয়ে login করুন। Provider dashboard খুলবে।
6. **Add schedule**, **Add hotel**, **Manage seats**, **Cancel schedule**, **Bookings**, **Feedback & complaints**, **Live overview** ব্যবহার করুন।

## দ্রুত নতুন feature demo

- Passenger: একটি ticket/room book করুন → **Help & feedback** → নিজের booking বেছে complaint/feedback দিন।
- Provider: **Feedback & complaints** → conversation খুলে reply দিন → Mark resolved করুন।
- Passenger: একই conversation-এ reply ও status দেখুন। Follow-up reply দিলে resolved ticket আবার open হবে।
- Admin: **Complaints & feedback** থেকে সব conversation দেখুন ও reply দিন। General complaint শুধু Admin দেখবে।
- Provider/Admin: schedule-এর **Manage seats** থেকে unbooked seat tick করে block করুন। Passenger সেই seat book করতে পারবে না। Untick করলে আবার bookable হবে।
- **Live overview** খুলে অন্য browser session থেকে booking করুন। Visible dashboard ১০ সেকেন্ডের মধ্যে নতুন count/availability দেখাবে। এটি AJAX polling, WebSocket নয়।
- Admin: **Payments & refunds** → booking-এর payment বাইরে received হলে reference দিয়ে **Record payment received** দিন।
- Passenger/Admin booking cancel বা Provider schedule cancel করলে paid cancellation **refund due** filter-এ আসবে। বাইরে refund হওয়ার পরে **Record full refund** দিন। দ্বিতীয়বার refund record করা যাবে না।

**Payment/refund buttons টাকা পাঠায় না।** এটি manual ledger। Card, bKash, bank বা gateway integration নেই। Booking আগের মতো সরাসরি confirmed হয়।

## Business rules

- Cancelled schedule আবার activate করা হয় না; নতুন schedule add করুন। Cancellation reason passenger history-তে দেখা যায়।
- Schedule cancellation শুধু departure-এর আগে। এতে ওই schedule-এর সব confirmed tickets cancelled হয়, নতুন booking বন্ধ হয়, history থাকে।
- Confirmed seat block/remove করা যায় না। Capacity কমাতে চাইলে সেই capacity-এর বাইরের booked seats থাকা যাবে না। পুরোনো blocked seats capacity-এর বাইরে পড়লে প্রথমে untick করুন।
- Booking history থাকা schedule-এর route/time/price edit বন্ধ; seat capacity আলাদা Manage seats page থেকে safely বদলানো যায়। কোনো blocked seat থাকলে route/details edit-এর আগে unblock করুন।
- Admin individual booking cancel করতে পারে departure/check-in-এর আগে। Refund record আলাদা action।
- Payment amounts নতুন bookings-এর সময় snapshot হয়; পুরোনো booking backfill বর্তমান listing price ব্যবহার করে (পুরোনো schema-তে price snapshot ছিল না)। Hotel amount = nightly price × nights।
- Manual refunds শুধু পুরো recorded amount-এর জন্য; partial refund নেই। Paid/refunded history-তে admin, note এবং timestamp থাকে।
- Hotel room inventory আগের simple model-এ আছে: confirmed booking একটি room ধরে রাখে cancellation পর্যন্ত; date-overlap inventory নয়।
- Refresh বন্ধ account/session হলে live data দেখানো বন্ধ হয়। Provider শুধু নিজের service-এর data দেখে।

## Error হলে

- `Unknown column schedule_status` / `Table support_tickets doesn't exist`: `upgrade_v2.sql` একই configured database-এ import হয়নি।
- `Access denied for user`: config.php-এর MySQL username/password দেখুন।
- `Unknown database`: database import বা config-এর database name ভুল।
- CSS পুরোনো দেখালে Ctrl + F5 দিন।
- Page not found হলে folder-এর মধ্যে double `eticket/eticket` হয়েছে কি না দেখুন।

Validation-এর প্রকৃত ফল `TESTING.md`-এ এবং ফাইলের তালিকা `FILES.md`-এ আছে।
