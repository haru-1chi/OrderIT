<?php
require_once 'config/db.php';
date_default_timezone_set('Asia/Bangkok');

function sendTelegramMessage($message, $chatIds)
{
    $botToken = '7695900629:AAEA5RLovP1QDQy8w4jc8PMAvAoj1HZ6Ivo';
    if (!$botToken) {
        error_log("Missing Telegram bot token");
        return;
    }

    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    foreach ($chatIds as $chatId) {
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'timeout' => 5
            ]
        ];

        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            error_log("Failed to send message to chat ID $chatId");
        }
    }
}

try {
    $dateNow = new DateTime();
    $dateThai = $dateNow->format("Y-m-d");
    $timeThai = $dateNow->format("H:i:s");

    $sql1 = "SELECT dp.*, dt.depart_name 
             FROM data_report AS dp
             LEFT JOIN depart AS dt ON dp.department = dt.depart_id
             WHERE dp.status = 0 
               AND DATE(dp.date_report) = ? 
               AND TIME(dp.time_report) <= TIME(?)";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute([$dateThai, $timeThai]);
    $newReports = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // 2. Records at current time ±30 seconds
    $sql2 = "SELECT dp.*, dt.depart_name 
         FROM data_report AS dp
         LEFT JOIN depart AS dt ON dp.department = dt.depart_id
         WHERE dp.status = 0
           AND DATE(dp.date_report) = ? 
           AND ABS(TIMESTAMPDIFF(SECOND, TIME(dp.time_report), TIME(?))) <= 15";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->execute([$dateThai, $timeThai]);
    $timeReports = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['reports' => $newReports, 'reports_set_up' => $timeReports]);

    $sql3 = "SELECT dp.*, dt.depart_name 
         FROM data_report AS dp
         LEFT JOIN depart AS dt ON dp.department = dt.depart_id
         WHERE dp.status = 0 AND dp.telegram_notified = 0 AND DATE(date_report) = ?
         AND TIME(dp.time_report) <= TIME(?)
         ";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->execute([$dateThai, $timeThai]);
    $notiReports = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    $chatIds = ['6810241495'];
    //  $chatIds = ['6810241495', '7542936104', '6684593322', '7551836315', '7929326845', '6221065459'];

    foreach ($notiReports as $report) {
        $updateStmt = $conn->prepare("UPDATE data_report SET telegram_notified = 1 WHERE id = ?");
        $updateStmt->execute([$report['id']]);

        $message = "📢<b>New Report</b>\n👤หน่วยงาน: <b>{$report['depart_name']}</b>\n🛠อาการรับแจ้ง: {$report['report']}\n🧑‍💻ผู้คีย์งาน: {$report['create_by']}\n👉🏻รับงาน: http://172.16.190.17/orderit/system/take.php?id={$report['id']}";

        sendTelegramMessage($message, $chatIds);
    }
} catch (Exception $e) {
    error_log("Error in report check: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
