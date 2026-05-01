<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once './function.class.php';
require_once '../db.php';

if (!isset($db)) {
    $db = new Database();
}

if (!isset($fn)) {
    $fn = new Functions();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['uemail']) || empty($_POST['upass'])) {
        $_SESSION['error'] = 'Please fill all required fields';
        header('Location: ' . BASE_URL . 'user/adminlogin.php');
        exit;
    }
    
    // Sanitize input
    $email = $db->real_escape_string($_POST['uemail']);
    
    $password = md5($db->real_escape_string($_POST['upass']));
    
    // Query the database
    $result = $db->query("SELECT id, full_name FROM admin WHERE email_id = '$email' AND password = '$password'");
    
    if (!$result) {
        $_SESSION['error'] = 'Database error: ' . $db->error;
        header('Location: ' . BASE_URL . 'user/adminlogin.php');
        exit;
    }
    
    $user = $result->fetch_assoc();
    if ($user) {
        $_SESSION['user'] = $user;
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_name'] = $user['full_name'];
        
        $_SESSION['success'] = 'Logged in Successfully';
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    } else {
        $_SESSION['error'] = 'Incorrect Email or Password';
        header('Location: ' . BASE_URL . 'user/adminlogin.php');
        exit;
    }
} else {
    header('Location: ' . BASE_URL . 'user/adminlogin.php');
    exit;
}
?>