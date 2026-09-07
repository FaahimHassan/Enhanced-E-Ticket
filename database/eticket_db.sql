CREATE DATABASE IF NOT EXISTS eticket_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE eticket_db;
SET time_zone = '+06:00';
CREATE TABLE users (
 user_id INT AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 email VARCHAR(100) NOT NULL UNIQUE,
 phone VARCHAR(20) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('passenger','provider','admin') NOT NULL,
 status ENUM('active','blocked','pending') NOT NULL DEFAULT 'active',
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE transport_schedules (
 schedule_id INT AUTO_INCREMENT PRIMARY KEY,
 provider_id INT NULL,
 transport_type ENUM('bus','train','air') NOT NULL,
 source VARCHAR(100) NOT NULL,
 destination VARCHAR(100) NOT NULL,
 departure_time DATETIME NOT NULL,
 arrival_time DATETIME NOT NULL,
 price DECIMAL(10,2) NOT NULL,
 total_seats INT NOT NULL,
 available_seats INT NOT NULL,
 FOREIGN KEY (provider_id) REFERENCES users(user_id),
 INDEX search_route(transport_type,departure_time)
) ENGINE=InnoDB;
CREATE TABLE hotels (
 hotel_id INT AUTO_INCREMENT PRIMARY KEY,
 provider_id INT NOT NULL,
 hotel_name VARCHAR(150) NOT NULL,
 location VARCHAR(150) NOT NULL,
 price_per_night DECIMAL(10,2) NOT NULL,
 description TEXT NOT NULL,
 total_rooms INT NOT NULL,
 available_rooms INT NOT NULL,
 FOREIGN KEY (provider_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE TABLE transport_bookings (
 booking_id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 schedule_id INT NOT NULL,
 seat_number VARCHAR(10) NOT NULL,
 booking_status ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
 booked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(user_id),
 FOREIGN KEY(schedule_id) REFERENCES transport_schedules(schedule_id),
 INDEX seat_lookup(schedule_id,seat_number,booking_status)
) ENGINE=InnoDB;
CREATE TABLE hotel_bookings (
 booking_id INT AUTO_INCREMENT PRIMARY KEY,
 user_id INT NOT NULL,
 hotel_id INT NOT NULL,
 check_in DATE NOT NULL,
 check_out DATE NOT NULL,
 booking_status ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
 booked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(user_id) REFERENCES users(user_id),
 FOREIGN KEY(hotel_id) REFERENCES hotels(hotel_id)
) ENGINE=InnoDB;
-- Demo accounts only. Password for all four accounts: password
INSERT INTO users(name,email,phone,password,role,status) VALUES
('Platform Admin','admin@eticket.test','01700000001','$2y$12$Iu5wDTdoGFrFLsaLXQKa7.5MheGtG1dd1.R5dgzK7m1S5IrLRuZby','admin','active'),
('Greenline Travels','provider@eticket.test','01700000002','$2y$12$Iu5wDTdoGFrFLsaLXQKa7.5MheGtG1dd1.R5dgzK7m1S5IrLRuZby','provider','active'),
('Fahim Hasan','passenger@eticket.test','01700000003','$2y$12$Iu5wDTdoGFrFLsaLXQKa7.5MheGtG1dd1.R5dgzK7m1S5IrLRuZby','passenger','active'),
('Horizon Travels','pending@eticket.test','01700000004','$2y$12$Iu5wDTdoGFrFLsaLXQKa7.5MheGtG1dd1.R5dgzK7m1S5IrLRuZby','provider','pending');
-- Dates are relative to SQL import day. Search tomorrow for these sample journeys.
INSERT INTO transport_schedules(provider_id,transport_type,source,destination,departure_time,arrival_time,price,total_seats,available_seats) VALUES
(2,'bus','Dhaka','Cox''s Bazar',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'07:00:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'17:30:00'),1400,40,39),
(2,'bus','Dhaka','Cox''s Bazar',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'21:30:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 2 DAY),'07:30:00'),1800,32,32),
(NULL,'bus','Dhaka','Cox''s Bazar',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'23:00:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 2 DAY),'09:00:00'),1100,40,40),
(2,'train','Dhaka','Cox''s Bazar',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'08:00:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'16:30:00'),900,60,60),
(2,'air','Dhaka','Cox''s Bazar',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'10:00:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'11:10:00'),5500,80,80),
(2,'bus','Dhaka','Sylhet',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'09:00:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 1 DAY),'15:30:00'),800,40,40),
(2,'train','Dhaka','Chattogram',TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 2 DAY),'07:00:00'),TIMESTAMP(DATE_ADD(CURDATE(),INTERVAL 2 DAY),'13:00:00'),700,60,60);
INSERT INTO hotels(provider_id,hotel_name,location,price_per_night,description,total_rooms,available_rooms) VALUES
(2,'Coastal Haven','Cox''s Bazar',3200,'A peaceful double room close to the beach. Air conditioning and breakfast included.',12,11),
(2,'Tea Garden Retreat','Sylhet',2400,'A quiet room among green hills, with a private bathroom and breakfast.',8,8),
(2,'City Nest','Dhaka',2000,'A comfortable city room with air conditioning and a convenient central location.',15,15);
INSERT INTO transport_bookings(user_id,schedule_id,seat_number) VALUES(3,1,'5');
INSERT INTO hotel_bookings(user_id,hotel_id,check_in,check_out) VALUES(3,1,DATE_ADD(CURDATE(),INTERVAL 2 DAY),DATE_ADD(CURDATE(),INTERVAL 4 DAY));

-- Version 2 extensions for a fresh install.
ALTER TABLE transport_schedules
    ADD COLUMN schedule_status ENUM('active','cancelled') NOT NULL DEFAULT 'active',
    ADD COLUMN cancellation_reason VARCHAR(500) NULL,
    ADD COLUMN cancelled_at DATETIME NULL;
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
