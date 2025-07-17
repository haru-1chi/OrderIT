<?php
// $servername = "172.16.190.6";
// $username = "administratorsmhcc";
// $password = "msh10723@maesot";
// $servername = "localhost";
$servername = "172.16.190.145";
$username = "maesot";
$password = "d+JsqBY[7RgwApl%00";
//อย่าลืมเปลี่ยน data connect เป็น http://172.16.190.6/ จากไฟล์ Itdata

try {
    $leave_conn = new PDO("mysql:host=$servername;dbname=leave_db", $username, $password);
    // set the PDO error mode to exception
    $leave_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}