<?php
require_once __DIR__ . '/../model/dbModel.php';
function e($value) {
 return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
 }
function go($page) {
 header('Location: '.$page);
 exit;
 }
function flash($message) {
 $_SESSION['message'] = $message;
 }
function dashboard() {
 return ($_SESSION['role'] ?? 'passenger').'_dashboard.php';
 }
// Recheck the database on every request so blocking an account ends its access immediately.
function current_user() {
    if (empty($_SESSION['user_id'])) return null;
    $user = one('SELECT * FROM users WHERE user_id=?', 'i', [$_SESSION['user_id']]);
    if (!$user || $user['status'] !== 'active') {
        unset($_SESSION['user_id'], $_SESSION['role']);
        return null;
    }
    $_SESSION['role'] = $user['role'];
    return $user;
}
function require_role($roles) {
    $user = current_user();
    if (!$user || !in_array($_SESSION['role'], (array)$roles, true)) go('login.php');
    return $user;
}
function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">';
}
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '') || empty($_SESSION['csrf'])) {
        http_response_code(403);
 exit('Invalid form token. Please reload the page.');
    }
}
function post($key) {
 return trim($_POST[$key] ?? '');
 }
function valid_date($value) {
 $d = DateTime::createFromFormat('!Y-m-d', $value);
 return $d && $d->format('Y-m-d') === $value;
 }
function valid_person($name, $email, $phone) {
    return strlen($name)>=2 && strlen($name)<=100 && strlen($email)<=100 && filter_var($email,FILTER_VALIDATE_EMAIL) && preg_match('/^\+?[0-9]{7,15}$/',$phone);
}
