-- BACK UP YOUR DATABASE FIRST. This upgrade does not delete users or bookings.
USE eticket_db;
SET time_zone = '+06:00';

SET @upgrade_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transport_schedules' AND COLUMN_NAME='schedule_status')=0, 'ALTER TABLE transport_schedules ADD COLUMN schedule_status ENUM(''active'',''cancelled'') NOT NULL DEFAULT ''active''', 'SELECT 1');
PREPARE upgrade_statement FROM @upgrade_sql;
EXECUTE upgrade_statement;
DEALLOCATE PREPARE upgrade_statement;

SET @upgrade_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transport_schedules' AND COLUMN_NAME='cancellation_reason')=0, 'ALTER TABLE transport_schedules ADD COLUMN cancellation_reason VARCHAR(500) NULL', 'SELECT 1');
PREPARE upgrade_statement FROM @upgrade_sql;
EXECUTE upgrade_statement;
DEALLOCATE PREPARE upgrade_statement;

SET @upgrade_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='transport_schedules' AND COLUMN_NAME='cancelled_at')=0, 'ALTER TABLE transport_schedules ADD COLUMN cancelled_at DATETIME NULL', 'SELECT 1');
PREPARE upgrade_statement FROM @upgrade_sql;
EXECUTE upgrade_statement;
DEALLOCATE PREPARE upgrade_statement;

CREATE TABLE IF NOT EXISTS blocked_seats (
    schedule_id INT NOT NULL,
    seat_number INT NOT NULL,
    PRIMARY KEY (schedule_id, seat_number),
    FOREIGN KEY (schedule_id) REFERENCES transport_schedules(schedule_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS support_tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider_id INT NULL,
    transport_booking_id INT NULL,
    hotel_booking_id INT NULL,
    category ENUM('complaint','feedback') NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    rating TINYINT NULL,
    status ENUM('open','resolved') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (provider_id) REFERENCES users(user_id),
    FOREIGN KEY (transport_booking_id) REFERENCES transport_bookings(booking_id),
    FOREIGN KEY (hotel_booking_id) REFERENCES hotel_bookings(booking_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS support_replies (
    reply_id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES support_tickets(ticket_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- This is a manual classroom ledger. It does not transfer money.
CREATE TABLE IF NOT EXISTS payment_records (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    transport_booking_id INT NULL UNIQUE,
    hotel_booking_id INT NULL UNIQUE,
    amount DECIMAL(16,2) NOT NULL,
    payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
    payment_note VARCHAR(500) NULL,
    refund_note VARCHAR(500) NULL,
    paid_by INT NULL,
    refunded_by INT NULL,
    paid_at DATETIME NULL,
    refunded_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transport_booking_id) REFERENCES transport_bookings(booking_id),
    FOREIGN KEY (hotel_booking_id) REFERENCES hotel_bookings(booking_id),
    FOREIGN KEY (paid_by) REFERENCES users(user_id),
    FOREIGN KEY (refunded_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Existing bookings start UNPAID; never assume money was received.
-- Old booking amounts use the current listing price because the original schema had no price snapshot.
INSERT INTO payment_records (transport_booking_id, amount)
SELECT b.booking_id, s.price
FROM transport_bookings b JOIN transport_schedules s ON b.schedule_id=s.schedule_id
LEFT JOIN payment_records p ON p.transport_booking_id=b.booking_id
WHERE p.payment_id IS NULL;

INSERT INTO payment_records (hotel_booking_id, amount)
SELECT b.booking_id, h.price_per_night * DATEDIFF(b.check_out,b.check_in)
FROM hotel_bookings b JOIN hotels h ON b.hotel_id=h.hotel_id
LEFT JOIN payment_records p ON p.hotel_booking_id=b.booking_id
WHERE p.payment_id IS NULL;
