<?php
// Set the file encoding to UTF-8
header('Content-Type: text/html; charset=utf-8');

require_once 'config/db.php';
require_once 'config/leave_db.php';
// Start the session if not already started
session_start();

// Step 1: Get leave days
$leaveDaysMap = []; // username => [ 'YYYY-MM-DD', ... ]

// Step 2: Get admin EMPLOYEE_ID → username
$adminSql = "SELECT username, id_card FROM admin";
$adminStmt = $conn->prepare($adminSql);
$adminStmt->execute();
$adminMap = [];
foreach ($adminStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $adminMap[$row['id_card']] = $row['username'];
}

// Step 3: Get leave ranges from leave_db
$leaveSql = "SELECT EMPLOYEE_ID, START_DATE, END_DATE FROM data_leave";
$leaveStmt = $leave_conn->prepare($leaveSql); // use leave_conn for leave_db
$leaveStmt->execute();

foreach ($leaveStmt->fetchAll(PDO::FETCH_ASSOC) as $leave) {
    $empId = $leave['EMPLOYEE_ID'];
    $username = $adminMap[$empId] ?? null;
    if (!$username) continue;

    $start = new DateTime($leave['START_DATE']);
    $end = new DateTime($leave['END_DATE']);

    while ($start <= $end) {
        $leaveDaysMap[$username][] = $start->format('Y-m-d');
        $start->modify('+1 day');
    }
}

