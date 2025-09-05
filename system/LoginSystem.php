<?php
session_start();
require_once '../config/db.php';

// CSRF Token check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'CSRF token ไม่ถูกต้อง';
    header('Location: ../login.php');
    exit();
}

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM admin WHERE username = :username";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_log'] = $user['username'];
            $_SESSION['show_dashboard_panels'] = true;
            header('Location: ../dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            header('Location: ../login.php');
            exit();
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
