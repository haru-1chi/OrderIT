<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
}
$id = $_GET['workid'];

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>รวมใบขอเบิก</title>
    <?php bs5(); ?>
    <style>
        @media print {
            .breakhere {
                page-break-after: always;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="breakhere">
        <?php include('ใบเบิกครุภัณฑ์ในแผน.php'); ?>
    </div>

    <div class="breakhere">
        <?php include('พิมพ์ใบครุภัณฑ์_รวม.php'); ?>
    </div>

    <div>
        <?php include('เอกสารคณะกรรมการ.php'); ?>
    </div>

</body>

</html>
