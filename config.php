<?php
// One connection for the entire application. Change these values for your XAMPP setup.
date_default_timezone_set('Asia/Dhaka');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = mysqli_connect('localhost', 'root', '', 'eticket_db');
mysqli_set_charset($conn, 'utf8mb4');
mysqli_query($conn, "SET time_zone = '+06:00'");
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly'=>true, 'samesite'=>'Lax', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    session_start();
}
