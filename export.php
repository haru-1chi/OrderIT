<?php
// Set the file encoding to UTF-8
header('Content-Type: text/html; charset=utf-8');

require_once 'config/db.php';

// Start the session if not already started
session_start();

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

// Set the file encoding to UTF-8
header('Content-Type: text/html; charset=utf-8');

require_once 'config/db.php';

// Start the session if not already started
session_start();

// Check if the user is logged in
if (isset($_SESSION['admin_log'])) {
    $admin = $_SESSION['admin_log'];
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

    // Output the HTML for the table
    echo '<table style="border: 1px solid black;">';
    echo '<thead>';
    echo '<tr style="text-align:center;">';
    echo '<th style="border: 1px solid black;" scope="col">หมายเลขออกงาน</th>';
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
    for ($i = 1; $i <= 15; $i++) {
        echo '<th style="border: 1px solid black;" scope="col">รายการลำดับที่ ' . $i . '</th>';
        echo '<th style="border: 1px solid black;" scope="col">คุณสมบัติลำดับที่ ' . $i . '</th>';
        echo '<th style="border: 1px solid black;" scope="col">จำนวนลำดับที่ ' . $i . '</th>';
        echo '<th style="border: 1px solid black;" scope="col">ราคาลำดับที่ ' . $i . '</th>';
        echo '<th style="border: 1px solid black;" scope="col">หน่วยลำดับที่ ' . $i . '</th>';
    }
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
    (od.numberWork = :numberWork) AND
    (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
    AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
    ORDER BY nd.id, oi.id
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":numberWork", $numberWork);
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

        for ($i = 1; $i <= 15; $i++) {
            echo '<td style="border: 1px solid black;" scope="col">' . $row["list{$i}"] . '</td>';
            echo '<td style="border: 1px solid black;" scope="col">' . $row["quality{$i}"] . '</td>';
            echo '<td style="border: 1px solid black;" scope="col">' . $row["amount{$i}"] . '</td>';
            echo '<td style="border: 1px solid black;" scope="col">' . $row["price{$i}"] . '</td>';
            echo '<td style="border: 1px solid black;" scope="col">' . $row["unit{$i}"] . '</td>';
        }

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
