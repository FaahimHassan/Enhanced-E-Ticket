<?php
// Included by login and registration pages before any HTML is sent.
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if ($mode === 'login') {
        $user = one('SELECT * FROM users WHERE email=? OR phone=? LIMIT 1', 'ss', [post('identity'),post('identity')]);
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password'])) $error='Email/phone or password is incorrect.';
        elseif ($user['status'] !== 'active') $error='This account is awaiting approval or has been blocked. Please contact the administrator.';
        else {
            session_regenerate_id(true);
            $_SESSION['user_id']=$user['user_id'];
 $_SESSION['role']=$user['role'];
            go(dashboard());
        }
    }
 else {
        $name=post('name');
 $email=post('email');
 $phone=post('phone');
 $password=$_POST['password'] ?? '';
        if (!valid_person($name,$email,$phone) || strlen($password)<8 || strlen($password)>72) $error='Enter a valid name, email, phone and a password of 8–72 characters.';
        elseif ($password !== ($_POST['confirm_password'] ?? '')) $error='Passwords do not match.';
        else {
            try {
                query('INSERT INTO users(name,email,phone,password,role,status) VALUES(?,?,?,?,?,?)','ssssss',[$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT),$mode==='provider'?'provider':'passenger',$mode==='provider'?'pending':'active']);
                flash($mode==='provider'?'Registration received. An admin must approve your account before you can log in.':'Account created. You can now log in.');
 go('login.php');
            }
 catch (mysqli_sql_exception $exception) {
                if ($exception->getCode()===1062) $error='That email or phone is already registered.';
 else throw $exception;
            }
        }
    }
}