// Check if the user is logged in
if (isset($_SESSION['admin_log'])) {
    $admin = $_SESSION['admin_log'];

    // Fetch admin details
    $sql = "SELECT * FROM admin WHERE username = :admin";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":admin", $admin);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['activity_username'])) {
    $filter = $_POST['filter'] ?? 'problem';
    $selectedUser = $_POST['username'] ?? 'all';
    $dateFilterType = $_POST['date_filter_type'] ?? 'period';
    $filterCondition = "1"; // default if nothing set
    $start = null;
    $end = null;
    if ($dateFilterType === 'period') {
        $period = $_POST['period'] ?? 'week';
        switch ($period) {
            case 'week':
                $filterCondition = "WEEK(date_report) = WEEK(CURDATE())";
                break;
            case 'month':
                $filterCondition = "MONTH(date_report) = MONTH(CURDATE())";
                break;
            case 'year':
                $filterCondition = "YEAR(date_report) = YEAR(CURDATE())";
                break;
        }
    } elseif ($dateFilterType === 'custom') {
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;
        if ($startDate && $endDate) {
            // convert Buddhist year to Gregorian
            $startTimestamp = strtotime($startDate);
            $endTimestamp = strtotime($endDate);

            $start = (date("Y", $startTimestamp)) . date("-m-d", $startTimestamp);
            $end   = (date("Y", $endTimestamp)) . date("-m-d", $endTimestamp);

            $filterCondition = "date_report BETWEEN :dateStart AND :dateEnd";
        }
    }

    $sql = "
        SELECT 
            CONCAT(a.fname, ' ', a.lname) AS name,
            DATE(date_report) AS real_date,
            TIME(take) AS take_time,
            TIME(close_date) AS close_time,
            $filter AS info
        FROM 
            data_report d
		JOIN 
            admin a
        ON 
            d.username = a.username
        WHERE 
            status = 4 AND
        $filterCondition
    ";

    if ($selectedUser !== 'all') {
        $sql .= " AND d.username = :username";
    }

    $sql .= " ORDER BY real_date ASC";

    $stmt = $conn->prepare($sql);
    if ($selectedUser !== 'all') {
        $stmt->bindParam(':username', $selectedUser, PDO::PARAM_STR);
    }
    if ($dateFilterType === 'custom' && isset($start) && isset($end)) {
        $stmt->bindParam(":dateStart", $start);
        $stmt->bindParam(":dateEnd", $end);
    }
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //ไม่พบรายการ
    if (empty($result)) {
        if ($selectedUser === 'all') {
            $selectedUserText = 'ทุกคน';
        } else {
            $selectedUserText = $selectedUser;
        }

        if ($dateFilterType === 'period') {
            switch ($period) {
                case 'week':
                    $_SESSION['export_error'] = "ไม่พบรายการใดๆใน สัปดาห์นี้ ของ $selectedUserText";
                    break;
                case 'month':
                    $_SESSION['export_error'] = "ไม่พบรายการใดๆใน เดือนนี้ ของ $selectedUserText";
                    break;
                case 'year':
                    $_SESSION['export_error'] = "ไม่พบรายการใดๆใน ปีนี้ ของ $selectedUserText";
                    break;
            }
        } elseif ($dateFilterType === 'custom' && isset($startDate) && isset($endDate)) {
            $_SESSION['export_error'] = "ไม่พบรายการใดๆในช่วง $startDate - $endDate ของ $selectedUserText";
        } else {
            $_SESSION['export_error'] = "ไม่พบรายการใดๆ ของ $selectedUserText";
        }

        header("Location: summary.php");
        exit();
    }

    $timeSlots = [];
    for ($hour = 8; $hour < 16; $hour++) {
        $start = sprintf('%02d:30', $hour);
        $end = sprintf('%02d:30', $hour + 1);
        $label = "$start-$end";
        $timeSlots[] = $label;
    }

    // Organize by [username][date][slot] = info
    $grouped = [];
    foreach ($result as $row) {
        $name = $row['name'];
        $date = (new DateTime($row['real_date']))->format('d/m/y');
        $start = new DateTime("2023-01-01 " . $row['take_time']);
        $end = new DateTime("2023-01-01 " . $row['close_time']);
        $info = $row['info'];

        foreach ($timeSlots as $slot) {
            list($slotStartStr, $slotEndStr) = explode('-', $slot);
            $slotStart = new DateTime("2023-01-01 $slotStartStr");
            $slotEnd = new DateTime("2023-01-01 $slotEndStr");

            if ($end > $slotStart && $start < $slotEnd) {
                $grouped[$name][$date][$slot] = $info;
            }
        }
    }

    if ($selectedUser === 'all') {
        $name = 'ทุกคน';
    }

    echo '<head><meta charset="UTF-8">';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Activity_Report_$name.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Output
    foreach ($grouped as $name => $days) {
        echo "<table border='1' style='margin-bottom: 20px;'>";
        echo "<tr><th colspan='" . (count($timeSlots) + 1) . "'>Activity Report ของ $name</th></tr>";
        echo "<tr><th>วันที่</th>";
        foreach ($timeSlots as $slot) {
            echo "<th>$slot</th>";
        }
        echo "</tr>";

        foreach ($days as $date => $slots) {
            echo "<tr><td>$date</td>";
            foreach ($timeSlots as $slot) {
                echo "<td>" . ($slots[$slot] ?? '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}


if (isset($_POST['activity'])) {
    // Fetch data
    $date = $_POST['date'] ?? date('Y-m-d');
    $filter = $_POST['filter'] ?? 'problem';
    $validFilters = ['device', 'problem', 'report', 'sla'];
    $filterColumn = in_array($filter, $validFilters) ? $filter : 'problem';

    $sql = "
        SELECT 
            CONCAT(a.fname, ' ', a.lname) AS name,
            date_report,
            TIME(take) AS take_time,
            TIME(close_date) AS close_time,
            $filterColumn AS problem
        FROM 
            data_report d
		JOIN 
            admin a
        ON 
            d.username = a.username
        WHERE 
            status = 4 
            AND DATE(date_report) = :selected_date
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':selected_date', $date, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //ไม่พบรายการ
    if (empty($result)) {
        $_SESSION['export_error'] = "ไม่พบรายการใดๆใน $date";
        header("Location: summary.php");
        exit();
    }

    // Time ranges: 08:30 - 09:30 to 15:30 - 16:30
    $timeSlots = [];
    for ($hour = 8; $hour < 16; $hour++) {
        $start = sprintf('%02d:30', $hour);
        $end = sprintf('%02d:30', $hour + 1);
        $label = "$start-$end";
        $timeSlots[] = $label;
    }

    // Summarize data
    $summary = []; // ['name' => ['08:30-09:30' => 'Problem Name', ...]]

    foreach ($result as $row) {
        $name = $row['name'];
        $date = (new DateTime($row['date_report']))->format('d/m/y');
        $start = new DateTime("2023-01-01 " . $row['take_time']);
        $end = new DateTime("2023-01-01 " . $row['close_time']);
        $problem = $row['problem'];

        foreach ($timeSlots as $slot) {
            list($slotStartStr, $slotEndStr) = explode('-', $slot);
            $slotStart = new DateTime("2023-01-01 $slotStartStr");
            $slotEnd = new DateTime("2023-01-01 $slotEndStr");

            // If there's overlap, record the task
            if ($end > $slotStart && $start < $slotEnd) {
                if (!isset($summary[$name][$slot]) || !isset($summary[$name])) {
                    $summary[$name][$slot] = $problem;
                }
            }
        }
    }

    echo '<head><meta charset="UTF-8">';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Activity_Report_$date.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Output the table
    echo '<table border="1">';
    echo "<tr><th colspan='9'>Activity Report ประจำวันที่ $date </th></tr>";
    echo '<thead><tr><th>ชื่อ</th>';
    foreach ($timeSlots as $slot) {
        echo "<th>$slot</th>";
    }
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($summary as $name => $slots) {
        echo "<tr><td>$name</td>";
        foreach ($timeSlots as $slot) {
            echo '<td>' . ($slots[$slot] ?? '') . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
}


// Check if the export action is triggered
if (isset($_POST['actAll'])) {
    echo '<head>';
    echo '<meta charset="UTF-8">';

    // Set headers for Excel file download
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=งานที่เสร็จทั้งหมด.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Output the HTML for the table
    echo '<table style="border: 1px solid black;">';
    echo '<thead>';
    echo '<tr style="text-align:center;">';
    echo '<th style="border: 1px solid black;"  scope="col">หมายเลขงาน</th>';
    echo '<th style="border: 1px solid black;"  scope="col">วันที่</th>';
    echo '<th style="border: 1px solid black;"  scope="col">ผู้ซ่อม</th>';
    echo '<th style="border: 1px solid black;"  scope="col">อุปกรณ์</th>';
    echo '<th style="border: 1px solid black;"  scope="col">รูปแบบการทำงาน</th>';
    echo '<th style="border: 1px solid black;"  scope="col">หมายเลขครุภัณฑ์</th>';
    echo '<th style="border: 1px solid black;"  scope="col">ปัญหาเกี่ยวกับ</th>';
    echo '<th style="border: 1px solid black;"  scope="col">อาการเสีย</th>';
    echo '<th style="border: 1px solid black;"  scope="col">ผู้แจ้ง</th>';
    echo '<th style="border: 1px solid black;"  scope="col">หน่วยงาน</th>';
    echo '<th style="border: 1px solid black;"  scope="col">เบอร์โทรติดต่อ</th>';
    echo '<th style="border: 1px solid black;"  scope="col">SLA</th>';
    echo '<th style="border: 1px solid black;"  scope="col">ตัวชี้วัด</th>';
    echo '<th style="border: 1px solid black;"  scope="col">เวลาแจ้ง</th>';
    echo '<th style="border: 1px solid black;"  scope="col">เวลารับงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">เวลาปิดงาน</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch data from the database
    $sql = "SELECT dp.*,dt.depart_name 
            FROM data_report as dp
            INNER JOIN depart as dt ON dp.department = dt.depart_id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $row) {
        $reportTimeString = $row['time_report'];
        $takeTimeString = $row['take'];
        $closeTimeString = $row['close_date'];
        if (empty($closeTimeString) || $closeTimeString === '00:00:00.000000' || empty($takeTimeString) || $takeTimeString === '00:00:00.000000' || empty($reportTimeString) || $reportTimeString === '00:00:00.000000') {
            $reportFormatted = '-';
            $takeFormatted = '-';
            $closeTimeFormatted = '-';
        } else {
            $reportFormatted = date('H:i', strtotime($takeTimeString)) . 'น.';
            $takeFormatted = date('H:i', strtotime($takeTimeString)) . ' น.';
            $closeTimeFormatted = date('H:i', strtotime($closeTimeString)) . ' น.';
        }
        if ($row['status'] == 4) {
            echo '<tr style="text-align:center;">';
            echo '<td style="border: 1px solid black;">' . $row['id'] . '</td>';
            // Fetch the admin details for the specific user
            $sqlAdmin = "SELECT * FROM admin WHERE username = :username";
            $stmtAdmin = $conn->prepare($sqlAdmin);
            $stmtAdmin->bindParam(":username", $row['username']);
            $stmtAdmin->execute();
            $resultAdmin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);
            echo '<td style="border: 1px solid black;">' . $row['date_report'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $resultAdmin['fname'] . ' ' . $resultAdmin['lname'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['deviceName'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['device'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['number_device'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['problem'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['report'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['reporter'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['depart_name'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['tel'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['sla'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $row['kpi'] . '</td>';
            echo '<td style="border: 1px solid black;">' . $reportFormatted . '</td>';
            echo '<td style="border: 1px solid black;">' . $takeFormatted . '</td>';
            echo '<td style="border: 1px solid black;">' . $closeTimeFormatted . '</td>';
            echo '</tr>';
        }
    }

    echo '</tbody>';
    echo '</table>';

    // Exit to prevent any further output
    exit();
}

if (isset($_POST['activity_usernames'])) {
    $selectedUser = $_POST['username'] ?? 'all';
    // Insert leave days into NOT IN (...)
    $leaveDates = $leaveDaysMap[$selectedUser] ?? [];
    $leaveNotInMain = '';
    $leaveNotInFree = '';

    if (!empty($leaveDates)) {
        $quotedDates = array_map(function ($d) use ($conn) {
            return $conn->quote($d);
        }, $leaveDates);

        $leaveNotInMain = "AND d.date_report NOT IN (" . implode(',', $quotedDates) . ")";
        $leaveNotInFree = "AND date_report NOT IN (" . implode(',', $quotedDates) . ")";
    }

    function getWorkingDays($year, $month, $holidays = [], $leaveDays = [])
    {
        $workingDays = [];
        $start = new DateTime("$year-$month-01");
        $end = clone $start;
        $end->modify('last day of this month');

        while ($start <= $end) {
            $day = $start->format('Y-m-d');
            $dayOfWeek = $start->format('N'); // 6 = Saturday, 7 = Sunday

            if ($dayOfWeek < 6 && !in_array($day, $holidays) && !in_array($day, $leaveDays)) {
                $workingDays[] = $day;
            }

            $start->modify('+1 day');
        }

        return $workingDays;
    }


    $filter = $_POST['filter'] ?? 'problem';
    if (isset($_POST['month'])) {
        list($year, $month) = explode('-', $_POST['month']);
    } else {
        $year = date('Y');
        $month = date('n');
    }


    $sqld = "
SELECT 
    d.username,
    CONCAT(a.fname, ' ', a.lname) AS name,
    $filter AS info,
    SEC_TO_TIME(AVG(ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))))) AS avg_time,
    ROUND(AVG(ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take)))) / 60, 2) AS avg_minutes,
    ROUND(SUM(ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take)))) / 60, 2) AS total_minutes,
    COUNT(*) AS count_tasks
FROM 
    data_report d
JOIN 
    admin a ON d.username = a.username
WHERE 
    d.status = 4
    AND MONTH(d.date_report) = :month
    AND YEAR(d.date_report) = :year
    AND DAYOFWEEK(d.date_report) NOT IN (1, 7)
    AND d.date_report NOT IN (
        '2025-01-01','2025-02-12','2025-04-07','2025-04-14',
        '2025-04-15','2025-04-16','2025-05-05','2025-05-09',
        '2025-05-12','2025-06-02','2025-06-03'
    )
        $leaveNotInMain
";
    $sql = "SELECT 
    d.username,
    CONCAT(a.fname, ' ', a.lname) AS name,
    $filter AS info,
    
    -- Average of rounded time (convert back to TIME)
    SEC_TO_TIME(AVG(rounded_minutes * 60)) AS avg_time,
     ROUND(AVG(rounded_minutes), 2) AS avg_minutes,
    -- Total of rounded minutes
    SUM(rounded_minutes) AS total_minutes,
    
    COUNT(*) AS count_tasks

FROM (
    SELECT 
        d.*,
        -- Time spent in minutes
        ROUND(ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60) AS raw_minutes,

        -- Custom rounded minutes with ABS
        CASE
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 1 THEN 0
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 30 THEN 60
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 60 THEN 60
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 90 THEN 90
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 120 THEN 120
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 150 THEN 150
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 180 THEN 180
            WHEN ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 60 < 210 THEN 210
            ELSE CEIL(ABS(TIME_TO_SEC(TIMEDIFF(d.close_date, d.take))) / 1800) * 30
        END AS rounded_minutes


    FROM data_report d
    WHERE 
        d.status = 4
        AND MONTH(d.date_report) = :month
    AND YEAR(d.date_report) = :year
        AND DAYOFWEEK(d.date_report) NOT IN (1, 7)
        AND d.date_report NOT IN (
            '2025-01-01', '2025-02-12', '2025-04-07', '2025-04-14',
            '2025-04-15', '2025-04-16', '2025-05-05', '2025-05-09',
            '2025-05-12', '2025-06-02', '2025-06-03'
        )
        $leaveNotInMain
";

    if ($selectedUser !== 'all') {
        $sql .= " AND d.username = :username";
    }

    $sql .= ") AS d

JOIN admin a ON d.username = a.username
GROUP BY d.username, $filter ORDER BY d.username, $filter";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':month', (int)$month, PDO::PARAM_INT);
    $stmt->bindValue(':year', (int)$year, PDO::PARAM_INT);


    if ($selectedUser !== 'all') {
        $stmt->bindValue(':username', $selectedUser, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo '<head><meta charset="UTF-8">';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Activity_Report.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Group by user
    $grouped = [];
    foreach ($results as $row) {
        $grouped[$row['name']][] = $row;
    }
    $sqlFreeTime = "
SELECT 
    day_data.username,
    SEC_TO_TIME(AVG(day_data.total_free_time_minutes) * 60) AS avg_free_time_hours,
    ROUND(SUM(day_data.total_free_time_minutes), 2) AS total_free_time_minutes
FROM (
    SELECT
        username,
        date_report AS work_date,
        ROUND(
            480 - SUM(TIME_TO_SEC(TIMEDIFF(
                STR_TO_DATE(CONCAT(date_report, ' ', close_date), '%Y-%m-%d %H:%i:%s'),
                STR_TO_DATE(CONCAT(date_report, ' ', take), '%Y-%m-%d %H:%i:%s')
            )) / 60), 2
        ) AS total_free_time_minutes
    FROM data_report
    WHERE 
        status = 4
        AND MONTH(date_report) = :month
        AND YEAR(date_report) = :year
        AND DAYOFWEEK(date_report) NOT IN (1, 7)
        AND date_report NOT IN (
            '2025-01-01','2025-02-12','2025-04-07','2025-04-14',
            '2025-04-15','2025-04-16','2025-05-05','2025-05-09',
            '2025-05-12','2025-06-02','2025-06-03'
        )
            $leaveNotInFree
    GROUP BY username, date_report
) AS day_data
WHERE day_data.total_free_time_minutes >= 0
GROUP BY day_data.username";

    $stmtFree = $conn->prepare($sqlFreeTime);
    $stmtFree->bindValue(':month', (int)$month, PDO::PARAM_INT);
    $stmtFree->bindValue(':year', (int)$year, PDO::PARAM_INT);
    $stmtFree->execute();
    $freeTimeResults = $stmtFree->fetchAll(PDO::FETCH_ASSOC);

    // Map: username => [avg_hours, total_minutes]
    $freeTimeMap = [];
    foreach ($freeTimeResults as $row) {
        $freeTimeMap[$row['username']] = [
            'avg' => $row['avg_free_time_hours'],
            'total' => $row['total_free_time_minutes']
        ];
    }
    $thaiMonth = toMonthThai((int)$month);
    echo "<table border='1' style='margin-bottom: 20px; border-collapse: collapse;'>";
    echo "<tr><th colspan='4'>ข้อมูลการปฏิบัติงานของเจ้าหน้าที่ทุกคน เดือน $thaiMonth</th></tr>";
    echo "<tr></tr>";
    echo "</table>";
    // Output tables per user
    foreach ($grouped as $name => $rows) {
        $username = $rows[0]['username'];

        $thaiHolidays = [
            '2025-01-01',
            '2025-02-12',
            '2025-04-07',
            '2025-04-14',
            '2025-04-15',
            '2025-04-16',
            '2025-05-05',
            '2025-05-09',
            '2025-05-12',
            '2025-06-02',
            '2025-06-03'
        ];

        $leaveDays = $leaveDaysMap[$username] ?? [];
        $workingDays = getWorkingDays($year, str_pad($month, 2, '0', STR_PAD_LEFT), $thaiHolidays, $leaveDays);
        $totalWorkingDays = count($workingDays);

        echo "<table border='1' style='margin-bottom: 20px; border-collapse: collapse;'>";
        echo "<tr><th colspan='4'>ข้อมูลการปฏิบัติงาน ของ $name</th></tr>";
        echo "<tr><th>กิจกรรม</th><th>เวลาเฉลี่ย/ครั้ง</th><th>เวลาทั้งหมด (นาที)</th><th>จำนวนครั้ง</th></tr>";

        foreach ($rows as $row) {
            // Convert avg_minutes to readable format
            $avg = floatval($row['avg_minutes']);
            $hours = floor($avg / 60);
            $minutes = round($avg % 60);
            $avgText = ($hours > 0 ? "{$hours} ชม. " : "") . "{$minutes} นาที";

            echo "<tr>";
            echo "<td style='width: 700px; white-space: nowrap;'>{$row['info']}</td>";
            echo "<td>$avgText</td>";
            echo "<td>{$row['total_minutes']}</td>";
            echo "<td>{$row['count_tasks']}</td>";
            echo "</tr>";
        }

        if (isset($freeTimeMap[$username])) {
            // all rows in this group share the same username
            $avgTimeRaw = $freeTimeMap[$username]['avg']; // like 03:00:55
            $totalMinutes = $freeTimeMap[$username]['total']; // like 2352

            // Format avg time as readable Thai text
            list($h, $m, $s) = explode(':', $avgTimeRaw);
            $avgText = '';
            if ((int)$h > 0) $avgText .= (int)$h . ' ชม. ';
            if ((int)$m > 0) $avgText .= (int)$m . ' นาที';

            echo "<tr>";
            echo "<td><b>เวลาว่าง</b></td>";
            echo "<td>$avgText</td>";
            echo "<td>$totalMinutes</td>";
            echo "<td>$totalWorkingDays วัน</td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "</tr>";
        echo "</table>";
    }
}
// Check if the export action is triggered
if (isset($_POST['act'])) {
    echo '<head>';
    echo '<meta charset="UTF-8">';

    // Set headers for Excel file download
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=งานของฉัน.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Output the HTML for the table
    echo '<table style="border: 1px solid black;">';
    echo '<thead>';
    echo '<tr style="text-align:center;">';
    echo '<th style="border: 1px solid black;" scope="col">ลำดับ</th>';
    echo '<th style="border: 1px solid black;" scope="col">วันที่</th>';
    echo '<th style="border: 1px solid black;" scope="col">เวลาแจ้ง</th>';
    echo '<th style="border: 1px solid black;" scope="col">เวลาปิดงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">สถานะ</th>';
    echo '<th style="border: 1px solid black;" scope="col">ประเภทงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขครุภัณฑ์</th>';
    echo '<th style="border: 1px solid black;" scope="col">อาการเสีย</th>';
    echo '<th style="border: 1px solid black;" scope="col">ผู้แจ้ง</th>';
    echo '<th style="border: 1px solid black;" scope="col">หน่วยงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">เบอร์ติดต่อกลับ</th>';
    // ... (other table headers)
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch data from the database
    $sql = "SELECT dp.*, dt.depart_name 
            FROM data_report as dp
            INNER JOIN depart as dt ON dp.department = dt.depart_id
            WHERE dp.username = :username ORDER BY dp.status";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":username", $admin);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the table rows
    foreach ($result as $row) {
        $dateWithdrawThai = formatDateThai($row['date_report']);
        $closeDateText = ($row['close_date'] === '00:00:00.000000' || $row['close_date'] === null) ? '-' : $row['close_date'];
        $statusText = '';
        switch ($row['status']) {
            case 2:
                $statusText = 'กำลังดำเนินงาน';
                break;
            case 3:
                $statusText = 'รออะไหล่';
                break;
            case 4:
                $statusText = 'เสร็จงาน';
                break;
            case 5:
                $statusText = 'ส่งซ่อม';
                break;
            case 6:
                $statusText = 'รอกรอกรายละเอียด';
                break;
            default:
                $statusText = 'ไม่ทราบสถานะ'; // Fallback for unexpected values
        }

        echo '<tr style="text-align: center;"';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['id'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $dateWithdrawThai . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['time_report'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $closeDateText . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $statusText . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['device'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['number_device'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['report'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['reporter'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['depart_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['tel'] . '</td>';
        // ... (other table data)
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Exit to prevent any further output
    exit();
}

// Check if the export action is triggered
if (isset($_POST['actUncomplete'])) {
    echo '<head>';
    echo '<meta charset="UTF-8">';

    // Set headers for Excel file download
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=งานของฉัน.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Output the HTML for the table
    echo '<table style="border: 1px solid black;">';
    echo '<thead>';
    echo '<tr style="text-align:center;">';
    echo '<th style="border: 1px solid black;" scope="col">ลำดับ</th>';
    echo '<th style="border: 1px solid black;" scope="col">วันที่</th>';
    echo '<th style="border: 1px solid black;" scope="col">เวลาแจ้ง</th>';
    echo '<th style="border: 1px solid black;" scope="col">เวลาปิดงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">สถานะ</th>';
    echo '<th style="border: 1px solid black;" scope="col">ประเภทงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขครุภัณฑ์</th>';
    echo '<th style="border: 1px solid black;" scope="col">อาการเสีย</th>';
    echo '<th style="border: 1px solid black;" scope="col">ผู้แจ้ง</th>';
    echo '<th style="border: 1px solid black;" scope="col">หน่วยงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">เบอร์ติดต่อกลับ</th>';
    // ... (other table headers)
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Fetch data from the database
    $sql = "SELECT dp.*, dt.depart_name 
            FROM data_report as dp
            INNER JOIN depart as dt ON dp.department = dt.depart_id
            WHERE dp.username = :username AND dp.status = 6 ORDER BY dp.date_report ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":username", $admin);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the table rows
    foreach ($result as $row) {
        $dateWithdrawThai = formatDateThai($row['date_report']);
        $closeDateText = ($row['close_date'] === '00:00:00.000000' || $row['close_date'] === null) ? '-' : $row['close_date'];
        $statusText = '';
        switch ($row['status']) {
            case 2:
                $statusText = 'กำลังดำเนินงาน';
                break;
            case 3:
                $statusText = 'รออะไหล่';
                break;
            case 4:
                $statusText = 'เสร็จงาน';
                break;
            case 5:
                $statusText = 'ส่งซ่อม';
                break;
            case 6:
                $statusText = 'รอกรอกรายละเอียด';
                break;
            default:
                $statusText = 'ไม่ทราบสถานะ'; // Fallback for unexpected values
        }

        echo '<tr style="text-align: center;"';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['id'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $dateWithdrawThai . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['time_report'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $closeDateText . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $statusText . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['device'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['number_device'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['report'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['reporter'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['depart_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['tel'] . '</td>';
        // ... (other table data)
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Exit to prevent any further output
    exit();
}

if (isset($_POST['DataAll'])) {
    echo '<head>';
    echo '<meta charset="UTF-8">';

    // Set headers for Excel file download
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=บันทึกข้อมูลใบเบิก.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Simulated data from SQL
    $sql = "
    SELECT 
    od.*, 
    os.status,
    wd.withdraw_name, 
    lw.work_name, 
    dv.device_name, 
    dp.depart_name, 
    of.offer_name,
    nd.numberDevice, 
    nd.id AS numberDevice_id, 
    oi.id AS item_id, 
    oi.list, 
    dm.models_name as list_name, 
    oi.quality, 
    oi.amount, 
    oi.price, 
    oi.unit
    FROM 
    orderdata_new AS od
    LEFT JOIN (
                     SELECT order_id, status
                     FROM order_status AS os1
                     WHERE (os1.timestamp, os1.status) IN (
                       SELECT MAX(os2.timestamp) AS latest_timestamp, MAX(os2.status) AS latest_status
                       FROM order_status AS os2
                       WHERE os2.order_id = os1.order_id
                     )
                   ) AS os ON os.order_id = od.id
    INNER JOIN 
    withdraw AS wd ON od.refWithdraw = wd.withdraw_id
    INNER JOIN 
    offer AS of ON od.refOffer = of.offer_id
    INNER JOIN 
    depart AS dp ON od.refDepart = dp.depart_id
    INNER JOIN 
    listwork AS lw ON od.refWork = lw.work_id
    INNER JOIN 
    device AS dv ON od.refDevice = dv.device_id
    LEFT JOIN 
    order_numberdevice AS nd ON od.id = nd.order_item
    LEFT JOIN 
    order_items AS oi ON od.id = oi.order_id
    LEFT JOIN 
    device_models AS dm ON oi.list = dm.models_id
    WHERE 
    (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
    AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
    ORDER BY od.id ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process data to merge `numberDevice`
    $mergedData = [];
    foreach ($data as $row) {
        $key = $row["id"] . "|" . $row["numberWork"] . "|" . $row["list"];

        if (!isset($mergedData[$key])) {
            $mergedData[$key] = $row;
        } else {
            // Merge numberDevice
            if (!str_contains($mergedData[$key]["numberDevice"], $row["numberDevice"])) {
                $mergedData[$key]["numberDevice"] .= ", " . $row["numberDevice"];
            }
        }
    }

    // Output the HTML for the table
    echo '<table style="border: 1px solid black;">';
    echo '<thead>';
    echo '<tr style="text-align:center;">';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขใบเบิก</th>';
    echo '<th style="border: 1px solid black;" scope="col">ผูกหมายเลขงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">วันที่ออกใบเบิก</th>';
    echo '<th style="border: 1px solid black;" scope="col">ประเภทการเบิก</th>';
    echo '<th style="border: 1px solid black;" scope="col">ประเภทงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">รายการอุปกรณ์</th>';
    // echo '<th style="border: 1px solid black;" scope="col">หมายเลขครุภัณฑ์</th>';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขครุภัณฑ์</th>';
    echo '<th style="border: 1px solid black;" scope="col">อาการรับแจ้ง</th>';
    echo '<th style="border: 1px solid black;" scope="col">เหตุผลและความจำเป็น</th>';
    echo '<th style="border: 1px solid black;" scope="col">หน่วยงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">ผู้รับเรื่อง</th>';
    echo '<th style="border: 1px solid black;" scope="col">ร้านที่เสนอราคา</th>';
    echo '<th style="border: 1px solid black;" scope="col">เลขที่ใบเสนอ</th>';
    echo '<th style="border: 1px solid black;" scope="col">หมายเหตุ</th>';
    echo '<th style="border: 1px solid black;" scope="col">สถานะ</th>';

    echo '<th style="border: 1px solid black;" scope="col">รายการ</th>';
    echo '<th style="border: 1px solid black;" scope="col">คุณสมบัติ</th>';
    echo '<th style="border: 1px solid black;" scope="col">จำนวน</th>';
    echo '<th style="border: 1px solid black;" scope="col">ราคา</th>';
    echo '<th style="border: 1px solid black;" scope="col">หน่วย</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Print rows while skipping duplicate IDs
    $previousId = null;
    $previousNumberWork = null;
    foreach ($mergedData as $row) {
        echo '<tr>';

        $statusTxt = $row['status'];

        switch ($statusTxt) {
            case 1:
                $statusTxtSlect = "รอรับเอกสารจากหน่วยงาน";
                break;
            case 2:
                $statusTxtSlect = "รอส่งเอกสารไปพัสดุ";
                break;
            case 3:
                $statusTxtSlect = "รอพัสดุสั่งของ";
                break;
            case 4:
                $statusTxtSlect = "รอหมายเลขครุภัณฑ์";
                break;
            case 5:
                $statusTxtSlect = "ปิดงาน";
                break;
            case 6:
                $statusTxtSlect = "ยกเลิก";
                break;
            default:
                $statusTxtSlect = "ไม่พบสถานะ";
                break;
        }

        // Print only the first occurrence of ID and numberWork, leave others blank
        if ($row["id"] !== $previousId || $row["numberWork"] !== $previousNumberWork) {
            echo '<td style="border: 1px solid black;" scope="row">' . "'" .  $row["numberWork"] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['id_ref'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['dateWithdraw'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['withdraw_name'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['work_name'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['device_name'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row["numberDevice"] . '</td>';
            // echo '<td style="border: 1px solid black;" scope="row">' . $row['numberDevice1'] . ', ' . $row['numberDevice2'] . ', ' . $row['numberDevice3'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['report'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['reason'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['depart_name'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['refUsername'] . '</td>';
            // echo '<td style="border: 1px solid black;" scope="row">' . $row['fname'] . ' ' . $row['lname'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['offer_name'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['quotation'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $row['note'] . '</td>';
            echo '<td style="border: 1px solid black;" scope="row">' . $statusTxtSlect . '</td>';
        } else {
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
            echo '<td style="border: 1px solid black;" scope="row"></td>';
        }

        echo '<td style="border: 1px solid black;" scope="row">' . $row["list_name"] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row["quality"] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row["amount"] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row["price"] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row["unit"] . '</td>';

        echo '</tr>';

        $previousId = $row["id"];
        $previousNumberWork = $row["numberWork"];
    }

    echo '</tbody>';
    echo '</table>';
}

if (isset($_POST['DataAlls'])) {
    echo '<head>';
    echo '<meta charset="UTF-8">';

    // Set headers for Excel file download
    header("Content-Type: application/xls");
    header("Content-Disposition: attachment; filename=บันทึกข้อมูลใบเบิก.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '</head>';

    // Output the HTML for the table
    echo '<table style="border: 1px solid black;">';
    echo '<thead>';
    echo '<tr style="text-align:center;">';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขใบเบิก</th>';
    echo '<th style="border: 1px solid black;" scope="col">ผูกหมายเลขงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">วันที่ออกใบเบิก</th>';
    echo '<th style="border: 1px solid black;" scope="col">ประเภทการเบิก</th>';
    echo '<th style="border: 1px solid black;" scope="col">ประเภทงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">รายการอุปกรณ์</th>';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขครุภัณฑ์</th>';
    echo '<th style="border: 1px solid black;" scope="col">อาการรับแจ้ง</th>';
    echo '<th style="border: 1px solid black;" scope="col">เหตุผลและความจำเป็น</th>';
    echo '<th style="border: 1px solid black;" scope="col">หน่วยงาน</th>';
    echo '<th style="border: 1px solid black;" scope="col">ผู้รับเรื่อง</th>';
    echo '<th style="border: 1px solid black;" scope="col">ร้านที่เสนอราคา</th>';
    echo '<th style="border: 1px solid black;" scope="col">เลขที่ใบเสนอ</th>';
    echo '<th style="border: 1px solid black;" scope="col">หมายเหตุ</th>';
    echo '<th style="border: 1px solid black;" scope="col">สถานะ</th>';
    echo '<th style="border: 1px solid black;" scope="col">รายการ</th>';
    echo '<th style="border: 1px solid black;" scope="col">คุณสมบัติ</th>';
    echo '<th style="border: 1px solid black;" scope="col">จำนวน</th>';
    echo '<th style="border: 1px solid black;" scope="col">ราคา</th>';
    echo '<th style="border: 1px solid black;" scope="col">หน่วย</th>';
    // ... (other table headers)
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $sql = "
    SELECT 
    od.*, 
    wd.withdraw_name, 
    lw.work_name, 
    dv.device_name, 
    dp.depart_name, 
    of.offer_name,
    nd.numberDevice, 
    nd.id AS numberDevice_id, 
    oi.id AS item_id, 
    oi.list, 
    oi.quality, 
    oi.amount, 
    oi.price, 
    oi.unit
    FROM 
    orderdata_new AS od
    INNER JOIN 
    withdraw AS wd ON od.refWithdraw = wd.withdraw_id
    INNER JOIN 
    offer AS of ON od.refOffer = of.offer_id
    INNER JOIN 
    depart AS dp ON od.refDepart = dp.depart_id
    INNER JOIN 
    listwork AS lw ON od.refWork = lw.work_id
    INNER JOIN 
    device AS dv ON od.refDevice = dv.device_id
    LEFT JOIN 
    order_numberdevice AS nd ON od.id = nd.order_item
    LEFT JOIN 
    order_items AS oi ON od.id = oi.order_id
    WHERE 
    (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
    AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
    ORDER BY nd.id, oi.id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        // Process the latest record and related data
        $order = $results[0]; // Fetch the main record details
        $devices = [];
        $items = [];

        foreach ($results as $row) {
            // Collect unique devices
            if (!empty($row['numberDevice_id']) && !isset($devices[$row['numberDevice_id']])) {
                $devices[$row['numberDevice_id']] =
                    [
                        'numberDevice_id' => $row['numberDevice_id'],
                        'numberDevice' => $row['numberDevice']
                    ];
            }

            // Collect unique items
            if (!empty($row['item_id']) && !isset($items[$row['item_id']])) {
                $items[$row['item_id']] = [
                    'item_id' => $row['item_id'],
                    'list' => $row['list'],
                    'quality' => $row['quality'],
                    'amount' => $row['amount'],
                    'price' => $row['price'],
                    'total' => $row['price'] * $row['amount'],
                    'unit' => $row['unit']
                ];
            }
        }

        // Reset keys for JSON encoding or numeric indexing
        $devices = array_values($devices);
        $items = array_values($items);
    } else {
        $order = [];
        $devices = [];
        $items = [];
    }


    // Fetch data from the database
    $sql = "SELECT od.*, wd.*,lw.*,dm.*,dp.*,ad.*,of.*
    FROM orderdata AS od
    LEFT JOIN withdraw AS wd ON od.refWithdraw = wd.withdraw_id
    LEFT JOIN listwork AS lw ON od.refWork = lw.work_id
    LEFT JOIN device AS dm ON od.refDevice = dm.device_id
    LEFT JOIN depart AS dp ON od.refDepart = dp.depart_id
    LEFT JOIN admin AS ad ON od.refUsername = ad.username
    LEFT JOIN offer AS of ON od.refOffer = of.offer_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output the table rows
    foreach ($result as $row) {

        $statusTxt = $row['status'];

        switch ($statusTxt) {
            case 1:
                $statusTxtSlect = "รอรับเอกสารจากหน่วยงาน";
                break;
            case 2:
                $statusTxtSlect = "รอส่งเอกสารไปพัสดุ";
                break;
            case 3:
                $statusTxtSlect = "รอพัสดุสั่งของ";
                break;
            case 4:
                $statusTxtSlect = "รอหมายเลขครุภัณฑ์";
                break;
            case 5:
                $statusTxtSlect = "ปิดงาน";
                break;
            case 6:
                $statusTxtSlect = "ยกเลิก";
                break;
            default:
                $statusTxtSlect = "ไม่พบสถานะ";
                break;
        }

        echo '<tr style="text-align: center;"';
        echo '<td style="border: 1px solid black;" scope="row">' . "'" . $row['numberWork'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['id_ref'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['dateWithdraw'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['withdraw_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['work_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['device_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['numberDevice1'] . ', ' . $row['numberDevice2'] . ', ' . $row['numberDevice3'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['report'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['reason'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['depart_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['fname'] . ' ' . $row['lname'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['offer_name'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['quotation'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $row['note'] . '</td>';
        echo '<td style="border: 1px solid black;" scope="row">' . $statusTxt . '</td>';

        // for ($i = 1; $i <= 15; $i++) {
        //     echo '<td style="border: 1px solid black;" scope="col">' . $row["list{$i}"] . '</td>';
        //     echo '<td style="border: 1px solid black;" scope="col">' . $row["quality{$i}"] . '</td>';
        //     echo '<td style="border: 1px solid black;" scope="col">' . $row["amount{$i}"] . '</td>';
        //     echo '<td style="border: 1px solid black;" scope="col">' . $row["price{$i}"] . '</td>';
        //     echo '<td style="border: 1px solid black;" scope="col">' . $row["unit{$i}"] . '</td>';
        // }

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    exit();
}

// Function to format Thai date
function toMonthThai($m)
{
    $monthNamesThai = array(
        "",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม"
    );
    return $monthNamesThai[$m];
}

function formatDateThai($date)
{
    if ($date == null || $date == "") {
        return ""; // ถ้าวันที่เป็นค่าว่างให้คืนค่าว่างเปล่า
    }

    // แปลงวันที่ในรูปแบบ Y-m-d เป็น timestamp
    $timestamp = strtotime($date);

    // ดึงปีไทย
    $yearThai = date('Y', $timestamp);

    // ดึงเดือน
    $monthNumber = date('n', $timestamp);

    // แปลงเดือนเป็นภาษาไทย
    $monthThai = toMonthThai($monthNumber);

    // ดึงวันที่
    $day = date('d', $timestamp);

    // สร้างรูปแบบวันที่ใหม่
    $formattedDate = "$day $monthThai $yearThai";

    return $formattedDate;
}
