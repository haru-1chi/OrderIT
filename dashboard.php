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
        function refreshPage() {
            location.reload();
        }

        // Set timeout to refresh the page every 1 minute (60000 milliseconds)
        setTimeout(refreshPage, 30000);
    </script>
    <style>
        body {
            background-color: #F9FDFF;
        }

        #dataAll tbody tr td {
            background-color: #fff4f5;
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
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="container">
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
        <div class="row">
            <div class="col-sm-12 col-lg-12 col-md-12">
                <h1 class="text-center my-4">สรุปยอดจำนวนงาน</h1>
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
                    <!-- </div> -->
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-5 col-sm-12 col-lg-12 col-md-12">
                    <h1>งานวันนี้</h1>
                    <div class="table-responsive">
                        <table id="dataAll" class="table table-danger">
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
                                    <th scope="col">สร้างโดย</th>
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
                    INNER JOIN depart as dt ON dp.department = dt.depart_id
                    WHERE DATE(date_report) = :dateNow
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

                                    $timeString = $row['time_report'];
                                    $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';
                                    if ($row['status'] == 0) {
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
                                            <td><?= $row['create_by'] ?></td>
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
        INNER JOIN depart as dt ON dp.department = dt.depart_id 
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
                    INNER JOIN depart as dt ON dp.department = dt.depart_id
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
                    INNER JOIN depart as dt ON dp.department = dt.depart_id
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
                    INNER JOIN depart as dt ON dp.department = dt.depart_id
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
                                    <th scope="col" style="text-align: left; width: 10%;">หมายเลขงาน</th>
                                    <th scope="col" style="text-align: left; width: 10%;">ผู้ซ่อม</th>
                                    <th scope="col" style="text-align: left; width: 50%;">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col" style="text-align: left; width: 10%;">หน่วยงาน</th>
                                    <th scope="col" style="text-align: left; width: 10%;">เวลารับงาน</th>
                                    <th scope="col" style="text-align: left; width: 10%;">เวลาปิดงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT dp.*,dt.depart_name 
                        FROM data_report as dp
                        INNER JOIN depart as dt ON dp.department = dt.depart_id";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($result as $row) {
                                    $takeTimeString = $row['take'];
                                    $closeTimeString = $row['close_date'];
                                    if (empty($closeTimeString) || $closeTimeString === '00:00:00.000000' || empty($takeTimeString) || $takeTimeString === '00:00:00.000000') {
                                        $takeFormatted = '-';
                                        $closeTimeFormatted = '-';
                                    } else {
                                        $takeFormatted = date('H:i', strtotime($takeTimeString)) . ' น.';
                                        $closeTimeFormatted = date('H:i', strtotime($closeTimeString)) . ' น.';
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
                        const ctx = document.getElementById('myChart');
                        <?php
                        $sql_statuses = "SELECT status, COUNT(*) as count FROM data_report GROUP BY status";
                        $stmt_statuses = $conn->prepare($sql_statuses);
                        $stmt_statuses->execute();
                        $statuses = $stmt_statuses->fetchAll(PDO::FETCH_ASSOC);

                        // แปลง labels และ counts จาก status
                        $status_labels = ['งานที่ยังไม่ได้รับ', 'กำลังดำเนินการ', 'รออะไหล่', 'งานที่เสร็จ'];
                        $status_counts = ['0', '0', '0', '0'];
                        foreach ($statuses as $status) {
                            $status_code = $status['status'];
                            $count = $status['count'];

                            $status_counts[$status_code - 1] = $count;
                        }

                        // แปลงอาร์เรย์ counts เป็นสตริงที่คั่นด้วยเครื่องหมายจุลภาค
                        $status_counts_str = implode(', ', $status_counts);

                        ?>
                        // Data from PHP
                        const data = {
                            labels: <?= json_encode($status_labels) ?>,
                            datasets: [{
                                label: 'จำนวนงาน',
                                data: [<?= $status_counts_str ?>],

                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                ],
                                borderWidth: 1
                            }]
                        };

                        const options = {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        };

                        new Chart(ctx, {
                            type: 'bar',
                            data: data,
                            options: options
                        });
                        console.log('Labels:', data.labels);
                        console.log('Data:', data.datasets[0].data);
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
                        ] // adjust the column index as needed
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