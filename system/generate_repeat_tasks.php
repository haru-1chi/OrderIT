<?php
date_default_timezone_set('Asia/Bangkok');
require_once __DIR__ . '/../config/db.php';


$today = date('D'); // Mon, Tue, Wed, Thu, Fri, Sat, Sun
$todayDate = date('Y-m-d');
$todayDay = date('j');

try {
    // 1. Get all repeat tasks with template data
    $sql = "SELECT rt.id AS repeat_id, rt.weekdays,rt.monthdays, tpl.* 
            FROM repeat_task rt
            JOIN routine_template tpl ON tpl.id = rt.report_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tasks as $task) {
        if ($task['work_type'] === '' || $task['priority'] === '') {
            $status = 7;
        } else {
            $status = 0;
        }

        $shouldGenerate = false;

        if (!empty($task['weekdays'])) {
            $weekdays = explode(',', $task['weekdays']);
            if (in_array($today, $weekdays)) {
                $shouldGenerate = true;
            }
        }

        if (!empty($task['monthdays'])) {
            $monthdays = explode(',', $task['monthdays']);
            if (in_array($todayDay, $monthdays)) {
                $shouldGenerate = true;
            }
        }

        if (!$shouldGenerate) {
            continue;
        }
        // 3. Prevent duplicate insert (already generated today?)
        $checkSql = "SELECT COUNT(*) FROM data_report 
                     WHERE date_report = :todayDate AND repeat_task_id = :repeat_id";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([
            ":todayDate" => $todayDate,
            ":repeat_id" => $task['repeat_id']
        ]);
        if ($checkStmt->fetchColumn() > 0) {
            continue; // already generated
        }

        // 4. Insert into data_report
        $insertSql = "INSERT INTO data_report 
            (date_report, time_report, device, deviceName, number_device, ip_address, report, reporter, department, tel, status, problem, sla, kpi, description, work_type, priority, note, create_by, repeat_task_id, repair_count)
            VALUES 
            (:date_report, :time_report, :device, :deviceName, :number_device, :ip_address, :report, :reporter, :department, :tel, :status, :problem, :sla, :kpi, :description, :work_type, :priority, :note, :create_by, :repeat_task_id, :repair_count)";

        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->execute([
            ":date_report" => $todayDate,
            ":time_report" => $task['time_report'],
            ":device" => $task['device'],
            ":deviceName" => $task['deviceName'],
            ":number_device" => $task['number_device'],
            ":ip_address" => $task['ip_address'],
            ":report" => $task['report'],
            ":reporter" => $task['reporter'],
            ":department" => $task['department'],
            ":tel" => $task['tel'],
            ":status" => $status,
            ":problem" => $task['problem'],
            ":sla" => $task['sla'],
            ":kpi" => $task['kpi'],
            ":description" => $task['description'],
            ":work_type" => $task['work_type'],
            ":priority" => $task['priority'],
            ":note" => $task['note'],
            ":create_by" => $task['create_by'],
            ":repeat_task_id" => $task['repeat_id'],
            ":repair_count" => 0 // start fresh
        ]);

        echo "Generated task for repeat_task ID {$task['repeat_id']} on {$todayDate}\n";
        echo "TodayDay: $todayDay | Monthdays: {$task['monthdays']} | Monthdays: {$task['weekdays']} today = $today\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
