<?php
// $servername = "172.16.190.6";
// $username = "administratorsmhcc";
// $password = "msh10723@maesot";
$servername = "localhost";
// $servername = "172.16.190.17";
$username = "AchirayaJ";
$password = "Haru1chi_KzhsLov3r";
//อย่าลืมเปลี่ยน data connect เป็น http://172.16.190.6/ จากไฟล์ Itdata

try {
    $conn = new PDO("mysql:host=$servername;dbname=OrderIT", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}