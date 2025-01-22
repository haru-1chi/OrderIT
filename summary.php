<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
$dateNow = new DateTime();
$dateNow->modify("+543 years");

$dateThai = $dateNow->format("Y/m/d");

if (isset($_SESSION['admin_log'])) {
    $admin = $_SESSION['admin_log'];
    $sql = "SELECT * FROM admin WHERE username = :admin";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":admin", $admin);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
}

?>
<!doctype html>
<html lang="en">

<head>
    <title>ระบบบริหารจัดการ ศูนย์บริการซ่อมคอมพิวเตอร์</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap CSS v5.2.1 -->
    <?php bs5() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script>
        // Function to reload the page
        // function refreshPage() {
        //     location.reload();
        // }

        // // Set timeout to refresh the page every 1 minute (60000 milliseconds)
        // setTimeout(refreshPage, 30000);
    </script>
    <style>
        body {
            background-color: #F9FDFF;
        }

        #dataAll tbody tr td {
            background-color: #f2f7ff;
            color: #000;
        }

        #inTime tbody tr td {
            background-color: #fffbf0;
            color: #000;
        }

        #dataAllNOTTAKE tbody tr td {
            background-color: #fff4f5;
            color: #000;
        }

        #clam tbody tr td {
            background-color: #fff4f5;
            color: #000;
        }

        #wait tbody tr td {
            background-color: #f2f7ff;
            color: #000;
        }

        #success tbody tr td {
            background-color: #f3fffa;
            color: #000;
        }

        #rating tbody tr td {
            background-color: #fffbf0;
            color: #000;
        }
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="">
        <?php if (isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger" role="alert">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['warning'])) { ?>
            <div class="alert alert-warning" role="alert">
                <?php
                echo $_SESSION['warning'];
                unset($_SESSION['warning']);
                ?>
            </div>
        <?php } ?>

        <?php if (isset($_SESSION['success'])) { ?>
            <div class="alert alert-success" role="alert">
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php } ?>

        <div class="d-flex">
            <div class="card p-3 m-4" style="width: 500px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>สรุปยอดการรับงาน</p>
                    <select id="filterDropdown" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <canvas id="pairedChart" style="width: 100%; height: 100%;"></canvas>
            </div>
            <div class="card p-3 mt-4" style="width: 700px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>แนวโน้มงานรายวัน</p>
                    <select id="filterDropdown1" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <canvas id="lineChart" style="width: 100%; height: 100%;"></canvas>
            </div>

            <div class="card p-3 ms-4 me-4 mt-4">
                <p>สรุปความพึงพอใจ</p>
                <div style="width: 600px; height: 100px;">
                    <canvas id="serviceBarChart" style="width: 100%; height: 100%;"></canvas>
                    <canvas id="solvingBarChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 col-lg-12 col-md-12">
                <h1 class="text-center my-4">อยู่ในระหว่างการสร้างหน้าเว็บนี้...</h1>
                <div class="row d-flex justify-content-center">
                    <?php
                    $sql = "SELECT status, COUNT(*) as count FROM data_report GROUP BY status";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $statusOptions = array(
                        0 => array(
                            'text' => "งานที่ยังไม่ได้รับ",
                            'color' => "#FF7575"
                        ),
                        2 => array(
                            'text' => "กำลังดำเนินงาน",
                            'color' => "#F8BF24"
                        ),
                        3 => array(
                            'text' => "รออะไหล่",
                            'color' => "#659BFF"
                        ),
                        4 => array(
                            'text' => "เสร็จงาน",
                            'color' => "#6CC668"
                        ),
                        5 => array(
                            'text' => "ส่งซ่อม",
                            'color' => "#D673D3"
                        ),
                        6 => array(
                            'text' => "รอกรอกรายละเอียด",
                            'color' => "#6CC668"
                        ),

                    );
                    foreach ($statusCounts as $statusCount) {
                        $status = $statusCount['status'];
                        $count = $statusCount['count'];

                        $textS = isset($statusOptions[$status]['text']) ? $statusOptions[$status]['text'] : "ไม่ระบุสถานะ";
                        $color = isset($statusOptions[$status]['color']) ? $statusOptions[$status]['color'] : sprintf('#%06X', rand(0, 0xFFFFFF));

                    ?>
                        <div class="col-sm-2">
                            <div class="rounded-3 text-white ps-3 pb-2" style="max-width: 18rem; background-color: <?= $color ?>">
                                <div class="card-header">
                                    <ion-icon name="people-outline"></ion-icon>
                                    <div class="d-flex align-items-end">
                                        <p style="font-size: 45px; margin: 0px;"><?= $count ?></p>
                                        <p class="ms-2" style="font-size: 32px; margin: 0px; margin-bottom:.4rem;">งาน</p>
                                    </div>
                                    <p style="font-size: 20px; margin: 0px;"><?= $textS ?> </p>

                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-5 col-sm-12 col-lg-12 col-md-12">
                    <h1>รายการความพึงพอใจ</h1>
                    <div class="table-responsive">
                        <table id="dataAll" class="table table-primary">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">ช่องทางที่ใช้บริการ</th>
                                    <th scope="col">ปัญหาได้รับการแก้ไข</th>
                                    <th scope="col">ความรวดเร็ว</th>
                                    <th scope="col">การแก้ปัญหา</th>
                                    <th scope="col">การให้บริการ</th>
                                    <th scope="col">ข้อเสนอแนะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT *
                    FROM rating
                    ";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {
                                ?>
                                    <tr>
                                        <td><?= $row['service_channel'] ?></td>
                                        <td><?= $row['issue_resolved'] ?></td>
                                        <td><?= $row['service_speed'] ?></td>
                                        <td><?= $row['problem_satisfaction'] ?></td>
                                        <td><?= $row['service_satisfaction'] ?></td>
                                        <td><?= $row['suggestion'] ?></td>
                                    </tr>
                                <?php }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>กำลังดำเนินการ</h1>
                        <table id="inTime" class="table table-warning">
                            <thead>
                                <tr>
                                    <th scope="col">หมายเลข</th>
                                    <th scope="col">ผู้ซ่อม</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col">เวลาแจ้ง</th>
                                    <th scope="col">เวลารับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT dp.id, dp.device, dp.report, dp.time_report, dp.take, dt.depart_name, adm.fname, adm.lname,dp.deviceName
        FROM data_report as dp
        LEFT JOIN depart as dt ON dp.department = dt.depart_id 
        INNER JOIN admin as adm ON dp.username = adm.username
        WHERE dp.status = 2
        ORDER BY dp.id DESC";

                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($result as $row) {
                                    $timeString = $row['time_report'];
                                    $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                                    $takeTimeString = $row['take'];
                                    if (empty($takeTimeString) || $takeTimeString === '00:00:00.000000') {
                                        $takeFormatted = '-';
                                    } else {
                                        $takeFormatted = date('H:i', strtotime($takeTimeString)) . ' น.';
                                    }
                                ?>
                                    <tr class="text-center">
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['fname'] . ' ' . $row['lname'] ?></td>
                                        <td><?= $row['deviceName'] ?></td>
                                        <td><?= $row['report'] ?></td>
                                        <td><?= $row['depart_name'] ?></td>
                                        <td><?= $timeFormatted ?></td>
                                        <td><?= $takeFormatted ?></td>
                                    </tr>
                                <?php
                                }

                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <h1>งานที่ถูกบันทึกไว้</h1>
                    <hr>

                    <div class="table-responsive">
                        <table id="dataAllNOTTAKE" class="table table-danger">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">หมายเลข</th>
                                    <th scope="col">วันที่</th>
                                    <th scope="col">เวลาแจ้ง</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">ผู้แจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col">เบอร์ติดต่อกลับ</th>
                                    <th scope="col">ปุ่มรับงาน</th>
                                </tr>
                            </thead>
                            <tbody>


                                <?php
                                // function toMonthThai($m)
                                // {
                                //     $monthNamesThai = array(
                                //         "",
                                //         "มกราคม",
                                //         "กุมภาพันธ์",
                                //         "มีนาคม",
                                //         "เมษายน",
                                //         "พฤษภาคม",
                                //         "มิถุนายน",
                                //         "กรกฎาคม",
                                //         "สิงหาคม",
                                //         "กันยายน",
                                //         "ตุลาคม",
                                //         "พฤศจิกายน",
                                //         "ธันวาคม"
                                //     );
                                //     return $monthNamesThai[$m];
                                // }

                                // function formatDateThai($date)
                                // {
                                //     if ($date == null || $date == "") {
                                //         return ""; // ถ้าวันที่เป็นค่าว่างให้คืนค่าว่างเปล่า
                                //     }

                                //     // แปลงวันที่ในรูปแบบ Y-m-d เป็น timestamp
                                //     $timestamp = strtotime($date);

                                //     // ดึงปีไทย
                                //     $yearThai = date('Y', $timestamp);

                                //     // ดึงเดือน
                                //     $monthNumber = date('n', $timestamp);

                                //     // แปลงเดือนเป็นภาษาไทย
                                //     $monthThai = toMonthThai($monthNumber);

                                //     // ดึงวันที่
                                //     $day = date('d', $timestamp);

                                //     // สร้างรูปแบบวันที่ใหม่
                                //     $formattedDate = "$day $monthThai $yearThai";

                                //     return $formattedDate;
                                // }

                                $sql = "SELECT dp.*,dt.depart_name 
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    WHERE DATE(date_report) <> :dateNow
                    ";
                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(":dateNow", $dateThai);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                $i = 0;
                                foreach ($result as $row) {
                                    $i++;
                                    // $dateWithdrawFromDB = $row['date_report'];

                                    // $dateWithdrawThai = formatDateThai($dateWithdrawFromDB);

                                    $dateString = $row['date_report'];
                                    $timestamp = strtotime($dateString);
                                    $dateFormatted = date('d/m/Y', $timestamp);

                                    if ($row['status'] == 0) {
                                        $timeString = $row['time_report'];
                                        $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';
                                ?>
                                        <tr>
                                            <td scope="row"><?= $row['id'] ?></td>
                                            <td scope="row"><?= $dateFormatted ?></td>
                                            <td><?= $timeFormatted ?></td>
                                            <td><?= $row['deviceName'] ?></td>
                                            <td><?= $row['report'] ?></td>
                                            <td><?= $row['reporter'] ?></td>
                                            <td><?= $row['depart_name'] ?></td>
                                            <td><?= $row['tel'] ?></td>
                                            <td>
                                                <?php
                                                if (!$row['username']) { ?>
                                                    <form action="system/insert.php" method="post">
                                                        <input type="hidden" name="username" value="<?= $admin ?>">
                                                        <input type="hidden" name="take" class="time_report" value="<?= $currentTime ?>">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" name="takeaway" class="btn btn-primary">รับงาน</button>
                                                    </form>
                                                <?php } else { ?>
                                                    <form action="system/insert.php" method="post">
                                                        <input type="hidden" name="username" value="<?= $admin ?>">
                                                        <input type="hidden" name="take" class="time_report" value="<?= $currentTime ?>">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" name="takeaway" class="btn btn-primary">รับงาน</button>
                                                    </form>
                                                <?php  }
                                                ?>
                                            </td>
                                        </tr>
                                <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>ส่งซ่อม</h1>
                        <hr>
                        <table id="clam" class="table table-danger">
                            <thead>
                                <tr>
                                    <th scope="col">หมายเลขงาน</th>
                                    <th scope="col">ผู้ซ่อม</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col">เวลาแจ้ง</th>
                                    <th scope="col">เวลารับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $sql = "SELECT dp.*,dt.depart_name 
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    ";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($result as $row) {
                                    $timeString = $row['time_report'];
                                    $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                                    $takeTimeString = $row['take'];
                                    if (empty($takeTimeString) || $takeTimeString === '00:00:00.000000') {
                                        $takeFormatted = '-';
                                    } else {
                                        $takeFormatted = date('H:i', strtotime($takeTimeString)) . ' น.';
                                    }
                                    if ($row['status'] == 5) {

                                ?>
                                        <tr class="text-center">
                                            <td><?= $row['id'] ?></td>
                                            <td>
                                                <?php
                                                $sql = "SELECT * FROM admin";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->execute();
                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($result as $row2) {
                                                    if ($row['username'] == $row2['username']) {
                                                        echo $row2['fname'] . ' ' . $row2['lname'];
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td><?= $row['deviceName'] ?></td>
                                            <td><?= $row['report'] ?></td>
                                            <td><?= $row['depart_name'] ?></td>
                                            <td><?= $timeFormatted ?></td>
                                            <td><?= $takeFormatted ?></td>

                                        </tr>
                                <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                </div>


                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>รออะไหล่</h1>
                        <hr>
                        <table id="wait" class="table table-primary">
                            <thead>
                                <tr>
                                    <th scope="col">หมายเลขงาน</th>
                                    <th scope="col">ผู้ซ่อม</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col">เวลาแจ้ง</th>
                                    <th scope="col">เวลารับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT dp.*,dt.depart_name 
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    ";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($result as $row) {
                                    $timeString = $row['time_report'];
                                    $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                                    $takeTimeString = $row['take'];
                                    if (empty($takeTimeString) || $takeTimeString === '00:00:00.000000') {
                                        $takeFormatted = '-';
                                    } else {
                                        $takeFormatted = date('H:i', strtotime($takeTimeString)) . ' น.';
                                    }
                                    if ($row['status'] == 3) {
                                ?>
                                        <tr class="text-center">
                                            <td><?= $row['id'] ?></td>
                                            <td>
                                                <?php
                                                $sql = "SELECT * FROM admin";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->execute();
                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($result as $row2) {
                                                    if ($row['username'] == $row2['username']) {
                                                        echo $row2['fname'] . ' ' . $row2['lname'];
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td><?= $row['deviceName'] ?></td>
                                            <td><?= $row['report'] ?></td>
                                            <td><?= $row['depart_name'] ?></td>
                                            <td><?= $timeFormatted ?></td>
                                            <td><?= $takeFormatted ?></td>

                                        </tr>
                                <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                </div>


                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>งานที่เสร็จ</h1>
                        <form method="post" action="export.php">
                            <button name="actAll" class="btn btn-primary" type="submit">Export->Excel</button>
                        </form>
                        <hr>
                        <table id="success" class="table table-success">
                            <thead>
                                <tr>
                                    <th style="text-align: left;">หมายเลขงาน</th>
                                    <th style="text-align: left; width: 170px;">ผู้ซ่อม</th>
                                    <th style="text-align: left;">อาการที่ได้รับแจ้ง</th>
                                    <th style="text-align: left; width: 170px;">หน่วยงาน</th>
                                    <th style="text-align: left; width: 170px;">SLA</th>
                                    <th style="text-align: left; width: 120px;">ตัวชี้วัด</th>
                                    <th style="text-align: left; width: 80px;">เวลาแจ้ง</th>
                                    <th style="text-align: left; width: 80px;">เวลารับงาน</th>
                                    <th style="text-align: left; width: 80px;">เวลาปิดงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT dp.*,dt.depart_name 
                        FROM data_report as dp
                        LEFT JOIN depart as dt ON dp.department = dt.depart_id";
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
                                        $takeFormatted = date('H:i', strtotime($takeTimeString)) . 'น.';
                                        $closeTimeFormatted = date('H:i', strtotime($closeTimeString)) . 'น.';
                                    }
                                    if ($row['status'] == 4) {
                                ?>
                                        <tr class="text-left">
                                            <td><?= $row['id'] ?></td>
                                            <td>
                                                <?php
                                                $sql = "SELECT * FROM admin";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->execute();
                                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                foreach ($result as $row2) {
                                                    if ($row['username'] == $row2['username']) {
                                                        echo $row2['fname'] . ' ' . $row2['lname'];
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td><?= $row['report'] ?></td>
                                            <td><?= $row['depart_name'] ?></td>
                                            <td><?= $row['sla'] ?></td>
                                            <td><?= $row['kpi'] ?></td>
                                            <td><?= $reportFormatted ?></td>
                                            <td><?= $takeFormatted ?></td>
                                            <td><?= $closeTimeFormatted ?></td>
                                        </tr>
                                <?php }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Fetch data from PHP endpoint
                        fetch('fetch_service_channel.php')
                            .then(response => response.json())
                            .then(response => {
                                // Response should have 'labels' and 'data' arrays
                                const serviceLabels = response.labels;
                                const serviceData = response.data;

                                // Prepare datasets dynamically based on response
                                const datasets = serviceLabels.map((label, index) => ({
                                    label: label,
                                    data: [serviceData[index]], // Stacking requires an array per group
                                    backgroundColor: getColor(index, 0.5), // Adjust transparency
                                    borderColor: getColor(index, 1),
                                    borderWidth: 1,
                                }));

                                // Chart data
                                const data = {
                                    labels: ['ช่องทางการให้บริการ'],
                                    datasets: datasets,
                                };

                                // Create the Chart
                                const ctx = document.getElementById('serviceBarChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: data,
                                    options: {
                                        indexAxis: 'y', // Makes it horizontal
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top',
                                                align: 'end',
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: (tooltipItem) =>
                                                        `${tooltipItem.dataset.label}: ${tooltipItem.raw}%`,
                                                },
                                            },
                                        },
                                        scales: {
                                            x: {
                                                stacked: true, // Enables stacked bars
                                                title: {
                                                    display: true,
                                                },
                                                ticks: {
                                                    beginAtZero: true,
                                                    stepSize: 10,
                                                },
                                            },
                                            y: {
                                                stacked: true, // Enables stacked bars
                                            },
                                        },
                                    },
                                });
                            })
                            .catch(error => console.error('Error fetching data:', error));
                    });

                    // Function to generate colors
                    function getColor(index, alpha) {
                        const colors = [
                            `rgba(255, 99, 132, ${alpha})`,
                            `rgba(75, 192, 192, ${alpha})`,
                            `rgba(54, 162, 235, ${alpha})`,
                            `rgba(255, 206, 86, ${alpha})`,
                            `rgba(153, 102, 255, ${alpha})`,
                        ];
                        return colors[index % colors.length];
                    }
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Fetch data from PHP endpoint
                        fetch('fetch_solving.php')
                            .then(response => response.json())
                            .then(response => {
                                // Response should have 'labels' and 'data' arrays
                                const serviceLabels = response.labels;
                                const serviceData = response.data;

                                // Prepare datasets dynamically based on response
                                const datasets = serviceLabels.map((label, index) => ({
                                    label: label,
                                    data: [serviceData[index]], // Stacking requires an array per group
                                    backgroundColor: getColor(index, 0.5), // Adjust transparency
                                    borderColor: getColor(index, 1),
                                    borderWidth: 1,
                                }));

                                // Chart data
                                const data = {
                                    labels: ['ปัญหาได้รับการแก้ไข'],
                                    datasets: datasets,
                                };

                                // Create the Chart
                                const ctx = document.getElementById('solvingBarChart').getContext('2d');
                                new Chart(ctx, {
                                    type: 'bar',
                                    data: data,
                                    options: {
                                        indexAxis: 'y', // Makes it horizontal
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'top',
                                                align: 'end',
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: (tooltipItem) =>
                                                        `${tooltipItem.dataset.label}: ${tooltipItem.raw}%`,
                                                },
                                            },
                                        },
                                        scales: {
                                            x: {
                                                stacked: true, // Enables stacked bars
                                                title: {
                                                    display: true,
                                                },
                                                ticks: {
                                                    beginAtZero: true,
                                                    stepSize: 10,
                                                },
                                            },
                                            y: {
                                                stacked: true, // Enables stacked bars
                                            },
                                        },
                                    },
                                });
                            })
                            .catch(error => console.error('Error fetching data:', error));
                    });

                    // Function to generate colors
                    function getColor(index, alpha) {
                        const colors = [
                            `rgba(255, 99, 132, ${alpha})`,
                            `rgba(75, 192, 192, ${alpha})`,
                            `rgba(54, 162, 235, ${alpha})`,
                            `rgba(255, 206, 86, ${alpha})`,
                            `rgba(153, 102, 255, ${alpha})`,
                        ];
                        return colors[index % colors.length];
                    }
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const charts = [{
                                canvasId: 'pairedChart',
                                dropdownId: 'filterDropdown',
                                endpoint: 'fetch_staff.php',
                                type: 'bar',
                                datasets: (data) => [{
                                        label: 'จำนวนรับงาน',
                                        data: data.taker_counts,
                                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 1,
                                    },
                                    {
                                        label: 'จำนวนคีย์งาน',
                                        data: data.creator_counts,
                                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 1,
                                    },
                                ],
                            },
                            {
                                canvasId: 'lineChart',
                                dropdownId: 'filterDropdown1',
                                endpoint: 'fetch_count_task.php',
                                type: 'line',
                                datasets: (data) => [{
                                    label: 'จำนวนงาน',
                                    data: data.task_counts,
                                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    fill: false,
                                    tension: 0.2,
                                }, ],
                            },
                        ];

                        function fetchData(endpoint, filter) {
                            return fetch(endpoint, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        filter
                                    }),
                                })
                                .then((response) => {
                                    if (!response.ok) throw new Error('Network error');
                                    return response.json();
                                })
                                .catch((error) => console.error('Error fetching data:', error));
                        }

                        function createChart(canvas, type, data, datasetsFn) {
                            return new Chart(canvas, {
                                type: type,
                                data: {
                                    labels: data.labels,
                                    datasets: datasetsFn(data),
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            display: true,
                                            position: 'top'
                                        },
                                    },
                                    scales: {
                                        x: {
                                            title: {
                                                display: true
                                            }
                                        },
                                        y: {
                                            beginAtZero: true
                                        },
                                    },
                                },
                            });
                        }

                        charts.forEach(({
                            canvasId,
                            dropdownId,
                            endpoint,
                            type,
                            datasets
                        }) => {
                            const canvas = document.getElementById(canvasId);
                            const dropdown = document.getElementById(dropdownId);
                            let chartInstance;

                            function updateChart(filter) {
                                fetchData(endpoint, filter).then((data) => {
                                    if (!chartInstance) {
                                        chartInstance = createChart(canvas, type, data, datasets);
                                    } else {
                                        chartInstance.data.labels = data.labels;
                                        chartInstance.data.datasets = datasets(data);
                                        chartInstance.update();
                                    }
                                });
                            }

                            dropdown.addEventListener('change', () => updateChart(dropdown.value));
                            updateChart('day');
                        });
                    });
                </script>
                <hr>
            </div>
            <br>
            <script>
                // Get the current date and time
                const now = new Date();

                // Format the time as HH:mm
                const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

                // Set the current time as the default value for the input fields
                const timeReportInputs = document.querySelectorAll('.time_report');
                timeReportInputs.forEach(input => input.value = currentTime);
            </script>
            <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('#dataAll').DataTable({
                        order: [
                            [0, 'asc']
                        ] // assuming you want to sort the first column in ascending order
                    });
                    $('#dataAllNOTTAKE').DataTable({
                        order: [
                            [0, 'asc']
                        ] // assuming you want to sort the first column in ascending order
                    });

                    $('#dataAllTAKE').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });

                    $('#inTime').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });

                    $('#wait').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });

                    $('#success').DataTable({
                        order: [
                            [0, 'desc']
                        ],
                        columnDefs: [{
                                targets: 0,
                                width: "auto"
                            }, // หมายเลขงาน
                            {
                                targets: 1,
                                width: "170px"
                            }, // ผู้ซ่อม
                            {
                                targets: 2,
                                width: "auto"
                            }, // อาการที่ได้รับแจ้ง
                            {
                                targets: 3,
                                width: "170px"
                            }, // หน่วยงาน
                            {
                                targets: 4,
                                width: "170px"
                            }, // SLA
                            {
                                targets: 5,
                                width: "120px"
                            }, // ตัวชี้วัด
                            {
                                targets: 6,
                                width: "80px"
                            }, // เวลาแจ้ง
                            {
                                targets: 7,
                                width: "80px"
                            }, // เวลารับงาน
                            {
                                targets: 8,
                                width: "80px"
                            }, // เวลาปิดงาน
                        ],
                        scrollX: true, // Allow horizontal scrolling if necessary
                        paging: true, // Enable pagination
                        searching: true,
                        autoWidth: false
                    });
                    $('#clam').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });
                });
            </script>
            <footer class="mt-5 footer mt-auto py-3" style="background: #fff;">

                <marquee class="font-thai" style="font-weight: bold; font-size: 1rem"><span class="text-muted text-center">Design website by นายอภิชน ประสาทศรี , พุฒิพงศ์ ใหญ่แก้ว &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Coding โดย นายอานุภาพ ศรเทียน &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ควบคุมโดย นนท์ บรรณวัฒน์ นักวิชาการคอมพิวเตอร์ ปฏิบัติการ</span>
                </marquee>

            </footer>
            <?php SC5() ?>


</body>

</html>