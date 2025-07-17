<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_log'])) {
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if ($id) {
    $username = $_SESSION['admin_log'];
    $status = 2;
    date_default_timezone_set('Asia/Bangkok');
    $take = date('H:i:s');

    $detailStmt = $conn->prepare("SELECT dp.id, dp.username, dp.report, dt.depart_name 
                              FROM data_report AS dp 
                              LEFT JOIN depart AS dt ON dp.department = dt.depart_id 
                              WHERE dp.id = :id");
    $detailStmt->bindParam(":id", $id);
    $detailStmt->execute();
    $report = $detailStmt->fetch(PDO::FETCH_ASSOC);

    if ($report && isset($report['username']) && $report['username'] !== '') {
        $_SESSION["warning"] = "งานนี้ถูกรับโดย {$report['username']} ไปแล้ว";
        header("Location: ../dashboard.php");
        exit();
    }

    $sql = "UPDATE data_report SET username = :username, take = :take, status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":take", $take);
    $stmt->bindParam(":id", $id);

    if ($stmt->execute()) {
        if ($report) {
            $message = "📢 <b>รับงานแล้ว!</b>\n🧑‍💻ผู้รับงาน: <b>$username</b>\n------------------------------------------------------\n🔧เลขงาน: <b>{$report['id']}</b>\n👤หน่วยงาน: <b>{$report['depart_name']}</b>\n🛠อาการรับแจ้ง: <b>{$report['report']}</b>";

            // เรียกแบบ async
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://localhost/htdocs/orderit/system/send_telegram.php"); // เปลี่ยนให้ตรง URL จริง
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['message' => $message]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // ไม่ต้องรอผล
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 100);       // 100ms ก็พอ
            curl_exec($ch);
            curl_close($ch);
        }

        $_SESSION["success"] = "รับงานเรียบร้อยแล้ว";
        header("location: ../myjob.php");
        exit();
    } else {
        $_SESSION["error"] = "พบข้อผิดพลาด";
        header("location: ../dashboard.php");
        exit();
    }
}
