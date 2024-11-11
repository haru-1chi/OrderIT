<?php
$servername = "localhost";
$username = "AchirayaJ";
$password = "Haru1chi_KzhsLov3r";

try {
    $conn = new PDO("mysql:host=$servername;dbname=OrderIT", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

