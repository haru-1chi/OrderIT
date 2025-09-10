<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
date_default_timezone_set("Asia/Bangkok");

if (isset($_SESSION['admin_log'])) {
    $admin = $_SESSION['admin_log'];
    $sql = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admin WHERE username = :admin";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":admin", $admin);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $name = $result['full_name'];
}
if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
}

?>
<!doctype html>
<html lang="en">

<head>
    <title>งานของฉัน | ระบบบริหารจัดการ ศูนย์บริการซ่อมคอมพิวเตอร์</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS v5.2.1 -->
    <?php bs5() ?>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #F9FDFF;
        }

        #dataAll tbody tr td {
            background-color: #f2f7ff;
            color: #000;
        }

        #dataAllUncomplete tbody tr td {
            background-color: #f2f7ff;
            color: #000;
        }

        .ui-autocomplete {
            z-index: 1055 !important;
        }

        .modal {
            background-color: rgba(0, 0, 0, 0.5);
        }

        .job-modal {
            width: 500px;
            transition: width 0.3s ease;
        }

        .job-modal.wide {
            width: 750px;
        }

        .job-modal-content {
            width: 500px;
        }

        .withdraw-modal {
            width: 850px;
            height: fit-content;
        }

        .overlay-modal {
            width: 600px;
            height: fit-content;
        }

        @keyframes shrinkExpand {

            0%,
            100% {
                transform: scale(1);
            }

            25% {
                transform: scale(0.95);
            }

            50% {
                transform: scale(1.05);
            }

            75% {
                transform: scale(0.98);
            }
        }

        .giggle {
            animation: shrinkExpand 0.3s ease-in-out;
        }

        .choices {
            margin-bottom: 0 !important;
        }

        .choices__inner {
            border-radius: 0.375rem !important;
            min-height: 33px !important;
            border: 1px solid #ced4da;
            padding: 0 !important;
            background-color: #fff !important;
            font-size: 1rem !important;
            line-height: 1.5;
        }

        .choices__inner.is-invalid {
            border-color: #dc3545 !important;
        }

        .choices__list--single {
            padding: 0 !important;
        }

        .choices__list--dropdown {
            border-radius: 0.375rem;
            border: 1px solid #ced4da;
        }

        .choices__item--selectable {
            padding: 0.375rem 0.75rem;
        }

        .choices.is-focused .choices__inner,
        .choices.is-open .choices__inner {
            border-color: #86b7fe !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, .25) !important;
        }
    </style>
</head>

<body>
    <?php navbar() ?>
    <div class="container-fluid">
        <h1 class="text-center my-4">งานของฉัน</h1>
        <div class="card rounded-4 shadow-sm p-3 mt-5 col-sm-12 col-lg-12 col-md-12">
            <div class="table-responsive">
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

                <?php
                if (isset($_POST['checkDate_my_work'])) {
                    // Get values from form
                    $status = $_POST['status_my_work'];
                    $dateStart = $_POST['dateStart_my_work'];
                    $dateEnd = $_POST['dateEnd_my_work'];

                    // แปลงวันที่เริ่มต้นเป็น พ.ศ.
                    $yearStart = date("Y", strtotime($dateStart));
                    $yearEnd = date("Y", strtotime($dateEnd));

                    $dateStart_buddhist = $yearStart . "-" . date("m-d", strtotime($dateStart));
                    $dateEnd_buddhist = $yearEnd . "-" . date("m-d", strtotime($dateEnd));

                    $sql = "SELECT dp.*, dt.depart_name 
                        FROM data_report AS dp
                        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
                        WHERE dp.username = :username ";

                    if ($status !== "") {
                        $sql .= " AND status = :status";
                    }

                    $sql .= "   AND date_report BETWEEN :dateStart AND :dateEnd
                        ORDER BY dp.id DESC";

                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":username", $admin);
                    if ($status !== "") {
                        $stmt->bindParam(":status", $status);
                    }
                    $stmt->bindParam(":dateStart", $dateStart_buddhist);
                    $stmt->bindParam(":dateEnd", $dateEnd_buddhist);
                } else {
                    // If checkDate is not set, retrieve all records without date range filter
                    $dateEnd = date('Y-m-d');
                    $dateStart = date('Y-m-d', strtotime('-3 days', strtotime($dateEnd)));

                    $yearStart = date("Y", strtotime($dateStart));
                    $yearEnd = date("Y", strtotime($dateEnd));

                    $dateStart_buddhist = $yearStart . "-" . date("m-d", strtotime($dateStart));
                    $dateEnd_buddhist = $yearEnd . "-" . date("m-d", strtotime($dateEnd));

                    $sql = "SELECT dp.*, dt.depart_name 
                        FROM data_report AS dp
                        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
                        WHERE dp.username = :username AND status = 2
                        ORDER BY dp.id DESC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":username", $admin);
                }

                // Prepare and execute the SQL query
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);


                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <form method="post" action="system_1/export.php">
                            <button name="act" class="btn btn-primary" type="submit">Export->Excel</button>
                        </form>
                    </div>

                    <form action="" method="post">
                        <div class="d-flex gap-4">
                            <select class="form-select" name="status_my_work" id="numberWork" style="width: 250px;">
                                <option value="2" <?php if (isset($status) == 2 && $status == 2)
                                                        echo "selected"; ?>>
                                    กำลังดำเนินการ</option>
                                <option value="3" <?php if (isset($status) == 3 && $status == 3)
                                                        echo "selected"; ?>>
                                    เบิกอะไหล่</option>
                                <option value="5" <?php if (isset($status) == 5 && $status == 5)
                                                        echo "selected"; ?>>ส่งซ่อม
                                </option>
                                <option value="4" <?php if (isset($status) == 4 && $status == 4)
                                                        echo "selected"; ?>>
                                    เสร็จสิ้น</option>
                                <option value="" <?php if (isset($status) && $status == '')
                                                        echo "selected"; ?>>
                                    ทั้งหมด</option>
                            </select>

                            <input type="date" value="<?= isset($dateStart) ? $dateStart : ''; ?>" name="dateStart_my_work"
                                class="form-control" style="width: 250px;">
                            <input type="date" value="<?= isset($dateEnd) ? $dateEnd : ''; ?>" name="dateEnd_my_work"
                                class="form-control" style="width: 250px;">
                            <button type="submit" name="checkDate_my_work" class="btn btn-primary">ยืนยัน</button>
                        </div>
                    </form>
                </div>

                <hr>

                <table id="dataAll" class="table table-primary">
                    <thead>
                        <tr>
                            <th class="text-center" scope="col">ลำดับ</th>
                            <th scope="col">วันที่</th>
                            <th scope="col">เวลา</th>
                            <th scope="col">รูปแบบการทำงาน</th>
                            <th scope="col">หมายเลขครุภัณฑ์</th>
                            <th scope="col">อาการที่ได้รับแจ้ง</th>
                            <th scope="col">ผู้แจ้ง</th>
                            <th scope="col">หน่วยงาน</th>
                            <th scope="col">เบอร์โทร</th>
                            <th scope="col">ระดับความเร่งด่วน</th>
                            <th scope="col">วันปิดงาน</th>
                            <th scope="col">เวลาปิดงาน</th>
                            <th scope="col">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $row) {
                            $dateString = $row['date_report'];
                            $timestamp = strtotime($dateString);
                            $dateFormatted = date('d/m/Y', $timestamp);

                            if (empty($row['close_time']) || $row['close_time'] === '00:00:00.000000') {
                                $closeDateFormatted = '-';
                            } else {
                                $closeDateFormatted = date('d/m/Y', strtotime($row['close_time']));
                            }

                            $timeString = $row['time_report'];
                            $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                            $closeTimeString = $row['close_date'];
                            if (empty($closeTimeString) || $closeTimeString === '00:00:00.000000') {
                                $closeTimeFormatted = '-';
                            } else {
                                $closeTimeFormatted = date('H:i', strtotime($closeTimeString)) . ' น.';
                            }
                        ?>
                            <tr>
                                <td class="text-start" scope="row"><?= $row['id'] ?></td>
                                <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                                <td class="text-start"><?= $timeFormatted ?></td>
                                <td class="text-start"><?= $row['device'] ?></td>
                                <td class="text-start"><?= $row['number_device'] ?></td>
                                <td class="text-start"><?= $row['report'] ?></td>
                                <td class="text-start"><?= $row['reporter'] ?></td>
                                <td class="text-start"><?= $row['depart_name'] ?></td>
                                <td class="text-start"><?= $row['tel'] ?></td>
                                <?php
                                $priorityLabels = [
                                    4 => "🔴เร่งด่วน",
                                    3 => "🟡กลาง",
                                    2 => "🔵ปกติ",
                                    1 => "⏰งานประจำวัน"
                                ];
                                ?>
                                <td class="text-start">
                                    <?= $priorityLabels[$row['priority']] ?? '-' ?>
                                </td>
                                <td class="text-start"><?= $closeDateFormatted ?></td>
                                <td class="text-start"><?= $closeTimeFormatted ?></td>
                                <?php
                                if ($row['status'] == 1) {
                                    $statusText = "ยังไม่ได้ดำเนินการ";
                                } else if ($row['status'] == 2) {
                                    $statusText = "กำลังดำเนินการ";
                                } else if ($row['status'] == 3) {
                                    $statusText = "รออะไหล่" . ' ' . $row['withdraw'];
                                } else if ($row['status'] == 4) {
                                    $statusText = "เสร็จสิ้น" . ' ' . $row['withdraw'];
                                } else if ($row['status'] == 5) {
                                    $statusText = "ส่งซ่อม";
                                } else if ($row['status'] == 6) {
                                    $statusText = "รอกรอกรายละเอียด" . ' ' . $row['withdraw'];
                                }
                                ?>
                                <td>

                                    <?php if ($row['status'] == 1) { ?>
                                        <button type="submit" name="inTime"
                                            style=" background-color: orange;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary">เริ่มดำเนินการ</button>
                                    <?php } else if ($row['status'] == 2) { ?>
                                        <button type="button"
                                            style="background-color: orange;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 3) { ?>
                                        <button type="button"
                                            style=" background-color: blue;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 4) { ?>
                                        <button type="button"
                                            style=" background-color: green;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 5) { ?>
                                        <button type="button"
                                            style=" background-color: #D673D3;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"><?= $statusText ?></button>
                                    <?php } else if ($row['status'] == 6) { ?>
                                        <button type="button"
                                            style=" background-color: green;color:white;border: 1px solid orange"
                                            class="btn mb-3 btn-primary" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"><?= $statusText ?></button>
                                    <?php } ?>

                                    <form action="system/insert.php" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <!-- modal -->
                                        <div id="workflowModalTask<?= $row['id'] ?>" class="modal" style="display: none;">
                                            <div class="p-5 d-flex justify-content-center gap-4">
                                                <div class="modal-content job-modal" id="job-modal-main-<?= $row['id'] ?>">
                                                    <div class="modal-header justify-content-between">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                                                        <div class="d-flex align-items-center">
                                                            <button type="button" class="btn btn-primary me-2" id="toggleAssignSectionBtn-main-<?= $row['id'] ?>">เพิ่มเจ้าหน้าที่ร่วมงาน</button>
                                                            <button type="button" class="btn-close" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"></button>
                                                        </div>
                                                    </div>
                                                    <div class="modal-body d-flex">
                                                        <div class="job-modal-content">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <label>หมายเลขงาน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['id'] ?>" disabled>
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>วันที่แจ้ง</label>
                                                                    <input type="date" class="form-control" name="date_report"
                                                                        value="<?= $row['date_report'] ?>">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>วันที่ปิดงาน</label>
                                                                    <input type="date" class="form-control" name="close_time"
                                                                        value="<?= $row['close_time'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <label>เวลาแจ้ง</label>
                                                                    <input type="time" class="form-control" name="time_report"
                                                                        value="<?= date('H:i', strtotime($row['time_report'])) ?>">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>เวลารับงาน</label>
                                                                    <input type="time" class="form-control" name="take"
                                                                        value="<?= date('H:i', strtotime($row['take']))  ?>">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>เวลาปิดงาน (ถ้ามี)</label>
                                                                    <input type="time" class="form-control" id="time_report" name="close_date"
                                                                        value="<?= ($row['status'] == 3 && ($row['close_date'] === '00:00:00.000000' || $row['close_date'] === null || trim($row['close_date']) === ''))
                                                                                    ? '' : (($row['close_date'] && $row['close_date'] !== '00:00:00.000000') ? date('H:i', strtotime($row['close_date'])) : '') ?>">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>ประเภทงาน</label>
                                                                    <select name="work_type" class="form-select work-type">
                                                                        <option value="" <?= empty($row['work_type']) ? 'selected' : '' ?>>เลือก...</option>
                                                                        <option value="incident" <?= ($row['work_type'] === 'incident') ? 'selected' : '' ?>>อุบัติการณ์</option>
                                                                        <option value="อื่นๆ" <?= ($row['work_type'] === 'อื่นๆ') ? 'selected' : '' ?>>อื่นๆ</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-6">
                                                                    <label>ระดับความเร่งด่วน</label>
                                                                    <select name="priority" class="form-select priority">
                                                                        <option value="" <?= empty($row['priority']) ? 'selected' : '' ?>>เลือก...</option>
                                                                        <option value="4" <?= ($row['priority'] == 4) ? 'selected' : '' ?>>🔴เร่งด่วน</option>
                                                                        <option value="3" <?= ($row['priority'] == 3) ? 'selected' : '' ?>>🟡กลาง</option>
                                                                        <option value="2" <?= ($row['priority'] == 2) ? 'selected' : '' ?>>🔵ปกติ</option>
                                                                        <option value="1" <?= ($row['priority'] == 1) ? 'selected' : '' ?>>⏰งานประจำวัน</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>ผู้แจ้ง</label>
                                                                    <input type="text" class="form-control" name="reporter"
                                                                        value="<?= $row['reporter'] ?>">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>หน่วยงาน</label>
                                                                    <?php
                                                                    $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute([$row['department']]);
                                                                    $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <select class="form-select" name="department" id="departId<?= $row['id'] ?>" required>
                                                                        <option value="<?= $row['department'] ?>" selected><?= $departRow['depart_name'] ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>เบอร์ติดต่อกลับ</label>
                                                                    <input type="text" class="form-control" name="tel"
                                                                        value="<?= $row['tel'] ?>">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="deviceInput">อุปกรณ์</label>
                                                                    <select class="form-select" id="deviceInput<?= $row['id'] ?>" name="deviceName" required>
                                                                        <option value="<?= $row['deviceName'] ?>" selected><?= $row['deviceName'] ?></option>
                                                                    </select>
                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                    <input value="<?= $row['number_device'] ?>" type="text"
                                                                        class="form-control" name="number_devices" id="numberDeviceSource-main-<?= $row['id'] ?>">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>หมายเลข IP addrees</label>
                                                                    <input type="text" class="form-control" name="ip_address"
                                                                        value="<?= $row['ip_address'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>อาการที่ได้รับแจ้ง</label>
                                                                    <input type="text" class="form-control" name="report_work"
                                                                        value="<?= $row['report'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>รูปแบบการทำงาน<span style="color: red;">*</span></label>
                                                                    <select class="form-select" name="device"
                                                                        aria-label="Default select example">
                                                                        <option value="<?= $row['device'] ?: '' ?>"
                                                                            selected>
                                                                            <?= !empty($row['device']) ? $row['device'] : '-' ?>
                                                                        </option>
                                                                        <?php
                                                                        $sql = "SELECT * FROM workinglist";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($checkD as $d) {
                                                                            if ($d['workingName'] != $row['device']) {
                                                                        ?>
                                                                                <option value="<?= $d['workingName'] ?>">
                                                                                    <?= $d['workingName'] ?>
                                                                                </option>
                                                                        <?php }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>หมายเลขใบเบิก</label>
                                                                    <?php if (empty($row['withdraw'])) { ?>
                                                                        <input disabled type="text"
                                                                            class="form-control withdrawInput" name="withdraw"
                                                                            id="withdrawInput<?= $row['id'] ?>">
                                                                    <?php } else { ?>
                                                                        <input disabled value="<?= $row['withdraw'] ?>"
                                                                            type="text" class="form-control withdrawInput"
                                                                            name="withdraw" id="withdrawInput<?= $row['id'] ?>">
                                                                        <input type="hidden" value="<?= $row['withdraw'] ?>"
                                                                            class="form-control withdrawInput"
                                                                            id="withdrawInputHidden<?= $row['id'] ?>" name="withdraw2">
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <label>รายละเอียด<span style="color: red;">*</span></label>
                                                                        <?php
                                                                        // Fetch images for this report
                                                                        $sql = "SELECT filename FROM images_table WHERE report_id = :report_id";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->bindParam(':report_id', $row['id'], PDO::PARAM_INT);
                                                                        $stmt->execute();
                                                                        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        if ($images):
                                                                        ?>
                                                                            <!-- Button to open modal -->
                                                                            <button type="button" class="btn btn-link btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal<?= $row['id'] ?>">
                                                                                🖼️ดูรูปภาพ
                                                                            </button>

                                                                            <!-- Modal -->
                                                                            <div class="modal fade" id="imageModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-body">
                                                                                            <div id="carouselImages<?= $row['id'] ?>" class="carousel slide carousel-dark">
                                                                                                <div class="carousel-inner">
                                                                                                    <?php foreach ($images as $key => $img): ?>
                                                                                                        <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                                                                                                            <div class="d-flex justify-content-center">
                                                                                                                <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" class="d-block" style="max-height:500px; max-width:100%;">
                                                                                                            </div>
                                                                                                            <div class="d-flex justify-content-center mt-3">
                                                                                                                <button type="button" class="btn btn-danger btn-sm delete-image"
                                                                                                                    data-filename="<?= htmlspecialchars($img['filename']) ?>"
                                                                                                                    data-report-id="<?= $row['id'] ?>">
                                                                                                                    ลบรูปภาพนี้
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>

                                                                                                    <?php endforeach; ?>
                                                                                                </div>
                                                                                                <?php if (count($images) > 1): ?>
                                                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages<?= $row['id'] ?>" data-bs-slide="prev">
                                                                                                        <span class="carousel-control-prev-icon"></span>
                                                                                                    </button>
                                                                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselImages<?= $row['id'] ?>" data-bs-slide="next">
                                                                                                        <span class="carousel-control-next-icon"></span>
                                                                                                    </button>
                                                                                                <?php endif; ?>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <script>
                                                                                document.addEventListener('click', function(e) {
                                                                                    if (e.target && e.target.classList.contains('delete-image')) {
                                                                                        const buttonElement = e.target;
                                                                                        const filename = buttonElement.getAttribute('data-filename');
                                                                                        const reportId = buttonElement.getAttribute('data-report-id');

                                                                                        Swal.fire({
                                                                                            title: 'ยืนยันการลบ?',
                                                                                            text: "คุณต้องการลบรูปภาพนี้หรือไม่",
                                                                                            icon: 'warning',
                                                                                            showCancelButton: true,
                                                                                            confirmButtonColor: '#d33',
                                                                                            cancelButtonColor: '#3085d6',
                                                                                            confirmButtonText: 'ใช่, ลบเลย',
                                                                                            cancelButtonText: 'ยกเลิก'
                                                                                        }).then((result) => {
                                                                                            if (result.isConfirmed) {
                                                                                                fetch('system_1/delete_image.php', {
                                                                                                        method: 'POST',
                                                                                                        headers: {
                                                                                                            'Content-Type': 'application/x-www-form-urlencoded'
                                                                                                        },
                                                                                                        body: `filename=${encodeURIComponent(filename)}&report_id=${encodeURIComponent(reportId)}`
                                                                                                    })
                                                                                                    .then(response => response.json())
                                                                                                    .then(data => {
                                                                                                        if (data.status === 'success') {
                                                                                                            Swal.fire({
                                                                                                                title: 'ลบแล้ว!',
                                                                                                                text: 'รูปภาพถูกลบเรียบร้อย',
                                                                                                                icon: 'success',
                                                                                                                timer: 1200,
                                                                                                                showConfirmButton: false
                                                                                                            }).then(() => {
                                                                                                                const carouselItem = buttonElement.closest('.carousel-item');
                                                                                                                const carouselInner = carouselItem.parentElement;

                                                                                                                if (carouselItem.classList.contains('active')) {
                                                                                                                    let nextItem = carouselItem.nextElementSibling || carouselItem.previousElementSibling;
                                                                                                                    if (nextItem) {
                                                                                                                        nextItem.classList.add('active');
                                                                                                                    }
                                                                                                                }

                                                                                                                carouselItem.remove();

                                                                                                                // ✅ handle empty case
                                                                                                                if (carouselInner.children.length === 0) {
                                                                                                                    const placeholder = document.createElement('div');
                                                                                                                    placeholder.classList.add('carousel-item', 'active');
                                                                                                                    placeholder.innerHTML = `
                                        <div class="d-flex justify-content-center">
                                            <img src="image/Image-not-found.png" class="d-block" style="max-height:500px; max-width:100%;">
                                        </div>
                                    `;
                                                                                                                    carouselInner.appendChild(placeholder);

                                                                                                                    const modal = buttonElement.closest('.modal');
                                                                                                                    modal.querySelectorAll('.carousel-control-prev, .carousel-control-next').forEach(btn => btn.style.display = 'none');
                                                                                                                    modal.querySelectorAll('.delete-image').forEach(btn => btn.style.display = 'none');
                                                                                                                }
                                                                                                            });
                                                                                                        } else {
                                                                                                            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถลบรูปภาพได้', 'error');
                                                                                                        }
                                                                                                    });
                                                                                            }
                                                                                        });
                                                                                    }
                                                                                });
                                                                            </script>
                                                                        <?php endif; ?>
                                                                    </div>

                                                                    <textarea class="form-control" name="description" rows="2" id="descriptionSource-main-<?= $row['id'] ?>"><?= $row['description'] ?></textarea>
                                                                    <input
                                                                        class="form-control mt-2"
                                                                        type="file"
                                                                        id="formFileMultiple"
                                                                        name="images[]"
                                                                        multiple
                                                                        accept="image/*">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>หมายเหตุ</label>
                                                                    <input value="<?= $row['note'] ?>" type="text"
                                                                        class="form-control" name="noteTask">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>ผู้คีย์งาน</label>
                                                                    <input value="<?= $row['create_by'] ?>" type="text"
                                                                        class="form-control" name="create_by" disabled>
                                                                    <input value="<?= $row['create_by'] ?>" type="hidden"
                                                                        class="form-control" name="create_by">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>ซ่อมครั้งที่</label>
                                                                    <?php
                                                                    if (!empty($row['number_device']) && $row['number_device'] !== '-') {
                                                                        $sqlCount = "SELECT COUNT(*) AS repair_count 
                     FROM data_report 
                     WHERE number_device = :number_device 
                     AND (number_device IS NOT NULL AND number_device <> '' AND number_device <> '-')";
                                                                        $stmtCount = $conn->prepare($sqlCount);
                                                                        $stmtCount->bindParam(":number_device", $row['number_device']);
                                                                        $stmtCount->execute();
                                                                        $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
                                                                        $repairCount = $count['repair_count'];
                                                                    } else {
                                                                        $repairCount = '-';
                                                                    }
                                                                    ?>
                                                                    <input value="<?= $repairCount ?>" type="text" class="form-control" name="repair_count">
                                                                </div>
                                                            </div>

                                                            <hr class="mb-2">
                                                            <!-- !!!!! -->
                                                            <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>ปัญหาอยู่ใน SLA หรือไม่<span style="color: red;">*</span></label>
                                                                    <select class="form-select" name="sla"
                                                                        aria-label="Default select example">
                                                                        <option value="<?= $row['sla'] ?: '' ?>" selected>
                                                                            <?= !empty($row['sla']) ? $row['sla'] : '-' ?>
                                                                        </option>
                                                                        <?php
                                                                        $sql = "SELECT * FROM sla";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($checkD as $d) {
                                                                            if ($d['sla_name'] != $row['sla']) {
                                                                        ?>
                                                                                <option value="<?= $d['sla_name'] ?>">
                                                                                    <?= $d['sla_name'] ?>
                                                                                </option>
                                                                        <?php }
                                                                        }
                                                                        ?>

                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>เป็นตัวชี้วัดหรือไม่<span style="color: red;">*</span></label>
                                                                    <?php
                                                                    $sqlPublic = "SELECT kpi_name FROM kpi WHERE kpi_id IN (1, 2)";
                                                                    $stmt = $conn->prepare($sqlPublic);
                                                                    $stmt->execute();
                                                                    $publicKpis = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                                                    // 2. Assigned KPIs
                                                                    $sqlAssigned = "SELECT DISTINCT kpi.kpi_name 
                FROM kpi 
                INNER JOIN kpi_assignment 
                ON kpi.kpi_id = kpi_assignment.kpi_id 
                WHERE kpi.kpi_id NOT IN (1, 2) AND kpi_assignment.username = ?";
                                                                    $stmt = $conn->prepare($sqlAssigned);
                                                                    $stmt->execute([$admin]);
                                                                    $assignedKpis = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                                                    // 3. Merge both lists, keeping order
                                                                    $allKpis = array_merge($publicKpis, $assignedKpis);
                                                                    ?>

                                                                    <select class="form-select" name="kpi" aria-label="Default select example">
                                                                        <option value="<?= $row['kpi'] ?: '' ?>" selected>
                                                                            <?= !empty($row['kpi']) ? $row['kpi'] : '-' ?>
                                                                        </option>
                                                                        <?php foreach ($allKpis as $kpiName): ?>
                                                                            <?php if ($kpiName != $row['kpi']): ?>
                                                                                <option value="<?= $kpiName ?>"><?= $kpiName ?></option>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </select>

                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>Activity Report<span style="color: red;">*</span></label>
                                                                    <select class="form-select" name="problem"
                                                                        aria-label="Default select example">
                                                                        <?php
                                                                        $sql = "SELECT * FROM problemlist";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                                                                        <option value="<?= $row['problem'] ?: '' ?>"
                                                                            selected>
                                                                            <?= !empty($row['problem']) ? $row['problem'] : '-' ?>
                                                                        </option>
                                                                        <?php foreach ($data as $d) {
                                                                            if ($row['problem'] != $d['problemName']) { ?>
                                                                                <option value="<?= $d['problemName'] ?>">
                                                                                    <?= $d['problemName'] ?>
                                                                                </option>
                                                                        <?php }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div id="assignSection-main-<?= $row['id'] ?>" style="display: none; width: 210px;" class="ms-3">
                                                                <h6 class="mb-1">เพิ่มเจ้าหน้าที่ร่วมงาน</h6>
                                                                <?php
                                                                $assignedStmt = $conn->prepare("SELECT username FROM admin");
                                                                $assignedStmt->execute();
                                                                $allAdmins = $assignedStmt->fetchAll(PDO::FETCH_ASSOC);

                                                                $currentAdmin = $_SESSION['admin_log'] ?? null;
                                                                $assignedTask = array_filter($allAdmins, fn($admin) => $admin['username'] !== $currentAdmin);

                                                                $reportName = $row['report'];
                                                                $checkAssignedStmt = $conn->prepare("SELECT username FROM data_report WHERE report = :report AND date_report = :date_report AND time_report = :time_report");
                                                                $checkAssignedStmt->bindParam(":report", $reportName);
                                                                $checkAssignedStmt->bindParam(":date_report", $row['date_report']);
                                                                $checkAssignedStmt->bindParam(":time_report", $row['time_report']);
                                                                $checkAssignedStmt->execute();
                                                                $alreadyAssigned = $checkAssignedStmt->fetchAll(PDO::FETCH_COLUMN);
                                                                ?>
                                                                <div class="col-sm-12 mb-3">
                                                                    <div class="list-group">
                                                                        <label class="list-group-item">
                                                                            <input type="checkbox" class="form-check-input me-1" id="toggleAssignedTask-main-<?= $row['id'] ?>">
                                                                            เลือกทั้งหมด
                                                                        </label>
                                                                        <?php foreach ($assignedTask as $task): ?>
                                                                            <?php
                                                                            $username = $task['username'];
                                                                            $isAssigned = in_array($username, $alreadyAssigned);
                                                                            ?>
                                                                            <label class="list-group-item">
                                                                                <input
                                                                                    class="form-check-input me-1"
                                                                                    type="checkbox"
                                                                                    name="assignedTask[]"
                                                                                    value="<?= htmlspecialchars($username) ?>"
                                                                                    <?= $isAssigned ? 'disabled checked' : '' ?>>
                                                                                <?= htmlspecialchars($username) ?>
                                                                                <?php if ($isAssigned): ?>
                                                                                    <span class="text-muted">(เพิ่มแล้ว)</span>
                                                                                <?php endif; ?>
                                                                            </label>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer" style="justify-content: space-between; border: none;">
                                                        <button type="submit" class="btn btn-danger"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            name="disWork">คืนงาน</button>
                                                        <button type="submit" class="btn btn-dark"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            name="cancelWork">ยกเลิก</button>
                                                        <button type="button" class="btn btn-primary" onclick="toggleModal('#requisitionModal<?= $row['id'] ?>')">เบิก/ส่งซ่อม</button>
                                                        <button type="submit" class="btn btn-primary"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            name="Bantext">บันทึก</button>
                                                        <button type="submit" name="CloseSubmit"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            class="btn btn-success">ปิดงาน</button>
                                                    </div>
                                                </div>

                                                <?php
                                                // Check if records exist in `orderdata_new` for the current `id`
                                                $checkQuery = "SELECT * FROM orderdata_new WHERE id_ref = :id_ref";
                                                $checkStmt = $conn->prepare($checkQuery);
                                                $checkStmt->bindParam(":id_ref", $row['id']);
                                                $checkStmt->execute();
                                                $requisitionData = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                                                $hasRequisition = count($requisitionData) > 0;
                                                ?>

                                                <div id="requisitionModal<?= $row['id'] ?>" class="modal-content withdraw-modal" style="display: none;">
                                                    <div class="modal-header" style="background-color: <?= $hasRequisition ? '#F8BF24' : '#cfe2ff'; ?>;">
                                                        <?php if ($hasRequisition): ?>
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">แก้ไขใบเบิก</h1>
                                                        <?php else: ?>
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">สร้างใบเบิก</h1>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn-close" onclick="toggleModal('#requisitionModal<?= $row['id'] ?>')"></button>
                                                    </div>
                                                    <div class="p-3">
                                                        <div class="row">
                                                            <input type="hidden" name="id_ref" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <?php if ($hasRequisition) {
                                                                foreach ($requisitionData as $rowData) { ?>
                                                                    <input type="hidden" name="withdraw_id" value="<?= htmlspecialchars($rowData['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                                    <div class="col-sm-12">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">เลขใบเบิก</label>
                                                                            <input type="text" class="form-control" value="<?= $rowData['numberWork'] ?>" disabled>
                                                                            <input type="hidden" name="numberWork" value="<?= $rowData['numberWork'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                            <input required type="date" name="dateWithdraw" class="form-control" value="<?= $rowData['dateWithdraw'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                            <select required class="form-select" name="refWithdraw" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM withdraw';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($withdraws as $withdraw) {
                                                                                    $selected = ($withdraw['withdraw_id'] == $rowData['refWithdraw']) ? 'selected' : ''; ?>
                                                                                    <option value="<?= $withdraw['withdraw_id'] ?>" <?= $selected ?>><?= $withdraw['withdraw_name'] ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                            <select required class="form-select" name="refWork" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM listwork';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($listworks as $listwork) {
                                                                                    $selected = ($listwork['work_id'] == $rowData['refWork']) ? 'selected' : '';
                                                                                ?>
                                                                                    <option value="<?= $listwork['work_id'] ?>" <?= $selected ?>><?= $listwork['work_name'] ?></option>
                                                                                <?php }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">รายการอุปกรณ์</label>
                                                                            <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM device';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($devices as $device) {
                                                                                    $isSelected = ($device['device_id'] === $rowData['refDevice']) ? 'selected' : '';
                                                                                ?>
                                                                                    <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                                                                                        <?= $device['device_name'] ?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                            <div id="device-number-container-main-<?= $row['id'] ?>">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM order_numberdevice WHERE order_item = :order_item AND is_deleted = 0';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->bindParam(':order_item', $rowData['id'], PDO::PARAM_INT);
                                                                                $stmt->execute();
                                                                                $numberDevices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                if (!empty($numberDevices)) {
                                                                                    $isFirst = true;
                                                                                    foreach ($numberDevices as $device) { ?>
                                                                                        <div class="d-flex device-number-row">
                                                                                            <input type="text" name="update_number_device[<?= $row['id'] ?>][<?= $device['id'] ?>]" class="form-control mb-2" value="<?= htmlspecialchars($device['numberDevice']) ?>">
                                                                                            <button type="button" class="btn btn-warning p-2 mb-2 ms-3 remove-field"
                                                                                                data-device-id="<?= $device['id'] ?>"
                                                                                                data-row-id="main-<?= $row['id'] ?>"
                                                                                                style="visibility: <?= $isFirst ? 'hidden' : 'visible' ?>;">ลบ</button>
                                                                                        </div>
                                                                                    <?php
                                                                                        $isFirst = false;
                                                                                    }
                                                                                } else { ?>
                                                                                    <div class="d-flex device-number-row">
                                                                                        <input type="text" name="number_device[<?= $row['id'] ?>][]" class="form-control mb-2" value="<?= isset($row['number_device']) ? $row['number_device'] : '' ?>">
                                                                                        <button type="button" class="btn btn-danger p-2 ms-3 remove-field" style="visibility: hidden;">ลบ</button>
                                                                                    </div>
                                                                                <?php } ?>
                                                                            </div>
                                                                            <div class="d-flex justify-content-end">
                                                                                <button type="button" id="add-device-number-main-<?= $row['id'] ?>" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label for="departInput">หน่วยงาน</label>
                                                                            <?php
                                                                            $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute([$rowData['refDepart']]);
                                                                            $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <select class="form-select" name="depart_id" id="departId<?= $row['id'] ?>" required>
                                                                                <option value="<?= $rowData['refDepart'] ?>" selected><?= $departRow['depart_name'] ?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                            </label>
                                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                                                                            <input type="hidden" name="ref_username" value="<?= htmlspecialchars($name) ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">อาการรับแจ้ง </label>
                                                                            <input type="text" name="report" class="form-control" value="<?= $rowData['report'] ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                            <input type="text" name="reason" class="form-control" value="<?= $rowData['reason'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ร้านที่เสนอราคา</label>
                                                                            <select required class="form-select" name="refOffer" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM offer';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                foreach ($offers as $offer) {
                                                                                    $selected = ($offer['offer_id'] == $rowData['refOffer']) ? 'selected' : '';
                                                                                ?>
                                                                                    <option value="<?= $offer['offer_id'] ?>" <?= $selected ?>><?= $offer['offer_name'] ?></option>
                                                                                <?php }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                            </label>
                                                                            <input type="text" name="quotation" class="form-control" value="<?= $rowData['quotation'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">สถานะ</label>
                                                                            <select required class="form-select" name="status" id="inputGroupSelect01" disabled>
                                                                                <?php
                                                                                // Fetch the latest status for the current order
                                                                                $statusSql = "SELECT status FROM order_status 
                          WHERE order_id = :order_id 
                          ORDER BY timestamp DESC LIMIT 1";
                                                                                $statusStmt = $conn->prepare($statusSql);
                                                                                $statusStmt->bindParam(':order_id', $rowData['id'], PDO::PARAM_INT);
                                                                                $statusStmt->execute();
                                                                                $latestStatus = $statusStmt->fetchColumn();

                                                                                // Define status options
                                                                                $statusOptions = [
                                                                                    1 => "รอรับเอกสารจากหน่วยงาน",
                                                                                    2 => "รอส่งเอกสารไปพัสดุ",
                                                                                    3 => "รอพัสดุสั่งของ",
                                                                                    4 => "รอหมายเลขครุภัณฑ์",
                                                                                    5 => "ปิดงาน",
                                                                                    6 => "ยกเลิก"
                                                                                ];

                                                                                // Loop through and render the options
                                                                                foreach ($statusOptions as $key => $label) {
                                                                                    $selected = ($latestStatus == $key) ? "selected" : "";
                                                                                    echo "<option value=\"$key\" $selected>$label</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                       
                                                                    </div>
                                                                </div> -->

                                                                    <div class="col-sm-12">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">หมายเหตุ
                                                                            </label>
                                                                            <input type="text" name="note" class="form-control" value="<?= $rowData['note'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <?php
                                                                    $orderId = $rowData['id'];
                                                                    $sql = "SELECT * FROM order_items WHERE order_id = :order_id AND is_deleted = 0";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                                                                    $stmt->execute();
                                                                    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <div class="d-flex justify-content-end align-items-center my-2">
                                                                        <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-main-<?= $row['id'] ?>" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                                                                    </div>
                                                                    <table id="pdf" style="width: 100%;" class="table">
                                                                        <thead class="table-primary">
                                                                            <tr class="text-center" style="text-align:center;">
                                                                                <th scope="col">ลำดับ</th>
                                                                                <th scope="col">รายการ</th>
                                                                                <th scope="col">คุณสมบัติ</th>
                                                                                <th scope="col">จำนวน</th>
                                                                                <th scope="col">ราคา</th>
                                                                                <th scope="col">รวม</th>
                                                                                <th scope="col">หน่วย</th>
                                                                                <th scope="col"></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="table-body-main-<?= $row['id'] ?>">
                                                                            <?php
                                                                            $rowNumber = 1;
                                                                            $isFirstRow = true;
                                                                            foreach ($orderItems as $item) { //สร้าง case ถ้า orderItems is null
                                                                            ?>
                                                                                <tr class="text-center">
                                                                                    <th scope="row"><?= $rowNumber++; ?></th>
                                                                                    <td>
                                                                                        <select style="width: 150px; margin: 0 auto;" class="form-select device-select" name="update_list[<?= $row['id'] ?>][<?= $item['id'] ?>]" data-row="1">
                                                                                            <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                            <!-- Populate options dynamically -->
                                                                                            <?php
                                                                                            $deviceSql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                            $deviceStmt = $conn->prepare($deviceSql);
                                                                                            $deviceStmt->execute();
                                                                                            $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                            foreach ($devices as $device) {
                                                                                                $selected = $device['models_id'] == $item['list'] ? 'selected' : '';
                                                                                                echo "<option value='{$device['models_id']}' $selected>{$device['models_name']}</option>";
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </td>
                                                                                    <td><textarea rows="2" maxlength="60" name="update_quality[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control"><?= htmlspecialchars($item['quality']); ?></textarea></td>
                                                                                    <td><input style="width: 3rem; margin: 0 auto;" type="text" name="update_amount[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control" value="<?= htmlspecialchars($item['amount']); ?>"></td>
                                                                                    <td><input style="width: 5rem; margin: 0 auto;" type="text" name="update_price[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control" value="<?= htmlspecialchars($item['price']); ?>"></td>
                                                                                    <td><input disabled value="" style="width: 5rem; margin: 0 auto;" type="text" class="form-control no-toggle"></td>
                                                                                    <td><input style="width: 4rem; margin: 0 auto;" type="text" name="update_unit[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control" value="<?= htmlspecialchars($item['unit']); ?>"></td>
                                                                                    <td><button type="button" class="btn btn-warning remove-row"
                                                                                            data-items-id="<?= $item['id'] ?>"
                                                                                            data-items-row-id="<?= $row['id'] ?>"
                                                                                            style="visibility: <?= $isFirstRow ? 'hidden' : 'visible' ?>;">ลบ</button></td>
                                                                                </tr>
                                                                            <?php
                                                                                $isFirstRow = false;
                                                                            } ?>
                                                                        </tbody>
                                                                    </table>
                                                                    <div class="d-flex justify-content-end">
                                                                        <button type="button" id="add-row-main-<?= $row['id'] ?>" class="btn btn-success">+ เพิ่มแถว</button>
                                                                    </div>

                                                                <?php } ?>
                                                                <div class="w-100 d-flex justify-content-center">
                                                                    <?php if ($row['status'] == 4 || $row['status'] == 6): ?>
                                                                        <button type="submit" name="backto_calm" class="w-100 btn btn-warning mt-3 me-3">ย้อนสถานะรออะไหล่</button>
                                                                    <?php endif; ?>
                                                                    <button type="submit" name="save_with_work" class="w-100 btn btn-primary mt-3">อัพเดตข้อมูล</button>
                                                                </div>
                                                            <?php } else { ?>
                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                        <input required type="date" name="dateWithdraw" class="form-control thaiDateInput">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                        <select required class="form-select" name="refWithdraw" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM withdraw';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($withdraws as $withdraw) { ?>

                                                                                <option value="<?= $withdraw['withdraw_id'] ?>"><?= $withdraw['withdraw_name'] ?></option>
                                                                            <?php }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                        <select required class="form-select" name="refWork" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM listwork';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($listworks as $listwork) { ?>

                                                                                <option value="<?= $listwork['work_id'] ?>"><?= $listwork['work_name'] ?></option>
                                                                            <?php }
                                                                            ?>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">รายการอุปกรณ์</label>

                                                                        <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM device';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($devices as $device) {
                                                                                $isSelected = ($device['device_name'] === $row['deviceName']) ? 'selected' : '';
                                                                            ?>
                                                                                <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                                                                                    <?= $device['device_name'] ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                        <div id="device-number-container-main-<?= $row['id'] ?>">
                                                                            <div class="d-flex device-number-row">
                                                                                <input type="text" name="number_device[<?= $row['id'] ?>][]" class="form-control"
                                                                                    value="<?= isset($row['number_device']) ? $row['number_device'] : '' ?>">
                                                                                <button type="button" class="btn btn-danger p-2 ms-3" style="visibility: hidden;">ลบ</button>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <button type="button" id="add-device-number-main-<?= $row['id'] ?>" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label for="departInput">หน่วยงาน</label>
                                                                        <?php
                                                                        $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute([$row['department']]);
                                                                        $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                        ?>
                                                                        <select class="form-select" name="depart_id" id="departId<?= $row['id'] ?>" required>
                                                                            <option value="<?= $row['department'] ?>" selected><?= $departRow['depart_name'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                        </label>
                                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                                                                        <input type="hidden" name="ref_username" value="<?= htmlspecialchars($name) ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                        <input type="text" name="report" class="form-control" value="<?= $row['report'] ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                        <input type="text" name="reason" class="form-control" id="reasonTarget-main-<?= $row['id'] ?>" value="<?= $row['description'] ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ร้านที่เสนอราคา
                                                                        </label>
                                                                        <select required class="form-select" name="refOffer" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM offer';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                            foreach ($offers as $offer) { ?>
                                                                                <option value="<?= $offer['offer_id'] ?>" <?= $offer['offer_id'] == 11 ? 'selected' : '' ?>><?= $offer['offer_name'] ?></option>
                                                                            <?php }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                        </label>
                                                                        <input value="-" type="text" name="quotation" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">สถานะ
                                                                        </label>
                                                                        <select required class="form-select" name="status" id="inputGroupSelect01" disabled>
                                                                            <option value="1">รอรับเอกสารจากหน่วยงาน</option>
                                                                            <option value="2">รอส่งเอกสารไปพัสดุ</option>
                                                                            <option value="3">รอพัสดุสั่งของ</option>
                                                                            <option value="4">รอหมายเลขครุภัณฑ์</option>
                                                                            <option value="5">ปิดงาน</option>
                                                                            <option value="6">ยกเลิก</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                       
                                                                    </div>
                                                                </div> -->

                                                                <div class="col-sm-12">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">หมายเหตุ
                                                                        </label>
                                                                        <input value="-" type="text" name="note" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex justify-content-end align-items-center my-2">
                                                                    <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-main-<?= $row['id'] ?>" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                                                                </div>
                                                                <table id="pdf" style="width: 100%;" class="table">
                                                                    <thead class="table-primary">
                                                                        <tr class="text-center" style="text-align:center;">
                                                                            <th scope="col">ลำดับ</th>
                                                                            <th scope="col">รายการ</th>
                                                                            <th scope="col">คุณสมบัติ</th>
                                                                            <th scope="col">จำนวน</th>
                                                                            <th scope="col">ราคา</th>
                                                                            <th scope="col">รวม</th>
                                                                            <th scope="col">หน่วย</th>
                                                                            <th scope="col"></th>
                                                                        </tr>
                                                                    </thead>

                                                                    <tbody id="table-body-main-<?= $row['id'] ?>">
                                                                        <tr class="text-center">
                                                                            <th scope="row">1</th>
                                                                            <td>
                                                                                <select style="width: 150px; margin: 0 auto;" class="form-select device-select" name="list[<?= $row['id'] ?>][]" data-row="1">
                                                                                    <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                    <!-- Populate options dynamically -->
                                                                                    <?php
                                                                                    $deviceSql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                    $deviceStmt = $conn->prepare($deviceSql);
                                                                                    $deviceStmt->execute();
                                                                                    $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                    foreach ($devices as $device) {
                                                                                    ?>
                                                                                        <option value="<?= $device['models_id'] ?>"><?= $device['models_name'] ?></option>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                            <td><textarea rows="2" maxlength="60" name="quality[<?= $row['id'] ?>][]" class="form-control"></textarea></td>
                                                                            <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[<?= $row['id'] ?>][]" class="form-control"></td>
                                                                            <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[<?= $row['id'] ?>][]" class="form-control"></td>
                                                                            <td><input disabled value="" style="width: 5rem;" type="text" class="form-control no-toggle"></td>
                                                                            <td><input style="width: 4rem; margin: 0 auto;" type="text" name="unit[<?= $row['id'] ?>][]" class="form-control"></td>
                                                                            <td><button type="button" class="btn btn-danger" style="visibility: hidden;">ลบ</button></td>
                                                                        </tr>

                                                                    </tbody>

                                                                </table>

                                                                <div class="d-flex justify-content-end">
                                                                    <button type="button" id="add-row-main-<?= $row['id'] ?>" class="btn btn-success">+ เพิ่มแถว</button>
                                                                </div>

                                                                <input type="hidden" name="submit_with_work" value="1">

                                                                <div class="w-100 d-flex justify-content-center">
                                                                    <button data-id="submit-main-<?= $row['id'] ?>" type="submit" name="submit_with_work" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                                                                </div>

                                                            <?php }
                                                            ?>

                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Modal will show when found duplicate numberDevices in table---------------------------------------------------------------- -->
                                                <div id="overlayModalTask-main-<?= $row['id'] ?>" class="modal" style="display: none;">
                                                    <div class="p-5 d-flex justify-content-center gap-4">
                                                        <div class="modal-content overlay-modal">
                                                            <div class="modal-header">
                                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">พบรายการที่เคยเบิกไปแล้ว</h1>
                                                                <button type="button" class="btn-close"
                                                                    onclick="toggleModal('#overlayModalTask-main-<?= $row['id'] ?>')">
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <h5>หมายเลขครุภัณฑ์ <strong class="text-danger" id="duplicateAssetNumber"></strong> เคยเบิกไปแล้ว</h5>

                                                                    <div class="col-sm-12">
                                                                        <div class="btn-group my-2" role="group" aria-label="Order toggle button group" id="orderRadioGroup"></div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                            <input type="date" name="dateWithdraw" class="form-control" disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-2">
                                                                            <label id="basic-addon1">หมายเลขครุภัณฑ์</label>

                                                                            <div class="d-flex device-number-row">
                                                                                <input type="text" class="form-control" disabled>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-2">
                                                                            <label for="inputGroupSelect01">รายการอุปกรณ์</label>
                                                                            <input type="text" class="form-control" name="device_name" disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-2">
                                                                            <label for="departInput">หน่วยงาน</label>
                                                                            <input type="text" class="form-control" name="depart_name" disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-12">
                                                                        <div class="mb-2">
                                                                            <label for="inputGroupSelect01">หมายเหตุ
                                                                            </label> <input type="text" name="note" class="form-control" disabled>
                                                                        </div>
                                                                    </div>

                                                                    <div class="d-flex justify-content-end align-items-center mb-2">
                                                                        <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-sub-main-<?= $row['id'] ?>" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                                                                    </div>
                                                                    <table id="pdf" style="width: 100%;" class="table mb-4">
                                                                        <thead class="table-primary">
                                                                            <tr class="text-center">
                                                                                <th scope="col" style="text-align:center;">ลำดับ</th>
                                                                                <th scope="col" style="text-align:center;">รายการ</th>
                                                                                <th scope="col" style="text-align:center;">จำนวน</th>
                                                                                <th scope="col" style="text-align:center;">ราคา</th>
                                                                                <th scope="col" style="text-align:center;">รวม</th>
                                                                            </tr>
                                                                        </thead>

                                                                        <tbody id="table-body-sub-<?= $row['id'] ?>">
                                                                        </tbody>
                                                                    </table>
                                                                    <div class="col-sm-6">
                                                                        <button type="button" class="w-100 btn btn-secondary" onclick="toggleModal('#overlayModalTask-main-<?= $row['id'] ?>')">ย้อนกลับ</button>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <button type="submit" class="w-100 btn btn-success">ยืนยันที่จะเบิก</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
            </div>
            </form>
            </td>
            </tr>
        <?php
                        }
        ?>
        </tbody>
        </table>

        </div>
    </div>

    <div class="container-fluid">
        <?php
        $sql = "
SELECT * 
FROM (
    SELECT dp.*, dt.depart_name, 
        SEC_TO_TIME(
            CASE
                WHEN sla LIKE 'คอมพิวเตอร์ ใช้งานไม่ได้%' THEN 30 * 60
                WHEN sla LIKE 'เครื่องพิมพ์ ใช้งานไม่ได้ - 30 นาที%' THEN 30 * 60
                WHEN sla LIKE 'PMK ใช้งานไม่ได้%' THEN 15 * 60
                WHEN sla LIKE 'Internet ใช้งานไม่ได้%' THEN 20 * 60
                ELSE 0
            END
        ) AS use_time, 
        SEC_TO_TIME(ABS(TIME_TO_SEC(TIMEDIFF(close_date, take)))) AS time_range, 
        SEC_TO_TIME(
            GREATEST(
                ABS(TIME_TO_SEC(TIMEDIFF(close_date, take))) - 
                CASE
                    WHEN sla LIKE 'คอมพิวเตอร์ ใช้งานไม่ได้%' THEN 30 * 60
                    WHEN sla LIKE 'เครื่องพิมพ์ ใช้งานไม่ได้ - 30 นาที%' THEN 30 * 60
                    WHEN sla LIKE 'PMK ใช้งานไม่ได้%' THEN 15 * 60
                    WHEN sla LIKE 'Internet ใช้งานไม่ได้%' THEN 20 * 60
                    ELSE 0
                END, 
                0
            )
        ) AS over_time 
    FROM data_report AS dp
    LEFT JOIN depart AS dt ON dp.department = dt.depart_id
    WHERE dp.username = :username 
      AND sla IS NOT NULL
      AND sla != ''
      AND sla != 'ไม่ใช่'
      AND note = ''
        AND GREATEST(
ABS(TIME_TO_SEC(TIMEDIFF(close_date, take))) - 
CASE
    WHEN sla LIKE 'คอมพิวเตอร์ ใช้งานไม่ได้%' THEN 30 * 60
    WHEN sla LIKE 'เครื่องพิมพ์ ใช้งานไม่ได้ - 30 นาที%' THEN 30 * 60
    WHEN sla LIKE 'PMK ใช้งานไม่ได้%' THEN 15 * 60
    WHEN sla LIKE 'Internet ใช้งานไม่ได้%' THEN 20 * 60
    ELSE 0
END, 
0
) > 0

    UNION 

    SELECT dp.*, dt.depart_name, 
        NULL AS use_time, 
        NULL AS time_range, 
        NULL AS over_time 
    FROM data_report AS dp
    LEFT JOIN depart AS dt ON dp.department = dt.depart_id
    WHERE dp.username = :username 
      AND (dp.status = 6 OR dp.status = 3)
) AS combined_result
GROUP BY id
ORDER BY id DESC;
";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $admin);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $hasOverTime = false;
        foreach ($result as $row) {
            if (!empty($row['over_time']) && $row['over_time'] !== '00:00:00') {
                $hasOverTime = true;
                break;
            }
        }
        ?>

        <h1 class="mt-5">งานที่ยังไม่ได้กรอกรายละเอียด</h1>

        <?php if ($hasOverTime): ?>
            <div class="alert alert-danger" role="alert">
                คุณมีงานที่เกิน SLA แต่ยังไม่ได้ระบุหมายเหตุ!
            </div>
        <?php endif; ?>

        <div class="card rounded-4 shadow-sm p-3 mb-5 col-sm-12 col-lg-12 col-md-12">
            <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <form method="post" action="system_1/export.php">
                            <button name="actUncomplete" class="btn btn-primary" type="submit">Export->Excel</button>
                        </form>
                    </div>
                </div>

                <hr>
                <table id="dataAllUncomplete" class="table table-primary">
                    <thead>
                        <tr>
                            <th class="text-center" scope="col">ลำดับ</th>
                            <th scope="col">วันที่</th>
                            <th scope="col">เวลา</th>

                            <!-- <th scope="col">อุปกรณ์</th> -->
                            <th scope="col">รูปแบบการทำงาน</th>
                            <th scope="col">หมายเลขครุภัณฑ์</th>
                            <th scope="col">อาการที่ได้รับแจ้ง</th>
                            <th scope="col">ผู้แจ้ง</th>
                            <th scope="col">หน่วยงาน</th>
                            <th scope="col">เบอร์โทร</th>
                            <th scope="col">ระดับความเร่งด่วน</th>
                            <th scope="col">วันปิดงาน</th>
                            <th scope="col">เวลาปิดงาน</th>
                            <th scope="col">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php

                        // Process the fetched data
                        foreach ($result as $row) {

                            // Format date_report
                            $dateString = $row['date_report'];
                            $timestamp = strtotime($dateString);
                            $dateFormatted = date('d/m/Y', $timestamp);

                            if (empty($row['close_time']) || $row['close_time'] === '00:00:00.000000') {
                                $closeDateFormatted = '-';
                            } else {
                                $closeDateFormatted = date('d/m/Y', strtotime($row['close_time']));
                            }

                            // Format time_report (e.g., 14:22:00.000000 to 14:22)
                            $timeString = $row['time_report'];
                            $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                            // Format close_date (e.g., 0000-00-00 00:00:00.000000 to 22/11/2567 13:59)
                            $closeTimeString = $row['close_date'];
                            if (empty($closeTimeString) || $closeTimeString === '00:00:00.000000') {
                                $closeTimeFormatted = '-';
                            } else {
                                $closeTimeFormatted = date('H:i', strtotime($closeTimeString)) . ' น.';
                            }
                        ?>
                            <tr>
                                <td class="text-start" scope="row"><?= $row['id'] ?></td>
                                <td class="text-start" scope="row"><?= $dateFormatted ?></td>
                                <td class="text-start"><?= $timeFormatted ?></td>
                                <td class="text-start"><?= $row['device'] ?></td>
                                <td class="text-start"><?= $row['number_device'] ?></td>
                                <td class="text-start"><?= $row['report'] ?></td>
                                <td class="text-start"><?= $row['reporter'] ?></td>
                                <td class="text-start"><?= $row['depart_name'] ?></td>
                                <td class="text-start"><?= $row['tel'] ?></td>
                                <?php
                                $priorityLabels = [
                                    4 => "🔴เร่งด่วน",
                                    3 => "🟡กลาง",
                                    2 => "🔵ปกติ",
                                    1 => "⏰งานประจำวัน"
                                ];
                                ?>
                                <td class="text-start">
                                    <?= $priorityLabels[$row['priority']] ?? '-' ?>
                                </td>
                                <td class="text-start"><?= $closeDateFormatted ?></td>
                                <td class="text-start"><?= $closeTimeFormatted ?></td>
                                <?php

                                if (!empty($row['over_time']) && $row['over_time'] !== "00:00:00") {
                                    $statusText = "เกิน SLA";
                                    $buttonColor = "red";
                                } else {

                                    if ($row['status'] == 1) {
                                        $statusText = "ยังไม่ได้ดำเนินการ";
                                    } else if ($row['status'] == 2) {
                                        $statusText = "กำลังดำเนินการ";
                                    } else if ($row['status'] == 3) {
                                        $statusText = "รออะไหล่" . ' ' . $row['withdraw'];
                                    } else if ($row['status'] == 4) {
                                        $statusText = "เสร็จสิ้น" . ' ' . $row['withdraw'];
                                    } else if ($row['status'] == 5) {
                                        $statusText = "ส่งซ่อม";
                                    } else if ($row['status'] == 6) {
                                        $statusText = "รอกรอกรายละเอียด";
                                    }

                                    // Default button colors based on status
                                    switch ($row['status']) {
                                        case 1:
                                            $buttonColor = "orange";
                                            break;
                                        case 2:
                                            $buttonColor = "orange";
                                            break;
                                        case 3:
                                            $buttonColor = "blue";
                                            break;
                                        case 4:
                                            $buttonColor = "green";
                                            break;
                                        case 5:
                                            $buttonColor = "#D673D3";
                                            break;
                                        case 6:
                                            $buttonColor = "green";
                                            break;
                                        default:
                                            $buttonColor = "gray";
                                            break;
                                    }
                                }
                                ?>



                                <td>
                                    <button type="button"
                                        style="width: 80%; background-color: <?= $buttonColor ?>; color: white; border: 1px solid <?= $buttonColor ?>;"
                                        class="btn mb-3 btn-primary"
                                        onclick="toggleModal('#workflowModalUncomplete<?= $row['id'] ?>')">
                                        <?= $statusText ?>
                                    </button>

                                    <form action="system/insert.php" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <!-- modal -->
                                        <div id="workflowModalUncomplete<?= $row['id'] ?>" class="modal" style="display: none;">
                                            <div class="p-5 d-flex justify-content-center gap-4">
                                                <div class="modal-content job-modal" id="job-modal-unComplete-<?= $row['id'] ?>">
                                                    <div class="modal-header justify-content-between">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                                                        <div class="d-flex align-items-center">
                                                            <button type="button" class="btn btn-primary me-2" id="toggleAssignSectionBtn-unComplete-<?= $row['id'] ?>">เพิ่มเจ้าหน้าที่ร่วมงาน</button>
                                                            <button type="button" class="btn-close" onclick="toggleModal('#workflowModalUncomplete<?= $row['id'] ?>')"></button>
                                                        </div>
                                                    </div>
                                                    <div class="modal-body d-flex">
                                                        <div class="job-modal-content">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <label>หมายเลขงาน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['id'] ?>" disabled>
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>วันที่แจ้ง</label>
                                                                    <input type="date" class="form-control" name="date_report"
                                                                        value="<?= $row['date_report'] ?>">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>วันที่ปิดงาน</label>
                                                                    <input type="date" class="form-control" name="close_time"
                                                                        value="<?= $row['close_time'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <label>เวลาแจ้ง</label>
                                                                    <input type="time" class="form-control" name="time_report"
                                                                        value="<?= date('H:i', strtotime($row['time_report'])) ?>">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>เวลารับงาน</label>
                                                                    <input type="time" class="form-control" name="take"
                                                                        value="<?= date('H:i', strtotime($row['take']))  ?>">
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>เวลาปิดงาน (ถ้ามี)</label>
                                                                    <input type="time" class="form-control" id="time_report" name="close_date"
                                                                        value="<?= ($row['status'] == 3 && ($row['close_date'] === '00:00:00.000000' || $row['close_date'] === null || trim($row['close_date']) === ''))
                                                                                    ? '' : (($row['close_date'] && $row['close_date'] !== '00:00:00.000000') ? date('H:i', strtotime($row['close_date'])) : '') ?>">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>ประเภทงาน</label>
                                                                    <select name="work_type" class="form-select work-type">
                                                                        <option value="" <?= empty($row['work_type']) ? 'selected' : '' ?>>เลือก...</option>
                                                                        <option value="incident" <?= ($row['work_type'] === 'incident') ? 'selected' : '' ?>>อุบัติการณ์</option>
                                                                        <option value="อื่นๆ" <?= ($row['work_type'] === 'อื่นๆ') ? 'selected' : '' ?>>อื่นๆ</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-6">
                                                                    <label>ระดับความเร่งด่วน</label>
                                                                    <select name="priority" class="form-select priority">
                                                                        <option value="" <?= empty($row['priority']) ? 'selected' : '' ?>>เลือก...</option>
                                                                        <option value="4" <?= ($row['priority'] == 4) ? 'selected' : '' ?>>🔴เร่งด่วน</option>
                                                                        <option value="3" <?= ($row['priority'] == 3) ? 'selected' : '' ?>>🟡กลาง</option>
                                                                        <option value="2" <?= ($row['priority'] == 2) ? 'selected' : '' ?>>🔵ปกติ</option>
                                                                        <option value="1" <?= ($row['priority'] == 1) ? 'selected' : '' ?>>⏰งานประจำวัน</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>ผู้แจ้ง</label>
                                                                    <input type="text" class="form-control" name="reporter"
                                                                        value="<?= $row['reporter'] ?>">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>หน่วยงาน</label>
                                                                    <?php
                                                                    $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->execute([$row['department']]);
                                                                    $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <select class="form-select" name="department" id="departId<?= $row['id'] ?>" required>
                                                                        <option value="<?= $row['department'] ?>" selected><?= $departRow['depart_name'] ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>เบอร์ติดต่อกลับ</label>
                                                                    <input type="text" class="form-control" name="tel"
                                                                        value="<?= $row['tel'] ?>">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label for="deviceInput">อุปกรณ์</label>
                                                                    <select class="form-select" id="deviceInput<?= $row['id'] ?>" name="deviceName" required>
                                                                        <option value="<?= $row['deviceName'] ?>" selected><?= $row['deviceName'] ?></option>
                                                                    </select>
                                                                </div>

                                                            </div>

                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                    <input value="<?= $row['number_device'] ?>" type="text"
                                                                        class="form-control" name="number_devices" id="numberDeviceSource-unComplete-<?= $row['id'] ?>">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>หมายเลข IP addrees</label>
                                                                    <input type="text" class="form-control" name="ip_address"
                                                                        value="<?= $row['ip_address'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>อาการที่ได้รับแจ้ง</label>
                                                                    <input type="text" class="form-control" name="report_work"
                                                                        value="<?= $row['report'] ?>">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>รูปแบบการทำงาน<span style="color: red;">*</span></label>
                                                                    <select class="form-select" name="device"
                                                                        aria-label="Default select example">
                                                                        <option value="<?= $row['device'] ?: '' ?>"
                                                                            selected>
                                                                            <?= !empty($row['device']) ? $row['device'] : '-' ?>
                                                                        </option>
                                                                        <?php
                                                                        $sql = "SELECT * FROM workinglist";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($checkD as $d) {
                                                                            if ($d['workingName'] != $row['device']) {
                                                                        ?>
                                                                                <option value="<?= $d['workingName'] ?>">
                                                                                    <?= $d['workingName'] ?>
                                                                                </option>
                                                                        <?php }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>หมายเลขใบเบิก</label>
                                                                    <?php if (empty($row['withdraw'])) { ?>
                                                                        <input disabled type="text"
                                                                            class="form-control withdrawInput" name="withdraw"
                                                                            id="withdrawInput<?= $row['id'] ?>">
                                                                    <?php } else { ?>
                                                                        <input disabled value="<?= $row['withdraw'] ?>"
                                                                            type="text" class="form-control withdrawInput"
                                                                            name="withdraw" id="withdrawInput<?= $row['id'] ?>">
                                                                        <input type="hidden" value="<?= $row['withdraw'] ?>"
                                                                            class="form-control withdrawInput"
                                                                            id="withdrawInputHidden<?= $row['id'] ?>" name="withdraw2">
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <label>รายละเอียด<span style="color: red;">*</span></label>
                                                                        <?php
                                                                        // Fetch images for this report
                                                                        $sql = "SELECT filename FROM images_table WHERE report_id = :report_id";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->bindParam(':report_id', $row['id'], PDO::PARAM_INT);
                                                                        $stmt->execute();
                                                                        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        if ($images):
                                                                        ?>
                                                                            <!-- Button to open modal -->
                                                                            <button type="button" class="btn btn-link btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal<?= $row['id'] ?>">
                                                                                🖼️ดูรูปภาพ
                                                                            </button>

                                                                            <!-- Modal -->
                                                                            <div class="modal fade" id="imageModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-body">
                                                                                            <div id="carouselImages<?= $row['id'] ?>" class="carousel slide carousel-dark">
                                                                                                <div class="carousel-inner">
                                                                                                    <?php foreach ($images as $key => $img): ?>
                                                                                                        <div class="carousel-item <?= $key === 0 ? 'active' : '' ?>">
                                                                                                            <div class="d-flex justify-content-center">
                                                                                                                <img src="uploads/<?= htmlspecialchars($img['filename']) ?>" class="d-block" style="max-height:500px; max-width:100%;">
                                                                                                            </div>
                                                                                                            <div class="d-flex justify-content-center mt-3">
                                                                                                                <button type="button" class="btn btn-danger btn-sm delete-image"
                                                                                                                    data-filename="<?= htmlspecialchars($img['filename']) ?>"
                                                                                                                    data-report-id="<?= $row['id'] ?>">
                                                                                                                    ลบรูปภาพนี้
                                                                                                                </button>
                                                                                                            </div>
                                                                                                        </div>

                                                                                                    <?php endforeach; ?>
                                                                                                </div>
                                                                                                <?php if (count($images) > 1): ?>
                                                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselImages<?= $row['id'] ?>" data-bs-slide="prev">
                                                                                                        <span class="carousel-control-prev-icon"></span>
                                                                                                    </button>
                                                                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselImages<?= $row['id'] ?>" data-bs-slide="next">
                                                                                                        <span class="carousel-control-next-icon"></span>
                                                                                                    </button>
                                                                                                <?php endif; ?>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>

                                                                            <script>
                                                                                document.addEventListener('click', function(e) {
                                                                                    if (e.target && e.target.classList.contains('delete-image')) {
                                                                                        const buttonElement = e.target;
                                                                                        const filename = buttonElement.getAttribute('data-filename');
                                                                                        const reportId = buttonElement.getAttribute('data-report-id');

                                                                                        Swal.fire({
                                                                                            title: 'ยืนยันการลบ?',
                                                                                            text: "คุณต้องการลบรูปภาพนี้หรือไม่",
                                                                                            icon: 'warning',
                                                                                            showCancelButton: true,
                                                                                            confirmButtonColor: '#d33',
                                                                                            cancelButtonColor: '#3085d6',
                                                                                            confirmButtonText: 'ใช่, ลบเลย',
                                                                                            cancelButtonText: 'ยกเลิก'
                                                                                        }).then((result) => {
                                                                                            if (result.isConfirmed) {
                                                                                                fetch('system_1/delete_image.php', {
                                                                                                        method: 'POST',
                                                                                                        headers: {
                                                                                                            'Content-Type': 'application/x-www-form-urlencoded'
                                                                                                        },
                                                                                                        body: `filename=${encodeURIComponent(filename)}&report_id=${encodeURIComponent(reportId)}`
                                                                                                    })
                                                                                                    .then(response => response.json())
                                                                                                    .then(data => {
                                                                                                        if (data.status === 'success') {
                                                                                                            Swal.fire({
                                                                                                                title: 'ลบแล้ว!',
                                                                                                                text: 'รูปภาพถูกลบเรียบร้อย',
                                                                                                                icon: 'success',
                                                                                                                timer: 1200,
                                                                                                                showConfirmButton: false
                                                                                                            }).then(() => {
                                                                                                                const carouselItem = buttonElement.closest('.carousel-item');
                                                                                                                const carouselInner = carouselItem.parentElement;

                                                                                                                if (carouselItem.classList.contains('active')) {
                                                                                                                    let nextItem = carouselItem.nextElementSibling || carouselItem.previousElementSibling;
                                                                                                                    if (nextItem) {
                                                                                                                        nextItem.classList.add('active');
                                                                                                                    }
                                                                                                                }

                                                                                                                carouselItem.remove();

                                                                                                                // ✅ handle empty case
                                                                                                                if (carouselInner.children.length === 0) {
                                                                                                                    const placeholder = document.createElement('div');
                                                                                                                    placeholder.classList.add('carousel-item', 'active');
                                                                                                                    placeholder.innerHTML = `
                                        <div class="d-flex justify-content-center">
                                            <img src="image/Image-not-found.png" class="d-block" style="max-height:500px; max-width:100%;">
                                        </div>
                                    `;
                                                                                                                    carouselInner.appendChild(placeholder);

                                                                                                                    const modal = buttonElement.closest('.modal');
                                                                                                                    modal.querySelectorAll('.carousel-control-prev, .carousel-control-next').forEach(btn => btn.style.display = 'none');
                                                                                                                    modal.querySelectorAll('.delete-image').forEach(btn => btn.style.display = 'none');
                                                                                                                }
                                                                                                            });
                                                                                                        } else {
                                                                                                            Swal.fire('เกิดข้อผิดพลาด!', 'ไม่สามารถลบรูปภาพได้', 'error');
                                                                                                        }
                                                                                                    });
                                                                                            }
                                                                                        });
                                                                                    }
                                                                                });
                                                                            </script>
                                                                        <?php endif; ?>
                                                                    </div>

                                                                    <textarea class="form-control " name="description" rows="2" id="descriptionSource-unComplete-<?= $row['id'] ?>"><?= $row['description'] ?></textarea>
                                                                    <input
                                                                        class="form-control mt-2"
                                                                        type="file"
                                                                        id="formFileMultiple"
                                                                        name="images[]"
                                                                        multiple
                                                                        accept="image/*">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <?php if (!empty($row['over_time']) && $row['over_time'] !== '00:00:00'): ?>
                                                                        <label class="text-danger">*กรุณากรอกหมายเหตุที่เกิน SLA</label>
                                                                    <?php else: ?>
                                                                        <label>หมายเหตุ</label>
                                                                    <?php endif; ?>

                                                                    <input value="<?= htmlspecialchars($row['note']) ?>" type="text"
                                                                        class="form-control" name="noteTask">
                                                                </div>
                                                            </div>



                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>ผู้คีย์งาน</label>
                                                                    <input value="<?= $row['create_by'] ?>" type="text"
                                                                        class="form-control" name="create_by" disabled>
                                                                    <input value="<?= $row['create_by'] ?>" type="hidden"
                                                                        class="form-control" name="create_by">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>ซ่อมครั้งที่</label>
                                                                    <?php
                                                                    if (!empty($row['number_device']) && $row['number_device'] !== '-') {
                                                                        $sqlCount = "SELECT COUNT(*) AS repair_count 
                     FROM data_report 
                     WHERE number_device = :number_device 
                     AND (number_device IS NOT NULL AND number_device <> '' AND number_device <> '-')";
                                                                        $stmtCount = $conn->prepare($sqlCount);
                                                                        $stmtCount->bindParam(":number_device", $row['number_device']);
                                                                        $stmtCount->execute();
                                                                        $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
                                                                        $repairCount = $count['repair_count'];
                                                                    } else {
                                                                        $repairCount = '-';
                                                                    }
                                                                    ?>
                                                                    <input value="<?= $repairCount ?>" type="text" class="form-control" name="repair_count">
                                                                </div>
                                                            </div>

                                                            <hr class="mb-2">
                                                            <!-- !!!!! -->
                                                            <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>ปัญหาอยู่ใน SLA หรือไม่<span style="color: red;">*</span></label>
                                                                    <select class="form-select" name="sla"
                                                                        aria-label="Default select example">
                                                                        <option value="<?= $row['sla'] ?: '' ?>" selected>
                                                                            <?= !empty($row['sla']) ? $row['sla'] : '-' ?>
                                                                        </option>
                                                                        <?php
                                                                        $sql = "SELECT * FROM sla";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                        foreach ($checkD as $d) {
                                                                            if ($d['sla_name'] != $row['sla']) {
                                                                        ?>
                                                                                <option value="<?= $d['sla_name'] ?>">
                                                                                    <?= $d['sla_name'] ?>
                                                                                </option>
                                                                        <?php }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                    <?php
                                                                    if (!empty($row['over_time']) && $row['over_time'] !== '00:00:00') {
                                                                        // Convert over_time to hours and minutes
                                                                        list($hours, $minutes, $seconds) = explode(":", $row['over_time']);
                                                                        $hours = (int) $hours; // Convert to integer to remove leading zeros
                                                                        $minutes = (int) $minutes;

                                                                        // Format output
                                                                        $formattedOverTime = "";
                                                                        if ($hours > 0) {
                                                                            $formattedOverTime .= "$hours ชั่วโมง ";
                                                                        }
                                                                        if ($minutes > 0) {
                                                                            $formattedOverTime .= "$minutes นาที";
                                                                        }

                                                                        // Display formatted text
                                                                        echo "<p style='color: red;'>เกินเวลา SLA ไป - $formattedOverTime</p>";
                                                                    }
                                                                    ?>

                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>เป็นตัวชี้วัดหรือไม่<span style="color: red;">*</span></label>
                                                                    <?php
                                                                    $sqlPublic = "SELECT kpi_name FROM kpi WHERE kpi_id IN (1, 2)";
                                                                    $stmt = $conn->prepare($sqlPublic);
                                                                    $stmt->execute();
                                                                    $publicKpis = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                                                    // 2. Assigned KPIs
                                                                    $sqlAssigned = "SELECT DISTINCT kpi.kpi_name 
                FROM kpi 
                INNER JOIN kpi_assignment 
                ON kpi.kpi_id = kpi_assignment.kpi_id 
                WHERE kpi.kpi_id NOT IN (1, 2) AND kpi_assignment.username = ?";
                                                                    $stmt = $conn->prepare($sqlAssigned);
                                                                    $stmt->execute([$admin]);
                                                                    $assignedKpis = $stmt->fetchAll(PDO::FETCH_COLUMN);

                                                                    // 3. Merge both lists, keeping order
                                                                    $allKpis = array_merge($publicKpis, $assignedKpis);
                                                                    ?>

                                                                    <select class="form-select" name="kpi" aria-label="Default select example">
                                                                        <option value="<?= $row['kpi'] ?: '' ?>" selected>
                                                                            <?= !empty($row['kpi']) ? $row['kpi'] : '-' ?>
                                                                        </option>
                                                                        <?php foreach ($allKpis as $kpiName): ?>
                                                                            <?php if ($kpiName != $row['kpi']): ?>
                                                                                <option value="<?= $kpiName ?>"><?= $kpiName ?></option>
                                                                            <?php endif; ?>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <label>Activity Report<span style="color: red;">*</span></label>
                                                                    <select class="form-select" name="problem"
                                                                        aria-label="Default select example">
                                                                        <?php
                                                                        $sql = "SELECT * FROM problemlist";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute();
                                                                        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                                                                        <option value="<?= $row['problem'] ?: '' ?>"
                                                                            selected>
                                                                            <?= !empty($row['problem']) ? $row['problem'] : '-' ?>
                                                                        </option>
                                                                        <?php foreach ($data as $d) {
                                                                            if ($row['problem'] != $d['problemName']) { ?>
                                                                                <option value="<?= $d['problemName'] ?>">
                                                                                    <?= $d['problemName'] ?>
                                                                                </option>
                                                                        <?php }
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div id="assignSection-unComplete-<?= $row['id'] ?>" style="display: none; width: 210px;" class="ms-3">
                                                                <h6 class="mb-1">เพิ่มเจ้าหน้าที่ร่วมงาน</h6>
                                                                <?php
                                                                $assignedStmt = $conn->prepare("SELECT username FROM admin");
                                                                $assignedStmt->execute();
                                                                $allAdmins = $assignedStmt->fetchAll(PDO::FETCH_ASSOC);

                                                                $currentAdmin = $_SESSION['admin_log'] ?? null;
                                                                $assignedTask = array_filter($allAdmins, fn($admin) => $admin['username'] !== $currentAdmin);

                                                                $reportName = $row['report'];
                                                                $checkAssignedStmt = $conn->prepare("SELECT username FROM data_report WHERE report = :report AND date_report = :date_report AND time_report = :time_report");
                                                                $checkAssignedStmt->bindParam(":report", $reportName);
                                                                $checkAssignedStmt->bindParam(":date_report", $row['date_report']);
                                                                $checkAssignedStmt->bindParam(":time_report", $row['time_report']);
                                                                $checkAssignedStmt->execute();
                                                                $alreadyAssigned = $checkAssignedStmt->fetchAll(PDO::FETCH_COLUMN);
                                                                ?>
                                                                <div class="col-sm-12 mb-3">
                                                                    <div class="list-group">
                                                                        <label class="list-group-item">
                                                                            <input type="checkbox" class="form-check-input me-1" id="toggleAssignedTask-unComplete-<?= $row['id'] ?>">
                                                                            เลือกทั้งหมด
                                                                        </label>
                                                                        <?php foreach ($assignedTask as $task): ?>
                                                                            <?php
                                                                            $username = $task['username'];
                                                                            $isAssigned = in_array($username, $alreadyAssigned);
                                                                            ?>
                                                                            <label class="list-group-item">
                                                                                <input
                                                                                    class="form-check-input me-1"
                                                                                    type="checkbox"
                                                                                    name="assignedTask[]"
                                                                                    value="<?= htmlspecialchars($username) ?>"
                                                                                    <?= $isAssigned ? 'disabled checked' : '' ?>>
                                                                                <?= htmlspecialchars($username) ?>
                                                                                <?php if ($isAssigned): ?>
                                                                                    <span class="text-muted">(เพิ่มแล้ว)</span>
                                                                                <?php endif; ?>
                                                                            </label>
                                                                        <?php endforeach; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer" style="justify-content: space-between; border: none;">
                                                        <button type="submit" class="btn btn-danger"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            name="disWork">คืนงาน</button>
                                                        <button type="submit" class="btn btn-dark"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            name="cancelWork">ยกเลิก</button>
                                                        <button type="button" class="btn btn-primary" onclick="toggleModal('#UnCompleteModal<?= $row['id'] ?>')">เบิก/ส่งซ่อม</button>
                                                        <button type="submit" class="btn me-3 btn-primary"
                                                            onclick="removeHiddenInputOnModalClose('#UnCompleteModal<?= $row['id'] ?>')"
                                                            name="Bantext">บันทึก</button>
                                                        <button type="submit" name="CloseSubmit"
                                                            onclick="removeHiddenInputOnModalClose('#UnCompleteModal<?= $row['id'] ?>')"
                                                            class="btn btn-success">ปิดงาน</button>
                                                    </div>
                                                </div>

                                                <?php
                                                // Check if records exist in `orderdata_new` for the current `id`
                                                $checkQuery = "SELECT * FROM orderdata_new WHERE id_ref = :id_ref";
                                                $checkStmt = $conn->prepare($checkQuery);
                                                $checkStmt->bindParam(":id_ref", $row['id']);
                                                $checkStmt->execute();
                                                $requisitionData = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
                                                $hasRequisition = count($requisitionData) > 0;
                                                ?>

                                                <div id="UnCompleteModal<?= $row['id'] ?>" class="modal-content withdraw-modal" style="display: none;">
                                                    <div class="modal-header" style="background-color: <?= $hasRequisition ? '#F8BF24' : '#cfe2ff'; ?>;">
                                                        <?php if ($hasRequisition): ?>
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">แก้ไขใบเบิก</h1>
                                                        <?php else: ?>
                                                            <h1 class="modal-title fs-5" id="staticBackdropLabel">สร้างใบเบิก</h1>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn-close" onclick="toggleModal('#UnCompleteModal<?= $row['id'] ?>')"></button>
                                                    </div>
                                                    <div class="p-3">
                                                        <div class="row">
                                                            <input type="hidden" name="id_ref" value="<?= htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                            <?php if ($hasRequisition) {
                                                                foreach ($requisitionData as $rowData) { ?>
                                                                    <input type="hidden" name="withdraw_id" value="<?= htmlspecialchars($rowData['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                                    <div class="col-sm-12">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">เลขใบเบิก</label>
                                                                            <input type="text" class="form-control" value="<?= $rowData['numberWork'] ?>" disabled>
                                                                            <input type="hidden" name="numberWork" value="<?= $rowData['numberWork'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                            <input required type="date" name="dateWithdraw" class="form-control" value="<?= $rowData['dateWithdraw'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                            <select required class="form-select" name="refWithdraw" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM withdraw';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($withdraws as $withdraw) {
                                                                                    $selected = ($withdraw['withdraw_id'] == $rowData['refWithdraw']) ? 'selected' : ''; ?>
                                                                                    <option value="<?= $withdraw['withdraw_id'] ?>" <?= $selected ?>><?= $withdraw['withdraw_name'] ?></option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                            <select required class="form-select" name="refWork" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM listwork';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($listworks as $listwork) {
                                                                                    $selected = ($listwork['work_id'] == $rowData['refWork']) ? 'selected' : '';
                                                                                ?>
                                                                                    <option value="<?= $listwork['work_id'] ?>" <?= $selected ?>><?= $listwork['work_name'] ?></option>
                                                                                <?php }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">รายการอุปกรณ์</label>
                                                                            <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM device';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                foreach ($devices as $device) {
                                                                                    $isSelected = ($device['device_id'] === $rowData['refDevice']) ? 'selected' : '';
                                                                                ?>
                                                                                    <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                                                                                        <?= $device['device_name'] ?>
                                                                                    </option>
                                                                                <?php } ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                            <div id="device-number-container-unComplete-<?= $row['id'] ?>">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM order_numberdevice WHERE order_item = :order_item AND is_deleted = 0';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->bindParam(':order_item', $rowData['id'], PDO::PARAM_INT);
                                                                                $stmt->execute();
                                                                                $numberDevices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                                if (!empty($numberDevices)) {
                                                                                    $isFirst = true;
                                                                                    foreach ($numberDevices as $device) { ?>
                                                                                        <div class="d-flex device-number-row">
                                                                                            <input type="text" name="update_number_device[<?= $row['id'] ?>][<?= $device['id'] ?>]" class="form-control mb-2" value="<?= htmlspecialchars($device['numberDevice']) ?>">
                                                                                            <button type="button" class="btn btn-warning p-2 mb-2 ms-3 remove-field"
                                                                                                data-device-id="<?= $device['id'] ?>"
                                                                                                data-row-id="unComplete-<?= $row['id'] ?>"
                                                                                                style="visibility: <?= $isFirst ? 'hidden' : 'visible' ?>;">ลบ</button>
                                                                                        </div>
                                                                                    <?php
                                                                                        $isFirst = false;
                                                                                    }
                                                                                } else { ?>

                                                                                    <div class="d-flex device-number-row">
                                                                                        <input type="text" name="number_device[<?= $row['id'] ?>][]" class="form-control mb-2" value="<?= isset($row['number_device']) ? $row['number_device'] : '' ?>">
                                                                                        <button type="button" class="btn btn-danger p-2 ms-3 remove-field" style="visibility: hidden;">ลบ</button>
                                                                                    </div>
                                                                                <?php } ?>
                                                                            </div>
                                                                            <div class="d-flex justify-content-end">
                                                                                <button type="button" id="add-device-number-unComplete-<?= $row['id'] ?>" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label for="departInput">หน่วยงาน</label>
                                                                            <?php
                                                                            $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute([$rowData['refDepart']]);
                                                                            $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                            ?>
                                                                            <select class="form-select" name="depart_id" id="departId<?= $row['id'] ?>" required>
                                                                                <option value="<?= $rowData['refDepart'] ?>" selected><?= $departRow['depart_name'] ?></option>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                            </label>
                                                                            <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                                                                            <input type="hidden" name="ref_username" value="<?= htmlspecialchars($name) ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                            <input type="text" name="report" class="form-control" value="<?= $rowData['report'] ?>">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-6">
                                                                        <div class="mb-3">
                                                                            <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                            <input type="text" name="reason" class="form-control" value="<?= $rowData['reason'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">ร้านที่เสนอราคา</label>
                                                                            <select required class="form-select" name="refOffer" id="inputGroupSelect01">
                                                                                <?php
                                                                                $sql = 'SELECT * FROM offer';
                                                                                $stmt = $conn->prepare($sql);
                                                                                $stmt->execute();
                                                                                $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                foreach ($offers as $offer) {
                                                                                    $selected = ($offer['offer_id'] == $rowData['refOffer']) ? 'selected' : '';
                                                                                ?>
                                                                                    <option value="<?= $offer['offer_id'] ?>" <?= $selected ?>><?= $offer['offer_name'] ?></option>
                                                                                <?php }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                            </label>
                                                                            <input type="text" name="quotation" class="form-control" value="<?= $rowData['quotation'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">สถานะ</label>
                                                                            <select required class="form-select" name="status" id="inputGroupSelect01" disabled>
                                                                                <?php
                                                                                // Fetch the latest status for the current order
                                                                                $statusSql = "SELECT status FROM order_status 
                          WHERE order_id = :order_id 
                          ORDER BY timestamp DESC LIMIT 1";
                                                                                $statusStmt = $conn->prepare($statusSql);
                                                                                $statusStmt->bindParam(':order_id', $rowData['id'], PDO::PARAM_INT);
                                                                                $statusStmt->execute();
                                                                                $latestStatus = $statusStmt->fetchColumn();

                                                                                // Define status options
                                                                                $statusOptions = [
                                                                                    1 => "รอรับเอกสารจากหน่วยงาน",
                                                                                    2 => "รอส่งเอกสารไปพัสดุ",
                                                                                    3 => "รอพัสดุสั่งของ",
                                                                                    4 => "รอหมายเลขครุภัณฑ์",
                                                                                    5 => "ปิดงาน",
                                                                                    6 => "ยกเลิก"
                                                                                ];

                                                                                // Loop through and render the options
                                                                                foreach ($statusOptions as $key => $label) {
                                                                                    $selected = ($latestStatus == $key) ? "selected" : "";
                                                                                    echo "<option value=\"$key\" $selected>$label</option>";
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                       
                                                                    </div>
                                                                </div> -->

                                                                    <div class="col-sm-12">
                                                                        <div class="mb-3">
                                                                            <label for="inputGroupSelect01">หมายเหตุ
                                                                            </label>
                                                                            <input type="text" name="note" class="form-control" value="<?= $rowData['note'] ?>">
                                                                        </div>
                                                                    </div>

                                                                    <?php
                                                                    $orderId = $rowData['id'];
                                                                    $sql = "SELECT * FROM order_items WHERE order_id = :order_id AND is_deleted = 0";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
                                                                    $stmt->execute();
                                                                    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                    ?>
                                                                    <div class="d-flex justify-content-end align-items-center my-2">
                                                                        <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-unComplete-<?= $row['id'] ?>" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                                                                    </div>
                                                                    <table id="pdf" style="width: 100%;" class="table">
                                                                        <thead class="table-primary">
                                                                            <tr class="text-center" style="text-align:center;">
                                                                                <th scope="col">ลำดับ</th>
                                                                                <th scope="col">รายการ</th>
                                                                                <th scope="col">คุณสมบัติ</th>
                                                                                <th scope="col">จำนวน</th>
                                                                                <th scope="col">ราคา</th>
                                                                                <th scope="col">รวม</th>
                                                                                <th scope="col">หน่วย</th>
                                                                                <th scope="col"></th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="table-body-unComplete-<?= $row['id'] ?>">
                                                                            <?php
                                                                            $rowNumber = 1;
                                                                            $isFirstRow = true;
                                                                            foreach ($orderItems as $item) { //สร้าง case ถ้า orderItems is null
                                                                            ?>
                                                                                <tr class="text-center">
                                                                                    <th scope="row"><?= $rowNumber++; ?></th>
                                                                                    <td>
                                                                                        <select style="width: 150px; margin: 0 auto;" class="form-select device-select" name="update_list[<?= $row['id'] ?>][<?= $item['id'] ?>]" data-row="1">
                                                                                            <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                            <!-- Populate options dynamically -->
                                                                                            <?php
                                                                                            $deviceSql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                            $deviceStmt = $conn->prepare($deviceSql);
                                                                                            $deviceStmt->execute();
                                                                                            $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                            foreach ($devices as $device) {
                                                                                                $selected = $device['models_id'] == $item['list'] ? 'selected' : '';
                                                                                                echo "<option value='{$device['models_id']}' $selected>{$device['models_name']}</option>";
                                                                                            }
                                                                                            ?>
                                                                                        </select>
                                                                                    </td>
                                                                                    <td><textarea rows="2" maxlength="60" name="update_quality[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control"><?= htmlspecialchars($item['quality']); ?></textarea></td>
                                                                                    <td><input style="width: 3rem; margin: 0 auto;" type="text" name="update_amount[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control" value="<?= htmlspecialchars($item['amount']); ?>"></td>
                                                                                    <td><input style="width: 5rem; margin: 0 auto;" type="text" name="update_price[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control" value="<?= htmlspecialchars($item['price']); ?>"></td>
                                                                                    <td><input disabled value="" style="width: 5rem;" type="text" class="form-control no-toggle"></td>
                                                                                    <td><input style="width: 4rem; margin: 0 auto;" type="text" name="update_unit[<?= $row['id'] ?>][<?= $item['id'] ?>]" class="form-control" value="<?= htmlspecialchars($item['unit']); ?>"></td>
                                                                                    <td><button type="button" class="btn btn-warning remove-row"
                                                                                            data-items-id="<?= $item['id'] ?>"
                                                                                            data-items-row-id="<?= $row['id'] ?>"
                                                                                            style="visibility: <?= $isFirstRow ? 'hidden' : 'visible' ?>;">ลบ</button></td>
                                                                                </tr>
                                                                            <?php
                                                                                $isFirstRow = false;
                                                                            } ?>
                                                                        </tbody>
                                                                    </table>
                                                                    <div class="d-flex justify-content-end">
                                                                        <button type="button" id="add-row-unComplete-<?= $row['id'] ?>" class="btn btn-success">+ เพิ่มแถว</button>
                                                                    </div>

                                                                <?php } ?>
                                                                <div class="w-100 d-flex justify-content-center">
                                                                    <?php if ($row['status'] == 4 || $row['status'] == 6): ?>
                                                                        <button type="submit" name="backto_calm" class="w-100 btn btn-warning mt-3 me-3">ย้อนสถานะรออะไหล่</button>
                                                                    <?php endif; ?>
                                                                    <button type="submit" name="save_with_work" class="w-100 btn btn-primary mt-3">อัพเดตข้อมูล</button>
                                                                </div>
                                                            <?php } else { ?>
                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                        <input required type="date" name="dateWithdraw" class="form-control thaiDateInput">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ประเภทการเบิก</label>
                                                                        <select required class="form-select" name="refWithdraw" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM withdraw';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($withdraws as $withdraw) { ?>

                                                                                <option value="<?= $withdraw['withdraw_id'] ?>"><?= $withdraw['withdraw_name'] ?></option>
                                                                            <?php }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ประเภทงาน</label>
                                                                        <select required class="form-select" name="refWork" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM listwork';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($listworks as $listwork) { ?>

                                                                                <option value="<?= $listwork['work_id'] ?>"><?= $listwork['work_name'] ?></option>
                                                                            <?php }
                                                                            ?>

                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">รายการอุปกรณ์</label>

                                                                        <select required class="form-select" name="ref_device" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM device';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                                                            foreach ($devices as $device) {
                                                                                $isSelected = ($device['device_name'] === $row['deviceName']) ? 'selected' : '';
                                                                            ?>
                                                                                <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                                                                                    <?= $device['device_name'] ?>
                                                                                </option>
                                                                            <?php } ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">หมายเลขครุภัณฑ์</label>
                                                                        <div id="device-number-container-unComplete-<?= $row['id'] ?>">
                                                                            <div class="d-flex device-number-row">
                                                                                <input type="text" name="number_device[<?= $row['id'] ?>][]" class="form-control"
                                                                                    value="<?= isset($row['number_device']) ? $row['number_device'] : '' ?>">
                                                                                <button type="button" class="btn btn-danger p-2 ms-3" style="visibility: hidden;">ลบ</button>
                                                                            </div>
                                                                        </div>
                                                                        <div class="d-flex justify-content-end">
                                                                            <button type="button" id="add-device-number-unComplete-<?= $row['id'] ?>" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label for="departInput">หน่วยงาน</label>
                                                                        <?php
                                                                        $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->execute([$row['department']]);
                                                                        $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                                                                        ?>
                                                                        <select class="form-select" name="depart_id" id="departId<?= $row['id'] ?>" required>
                                                                            <option value="<?= $row['department'] ?>" selected><?= $departRow['depart_name'] ?></option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ผู้รับเรื่อง
                                                                        </label>
                                                                        <input type="text" class="form-control" value="<?= htmlspecialchars($name) ?>" disabled>
                                                                        <input type="hidden" name="ref_username" value="<?= htmlspecialchars($name) ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                        <input type="text" name="report" class="form-control" value="<?= $row['report'] ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                                                                        <input type="text" name="reason" class="form-control" id="reasonTarget-unComplete-<?= $row['id'] ?>" value="<?= $row['description'] ?>">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">ร้านที่เสนอราคา
                                                                        </label>
                                                                        <select required class="form-select" name="refOffer" id="inputGroupSelect01">
                                                                            <?php
                                                                            $sql = 'SELECT * FROM offer';
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->execute();
                                                                            $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                                            foreach ($offers as $offer) { ?>
                                                                                <option value="<?= $offer['offer_id'] ?>" <?= $offer['offer_id'] == 11 ? 'selected' : '' ?>><?= $offer['offer_name'] ?></option>
                                                                            <?php }
                                                                            ?>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">เลขที่ใบเสนอราคา
                                                                        </label>
                                                                        <input value="-" type="text" name="quotation" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-4">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">สถานะ
                                                                        </label>
                                                                        <select required class="form-select" name="status" id="inputGroupSelect01" disabled>
                                                                            <option value="1">รอรับเอกสารจากหน่วยงาน</option>
                                                                            <option value="2">รอส่งเอกสารไปพัสดุ</option>
                                                                            <option value="3">รอพัสดุสั่งของ</option>
                                                                            <option value="4">รอหมายเลขครุภัณฑ์</option>
                                                                            <option value="5">ปิดงาน</option>
                                                                            <option value="6">ยกเลิก</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <!-- <div class="col-sm-6">
                                                                    <div class="mb-3">
                                                                        <label id="basic-addon1">อาการรับแจ้ง</label>
                                                                       
                                                                    </div>
                                                                </div> -->

                                                                <div class="col-sm-12">
                                                                    <div class="mb-3">
                                                                        <label for="inputGroupSelect01">หมายเหตุ
                                                                        </label>
                                                                        <input value="-" type="text" name="note" class="form-control">
                                                                    </div>
                                                                </div>

                                                                <div class="d-flex justify-content-end align-items-center my-2">
                                                                    <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-unComplete-<?= $row['id'] ?>" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                                                                </div>
                                                                <table id="pdf" style="width: 100%;" class="table">
                                                                    <thead class="table-primary">
                                                                        <tr class="text-center" style="text-align:center;">
                                                                            <th scope="col">ลำดับ</th>
                                                                            <th scope="col">รายการ</th>
                                                                            <th scope="col">คุณสมบัติ</th>
                                                                            <th scope="col">จำนวน</th>
                                                                            <th scope="col">ราคา</th>
                                                                            <th scope="col">รวม</th>
                                                                            <th scope="col">หน่วย</th>
                                                                            <th scope="col"></th>
                                                                        </tr>
                                                                    </thead>

                                                                    <tbody id="table-body-unComplete-<?= $row['id'] ?>">
                                                                        <tr class="text-center">
                                                                            <th scope="row">1</th>
                                                                            <td>
                                                                                <select style="width: 150px; margin: 0 auto;" class="form-select device-select" name="list[<?= $row['id'] ?>][]" data-row="1">
                                                                                    <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                                                                    <!-- Populate options dynamically -->
                                                                                    <?php
                                                                                    $deviceSql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                                                                    $deviceStmt = $conn->prepare($deviceSql);
                                                                                    $deviceStmt->execute();
                                                                                    $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
                                                                                    foreach ($devices as $device) {
                                                                                    ?>
                                                                                        <option value="<?= $device['models_id'] ?>"><?= $device['models_name'] ?></option>
                                                                                    <?php
                                                                                    }
                                                                                    ?>
                                                                                </select>
                                                                            </td>
                                                                            <td><textarea rows="2" maxlength="60" name="quality[<?= $row['id'] ?>][]" class="form-control"></textarea></td>
                                                                            <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[<?= $row['id'] ?>][]" class="form-control"></td>
                                                                            <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[<?= $row['id'] ?>][]" class="form-control"></td>
                                                                            <td><input disabled value="" style="width: 5rem;" type="text" class="form-control no-toggle"></td>
                                                                            <td><input style="width: 4rem; margin: 0 auto;" type="text" name="unit[<?= $row['id'] ?>][]" class="form-control"></td>
                                                                            <td><button type="button" class="btn btn-danger" style="visibility: hidden;">ลบ</button></td>
                                                                        </tr>

                                                                    </tbody>

                                                                </table>

                                                                <div class="d-flex justify-content-end">
                                                                    <button type="button" id="add-row-unComplete-<?= $row['id'] ?>" class="btn btn-success">+ เพิ่มแถว</button>
                                                                </div>

                                                                <input type="hidden" name="submit_with_work" value="1">

                                                                <div class="w-100 d-flex justify-content-center">
                                                                    <button data-id="submit-unComplete-<?= $row['id'] ?>" type="submit" name="submit_with_work" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                                                                </div>
                                                            <?php }
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- unComplete overlay modal -->
                                        <div id="overlayModalTask-unComplete-<?= $row['id'] ?>" class="modal" style="display: none;">
                                            <div class="p-5 d-flex justify-content-center gap-4">
                                                <div class="modal-content overlay-modal">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">พบรายการที่เคยเบิกไปแล้ว</h1>
                                                        <button type="button" class="btn-close"
                                                            onclick="toggleModal('#overlayModalTask-unComplete-<?= $row['id'] ?>')">
                                                        </button>

                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <h5>หมายเลขครุภัณฑ์ <strong class="text-danger" id="duplicateAssetNumber"></strong> เคยเบิกไปแล้ว</h5>

                                                            <div class="col-sm-12">
                                                                <div class="btn-group my-2" role="group" aria-label="Order toggle button group" id="orderRadioGroup"></div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-3">
                                                                    <label id="basic-addon1">วันที่ออกใบเบิก</label>
                                                                    <input type="date" name="dateWithdraw" class="form-control" disabled>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-2">
                                                                    <label id="basic-addon1">หมายเลขครุภัณฑ์</label>

                                                                    <div class="d-flex device-number-row">
                                                                        <input type="text" class="form-control" disabled>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-2">
                                                                    <label for="inputGroupSelect01">รายการอุปกรณ์</label>
                                                                    <input type="text" class="form-control" name="device_name" disabled>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-6">
                                                                <div class="mb-2">
                                                                    <label for="departInput">หน่วยงาน</label>
                                                                    <input type="text" class="form-control" name="depart_name" disabled>
                                                                </div>
                                                            </div>

                                                            <div class="col-sm-12">
                                                                <div class="mb-2">
                                                                    <label for="inputGroupSelect01">หมายเหตุ
                                                                    </label>
                                                                    <input type="text" name="note" class="form-control" disabled>
                                                                </div>
                                                            </div>

                                                            <div class="d-flex justify-content-end align-items-center mb-2">
                                                                <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-sub-unComplete-<?= $row['id'] ?>" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                                                            </div>
                                                            <table id="pdf" style="width: 100%;" class="table mb-4">
                                                                <thead class="table-primary">
                                                                    <tr class="text-center">
                                                                        <th scope="col" style="text-align:center;">ลำดับ</th>
                                                                        <th scope="col" style="text-align:center;">รายการ</th>
                                                                        <th scope="col" style="text-align:center;">จำนวน</th>
                                                                        <th scope="col" style="text-align:center;">ราคา</th>
                                                                        <th scope="col" style="text-align:center;">รวม</th>
                                                                    </tr>
                                                                </thead>

                                                                <tbody id="table-body-sub-<?= $row['id'] ?>">
                                                                </tbody>
                                                            </table>
                                                            <div class="col-sm-6">
                                                                <button type="button" class="w-100 btn btn-secondary" onclick="toggleModal('#overlayModalTask-unComplete-<?= $row['id'] ?>')">ย้อนกลับ</button>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <button type="submit" class="w-100 btn btn-success">ยืนยันที่จะเบิก</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

            </div>
        </div>
    </div>
    </form>
    </td>
    </tr>
<?php

                        }
?>
</tbody>
</table>

</div>
</div>



<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    const now = new Date();

    // Format the time as HH:mm
    const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

    // Set the current time as the default value for the input fields
    const timeReportInputs = document.querySelectorAll('.time_report');
    timeReportInputs.forEach(input => input.value = currentTime);
</script>
<script>
    // $(function() {
    //     function setupAutocomplete({
    //         type,
    //         inputSelector,
    //         hiddenInputSelector,
    //         sourceUrl,
    //         confirmMessage = "คุณต้องการเพิ่มรายการนี้หรือไม่?",
    //         resetValue = "",
    //         defaultHiddenId = ""
    //     }) {
    //         let inputChanged = false;
    //         let alertShown = false; // Flag to track if the alert has been shown already

    //         const $input = $(inputSelector);
    //         const $hiddenInput = $(hiddenInputSelector);

    //         $input.autocomplete({
    //                 source: function(request, response) {
    //                     $.ajax({
    //                         url: sourceUrl,
    //                         method: "GET",
    //                         dataType: "json",
    //                         data: {
    //                             term: request.term,
    //                             type: type
    //                         },
    //                         success: function(data) {
    //                             response(data); // Show suggestions
    //                         },
    //                         error: function() {
    //                             response([]);
    //                         }
    //                     });
    //                 },
    //                 minLength: 1,
    //                 autoFocus: true,
    //                 select: function(event, ui) {
    //                     if (ui.item && ui.item.value !== "") {
    //                         $input.val(ui.item.label);
    //                         $hiddenInput.val(ui.item.value);
    //                     } else {
    //                         $input.val('');
    //                         $hiddenInput.val('');
    //                     }
    //                     inputChanged = false;
    //                     return false;
    //                 }
    //             })
    //             .data("ui-autocomplete")._renderItem = function(ul, item) {
    //                 return $("<li>")
    //                     .append("<div>" + item.label + "</div>")
    //                     .appendTo(ul);
    //             };

    //         $input.on("input", function() {
    //             inputChanged = true;
    //         });

    //         $input.on("blur", function() {
    //             if (!inputChanged) return;

    //             const enteredValue = $input.val().trim();
    //             if (!enteredValue) {
    //                 $hiddenInput.val('');
    //                 return;
    //             }

    //             $.ajax({
    //                 url: sourceUrl,
    //                 method: "GET",
    //                 dataType: "json",
    //                 data: {
    //                     term: enteredValue,
    //                     type: type
    //                 },
    //                 success: function(data) {
    //                     const found = data.some(item => item.label === enteredValue);
    //                     if (!found) {
    //                         alertShown = true;
    //                         Swal.fire({
    //                             icon: 'warning',
    //                             title: confirmMessage,
    //                             text: 'หากต้องการเพิ่ม กรุณาติดต่อแอดมิน',
    //                             confirmButtonText: 'ตกลง'
    //                         }).then(() => {
    //                             $input.val(resetValue);
    //                             $hiddenInput.val(defaultHiddenId);
    //                             alertShown = false;
    //                         });
    //                     }
    //                 }
    //             });
    //             inputChanged = false; // Reset the flag
    //         });
    //     }

    //     // Initialize autocomplete for all dynamically generated inputs
    //     $("input[id^='deviceInput']").each(function() {
    //         const index = $(this).attr("id").replace("deviceInput", "");
    //         setupAutocomplete({
    //             type: "device",
    //             inputSelector: `#deviceInput${index}`,
    //             hiddenInputSelector: `#deviceId${index}`,
    //             sourceUrl: "system_1/autocomplete.php",
    //             confirmMessage: "ไม่พบหน่วยงานนี้ในระบบ",
    //             resetValue: "-",
    //             defaultHiddenId: "105"
    //         });
    //     });
    // });

    document.addEventListener('DOMContentLoaded', function() {
        function setupChoicesAutocomplete({
            type,
            selectSelector,
            sourceUrl,
            notFoundMessage = "ไม่พบข้อมูลในระบบ"
        }) {
            const selects = document.querySelectorAll(selectSelector);

            selects.forEach(select => {
                if (select.dataset.choices === "true") return;

                const initialValue = select.value;
                const choices = new Choices(select, {
                    searchEnabled: true,
                    shouldSort: false,
                    placeholder: true,
                    placeholderValue: `กรุณาเลือก...`,
                    searchPlaceholderValue: 'พิมพ์เพื่อค้นหา...',
                    itemSelectText: '',
                    searchResultLimit: -1,
                });

                select.dataset.choices = "true";

                async function fetchData(term = '') {
                    try {
                        const response = await fetch(`${sourceUrl}?term=${encodeURIComponent(term)}&type=${type}`);
                        const data = await response.json();

                        choices.clearChoices();

                        if (!data.length) {
                            choices.setChoices([{
                                value: '',
                                label: notFoundMessage,
                                disabled: true
                            }], 'value', 'label', true);
                            return;
                        }

                        const options = data.map(item => ({
                            value: (type === 'device') ? item.label : item.value,
                            label: item.label,
                            selected: item.value == initialValue // ✅ mark the DB value as selected
                        }));

                        // Add a placeholder option at the top
                        options.unshift({
                            value: '',
                            label: 'กรุณาเลือก...',
                            selected: !initialValue,
                            disabled: false
                        });

                        choices.setChoices(options, 'value', 'label', true);
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    }
                }
                // Load data initially
                fetchData();
            });
        }

        function initAllChoices() {
            setupChoicesAutocomplete({
                type: "device",
                selectSelector: "select[id^='deviceInput']",
                sourceUrl: "system_1/autocomplete.php"
            });

            setupChoicesAutocomplete({
                type: "depart",
                selectSelector: "select[id^='departId']",
                sourceUrl: "system_1/autocomplete.php"
            });
        }

        // ✅ Run on page load
        initAllChoices();

        // ✅ Run again whenever DataTables redraws
        $('#dataAll').on('draw.dt', function(e) {
            if (e.target && e.target.nodeName === "TABLE") {
                initAllChoices();
            }
        });

        $('#dataAllUncomplete').on('draw.dt', function(e) {
            if (e.target && e.target.nodeName === "TABLE") {
                initAllChoices();
            }
        });
    });
</script>
<script>
    function toggleModal(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.style.display = modal.style.display === "none" || modal.style.display === "" ? "block" : "none";
        } else {
            console.error("Modal not found:", modalId);
        }
    }
</script>

<script>
    //เพิ่มแถวตาราง
    function calculateSumTotal(tableBodyId) {
        let total = 0;
        const sumInputs = document.querySelectorAll(`#${tableBodyId} input.no-toggle`);
        sumInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        const [tableType, modalId] = tableBodyId.split('-').slice(-2);
        const totalAmount = document.querySelector(`#total-amount-${tableType}-${modalId}`);
        if (totalAmount) {
            totalAmount.textContent = total.toLocaleString();
        }
        // console.log(`Total for ${tableType}-${modalId}: `, total);
    }

    function calculateRowTotalAutoList(rowElement, tableBodyId) {
        const amountInput = rowElement.querySelector('input[name*="amount"]');
        const priceInput = rowElement.querySelector('input[name*="price"]');
        const totalInput = rowElement.querySelector('input.no-toggle');

        const amount = parseFloat(amountInput?.value || 0);
        const price = parseFloat(priceInput?.value || 0);
        totalInput.value = (amount * price);

        calculateSumTotal(tableBodyId);
    }

    function calculateRowTotal(row, tableBodyId) {
        const amountInput = row.querySelector('input[name*="amount"]');
        const priceInput = row.querySelector('input[name*="price"]');
        const totalInput = row.querySelector('input.no-toggle');

        const calculate = () => {
            const amount = parseFloat(amountInput.value) || 0;
            const price = parseFloat(priceInput.value) || 0;
            totalInput.value = (amount * price); // Ensure toFixed for consistent formatting
            calculateSumTotal(tableBodyId); // Update the table's total
        };

        // Attach event listeners for recalculating row totals
        if (amountInput && priceInput) {
            amountInput.addEventListener("input", calculate);
            priceInput.addEventListener("input", calculate);
        }

        calculate(); // Initial calculation when the row is added
    }

    document.addEventListener("DOMContentLoaded", function() {
        const tableRows = document.querySelectorAll('[id^="table-body-"] tr');
        tableRows.forEach((row) => {
            const tableBodyId = row.closest('tbody').id;
            calculateRowTotal(row, tableBodyId);
        });
    });

    let rowIndex = 1;
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id.startsWith('add-row-main-') || e.target.id.startsWith('add-row-unComplete-')) {
            const modalId = e.target.id.split('-').pop(); // Extract the modal ID
            const isMain = e.target.id.includes('main');
            const tableBody = document.querySelector(`#table-body-${isMain ? 'main' : 'unComplete'}-${modalId}`);
            const rowIndex = tableBody.querySelectorAll('tr').length + 1;

            const newRow = document.createElement('tr');
            newRow.className = 'text-center';
            newRow.innerHTML = `
            <th scope="row">${rowIndex}</th>
            <td>
                <select style="width: 150px; margin: 0 auto;" class="form-select device-select" 
                        name="list[${modalId}][]" data-row="${rowIndex}">
                    <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                    <?php
                    foreach ($devices as $device) {
                    ?>
                    <option value="<?= $device['models_id'] ?>"><?= $device['models_name'] ?></option>
                    <?php
                    }
                    ?>
                </select>
            </td>
            <td><textarea rows="2" maxlength="60" name="quality[${modalId}][]" class="form-control"></textarea></td>
            <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[${modalId}][]" class="form-control"></td>
            <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[${modalId}][]" class="form-control"></td>
            <td><input disabled value="" style="width: 5rem;" type="text" class="form-control no-toggle"></td>
            <td><input style="width: 4rem; margin: 0 auto;" type="text" name="unit[${modalId}][]" class="form-control"></td>
            <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
        `;

            tableBody.appendChild(newRow);
            calculateRowTotal(newRow, `table-body-${isMain ? 'main' : 'unComplete'}-${modalId}`);
        }
    });
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-row')) {
            const row = e.target.closest('tr');
            const hiddenInput = row.querySelector('select');
            const tableBody = row.closest("tbody");
            const tableBodyId = tableBody.id;

            if (hiddenInput && hiddenInput.name.startsWith('update_list')) {
                // Case 1: Soft delete for saved rows
                const rowId = e.target.getAttribute('data-items-row-id');
                const itemId = e.target.getAttribute('data-items-id');
                const isMain = tableBodyId.includes('main');
                const mainTableBody = document.querySelector(
                    `#table-body-${isMain ? 'main' : 'unComplete'}-${rowId}`
                );
                const deletedInput = document.createElement('input');
                deletedInput.type = 'hidden';
                deletedInput.name = `deleted_items[${rowId}][${itemId}]`;
                deletedInput.value = itemId;
                mainTableBody.appendChild(deletedInput);
            }

            // Case 2: Direct removal of unsaved rows
            row.remove();
            calculateSumTotal(tableBodyId)
        }
    });

    function updateRowNumbers() {
        const rows = document.querySelectorAll('#table-body tr');
        rows.forEach((row, index) => {
            row.querySelector('th').textContent = index + 1;
        });
        rowIndex = rows.length;
    }

    $(document).on('change', '.device-select', function() {
        const models_id = $(this).val();
        const rowElement = $(this).closest('tr');
        const tableBodyId = $(this).closest('tbody').attr('id');
        const modalId = tableBodyId.split('-').pop();
        const tableType = tableBodyId.includes('main') ? 'main' : 'unComplete';
        const nameAttr = $(this).attr('name');
        const matches = nameAttr.match(/\[(\d+)\]\[(\d+)\]/);
        const isUpdateMode = matches !== null;
        const itemId = isUpdateMode ? matches[2] : null;

        if (models_id) {
            $.ajax({
                url: 'system_1/autoList.php',
                type: 'POST',
                data: {
                    models_id: models_id
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        if (isUpdateMode) {
                            rowElement.find('textarea').attr('name', `update_quality[${modalId}][${itemId}]`).val(data.quality);
                            rowElement.find('input[name^="update_price"]').attr('name', `update_price[${modalId}][${itemId}]`).val(data.price);
                            rowElement.find('input[name^="update_unit"]').attr('name', `update_unit[${modalId}][${itemId}]`).val(data.unit);
                        } else {
                            rowElement.find('textarea').attr('name', `quality[${modalId}][]`).val(data.quality);
                            rowElement.find('input[name^="price"]').attr('name', `price[${modalId}][]`).val(data.price);
                            rowElement.find('input[name^="unit"]').attr('name', `unit[${modalId}][]`).val(data.unit);
                        }
                        calculateRowTotalAutoList(rowElement[0], `table-body-${tableType}-${modalId}`);
                    } else {
                        alert('ไม่สามารถดึงข้อมูลได้');
                    }
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                }
            });
        }
    });
</script>
<script>
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id.startsWith('add-device-number-')) {
            const [type, modalId] = e.target.id.split('-').slice(-2);
            const container = document.querySelector(`#device-number-container-${type}-${modalId}`);
            if (container) {
                const newRow = document.createElement('div');
                newRow.className = 'd-flex device-number-row';
                newRow.innerHTML = `
<input type="text" name="number_device[${modalId}][]" class="form-control mt-2">
                <button type="button" class="btn btn-warning mt-2 p-2 remove-field ms-3">ลบ</button>
            `;
                container.appendChild(newRow);
            } else {
                console.error(`Container not found for type: ${type} and modalId: ${modalId}`);
            }
        }
    });

    // Remove a device row
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-field')) {
            const row = e.target.closest('.device-number-row');
            const hiddenInput = row.querySelector('input[type="text"]');

            if (hiddenInput && hiddenInput.name.startsWith('update_number_device')) {
                // Case 1: Soft delete
                const getModalId = e.target.getAttribute('data-row-id');
                const modalId = getModalId.split('-').pop();
                const isMain = getModalId.includes('main');
                const deviceId = e.target.getAttribute('data-device-id');
                const container = document.querySelector(`#device-number-container-${isMain ? 'main' : 'unComplete'}-${modalId}`);
                const deletedInput = document.createElement('input');
                deletedInput.type = 'text';
                deletedInput.name = `deleted_devices[${modalId}][${deviceId}]`;
                deletedInput.value = hiddenInput.value;
                container.appendChild(deletedInput);
            } else if (hiddenInput && hiddenInput.name.startsWith('number_device')) {
                // Case 2: Remove blank field
                row.remove();
                return;
            }

            // Remove row for both cases
            row.remove();
        }
    });
</script>

<script>
    // ฟังก์ชันสำหรับแปลงปีคริสต์ศักราชเป็นปีพุทธศักราช
    function convertToBuddhistYear(englishYear) {
        return englishYear;
    }

    // ดึงอินพุทธศักราชปัจจุบัน
    const currentGregorianYear = new Date().getFullYear();
    const currentBuddhistYear = convertToBuddhistYear(currentGregorianYear);

    // หากคุณมีหลาย input ที่ต้องการกำหนดค่า
    const thaiDateInputs = document.querySelectorAll('.thaiDateInput');

    thaiDateInputs.forEach((input) => {
        // แปลงปีปัจจุบันเป็นปีพุทธศักราชแล้วกำหนดค่าให้กับ input
        const currentDate = new Date();
        input.value = currentBuddhistYear + '-' +
            ('0' + (currentDate.getMonth() + 1)).slice(-2) + '-' +
            ('0' + currentDate.getDate()).slice(-2);
    });
</script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
    $('#dataAll').DataTable({
        order: [
            [10, 'asc']
        ] // assuming you want to sort the first column in ascending order
    });
    $('#dataAllUncomplete').DataTable({
        order: [
            [10, 'asc']
        ] // assuming you want to sort the first column in ascending order
    });
</script>
<script>
    document.addEventListener('click', async function(event) {
        if (event.target.matches('[name="submit_with_work"]')) {
            event.preventDefault();

            // Get the form and device number inputs
            const button = event.target;
            const form = button.closest('form');
            const deviceNumberInputs = form.querySelectorAll('input[name^="number_device"]:not([name="number_devices"])');

            let duplicateFound = false;
            let deviceNumbers = [];

            deviceNumberInputs.forEach(input => {
                const deviceNumber = input.value.trim();
                if (deviceNumber && deviceNumber !== '-') {
                    deviceNumbers.push(deviceNumber);
                }
            });

            try {
                // AJAX request to fetch duplicate data
                const response = await fetch('system_1/check_duplicate.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `number_device=${encodeURIComponent(JSON.stringify(deviceNumbers))}`
                });

                const result = await response.json();
                // console.log(result)
                if (result.found) {
                    duplicateFound = true;

                    // Get modal ID dynamically
                    const getModalId = event.target.getAttribute('data-id');
                    const status = getModalId.split('-')[1];
                    const submitId = getModalId.split('-').pop();

                    // Show modal and populate fields dynamically
                    const modal = document.querySelector(`#overlayModalTask-${status}-${submitId}`);
                    modal.style.display = 'block';

                    const cardModal = modal.querySelector('.modal-content.overlay-modal');
                    cardModal.classList.add('giggle');
                    setTimeout(() => {
                        cardModal.classList.remove('giggle');
                    }, 300);

                    modal.querySelector('#duplicateAssetNumber').textContent = deviceNumbers.join(', ');
                    // Extract and group orders by numberWork
                    const ordersByNumberWork = {};

                    Object.keys(result.orders).forEach(orderId => {
                        const order = result.orders[orderId]; // Get the order object
                        const numberWork = order.numberWork;

                        if (!ordersByNumberWork[numberWork]) {
                            ordersByNumberWork[numberWork] = {
                                order: order,
                                items: []
                            };
                        }

                        // Add items from this order to the grouped structure
                        Object.keys(order.items).forEach(itemId => {
                            ordersByNumberWork[numberWork].items.push(order.items[itemId]);
                        });
                    });


                    // Populate the radio button group
                    const orderRadioGroup = modal.querySelector('#orderRadioGroup');
                    orderRadioGroup.innerHTML = ''; // Clear existing buttons

                    // console.log('Modal:', modal);
                    // console.log('Order Radio Group:', orderRadioGroup);


                    const orderCount = Object.keys(ordersByNumberWork).length;
                    orderRadioGroup.classList.remove('w-25', 'w-50', 'w-100', 'btn-group-vertical', 'btn-group'); // Remove old classes

                    if (orderCount === 1) {
                        orderRadioGroup.classList.add('btn-group', 'w-25');
                    } else if (orderCount === 2) {
                        orderRadioGroup.classList.add('btn-group', 'w-50');
                    } else if (orderCount > 8) {
                        orderRadioGroup.classList.add('btn-group-vertical', 'w-100');
                    } else {
                        orderRadioGroup.classList.add('btn-group', 'w-100');
                    }

                    // console.log('ordersByNumberWork', ordersByNumberWork);

                    Object.keys(ordersByNumberWork).forEach((numberWork, index) => {

                        const radioButton = document.createElement('input');
                        radioButton.type = 'radio';
                        radioButton.classList.add('btn-check');
                        radioButton.name = 'orderRadio';
                        radioButton.id = `orderRadio-${numberWork}`;
                        radioButton.value = numberWork;
                        radioButton.checked = index === 0; // Select the first by default

                        const label = document.createElement('label');
                        label.classList.add('btn', 'btn-outline-danger');
                        label.setAttribute('for', `orderRadio-${numberWork}`);
                        label.textContent = `ใบเบิก ${numberWork}`;

                        orderRadioGroup.appendChild(radioButton);
                        orderRadioGroup.appendChild(label);

                        // console.log('radioButton', radioButton);
                        // console.log('label', label);

                        // Add event listener for radio change
                        radioButton.addEventListener('change', () => {
                            displayOrderDetails(modal, ordersByNumberWork[numberWork]);
                        });

                        // Display first order by default
                        if (index === 0) {
                            displayOrderDetails(modal, ordersByNumberWork[numberWork]);
                        }
                    });

                }
            } catch (error) {
                console.error('Error checking device number:', error);
            }
            //     }
            // }

            if (!duplicateFound) {
                // If no duplicate is found, you can submit the form if needed
                form.submit();
            }
        }
    });

    function removeHiddenInputOnModalClose(modalId) {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.style.display = 'none';

            // Find and remove the hidden input if it exists
            const form = modal.closest('form');
            const hiddenInput = form.querySelector('input[name="submit_with_work"]');
            if (hiddenInput) {
                hiddenInput.remove();
            }
        }
    }

    // Function to display order details
    function displayOrderDetails(modal, orderData) {
        // Populate order info
        modal.querySelector('input[name="dateWithdraw"]').value = orderData.order.dateWithdraw || '';
        modal.querySelector('input[name="device_name"]').value = orderData.order.device_name || '';
        modal.querySelector('input[name="note"]').value = orderData.order.note || '';
        modal.querySelector('input[name="depart_name"]').value = orderData.order.depart_name || '';
        modal.querySelector('.device-number-row input').value = orderData.order.numberDevice || '';

        // Populate items in the table
        const tableBody = modal.querySelector('tbody');
        tableBody.innerHTML = '';

        let totalAmount = 0;
        orderData.items.forEach((item, index) => {
            totalAmount += parseFloat(item.total);

            const row = `
            <tr class="text-center">
                <th scope="row">${index + 1}</th>
                <td><input style="width: 200px; margin: 0 auto;" type="text" class="form-control" value="${item.list_name}" disabled></td>
                <td><input style="width: 3rem; margin: 0 auto;" type="text" class="form-control" value="${item.amount}" disabled></td>
                <td><input style="width: 5rem; margin: 0 auto;" type="text" class="form-control" value="${item.price}" disabled></td>
                <td><input disabled style="width: 5rem;" type="text" class="form-control no-toggle" value="${item.total}"></td>
            </tr>
        `;
            tableBody.innerHTML += row;
        });

        // console.log('#total-amount-sub-' + modal.id.split('-')[1] + '-' + modal.id.split('-')[2])
        // Update total amount
        modal.querySelector('#total-amount-sub-' + modal.id.split('-')[1] + '-' + modal.id.split('-')[2]).textContent = totalAmount;
    }
</script>
<script>
    document.addEventListener("click", function(e) {
        if (e.target && e.target.id.startsWith("toggleAssignSectionBtn-")) {
            const parts = e.target.id.split("-");
            const type = parts[1];
            const rowId = parts[2];

            const section = document.getElementById(`assignSection-${type}-${rowId}`);
            const jobModal = document.getElementById(`job-modal-${type}-${rowId}`);

            const isHidden = section.style.display === "none" || section.style.display === "";
            section.style.display = isHidden ? "block" : "none";

            if (isHidden) {
                jobModal?.classList.add("wide");
            } else {
                jobModal?.classList.remove("wide");
            }
        }
    });

    document.addEventListener("change", function(e) {
        if (e.target && e.target.id.startsWith("toggleAssignedTask-")) {
            const wrapper = e.target.closest('.list-group');
            if (!wrapper) return;

            wrapper.querySelectorAll('input[name="assignedTask[]"]:not(:disabled)').forEach(cb => {
                cb.checked = e.target.checked;
            });
        }
    });

    document.addEventListener("input", function(e) {
        if (e.target && e.target.id.startsWith("descriptionSource-")) {
            const parts = e.target.id.split("-");
            const type = parts[1]; // 'main' or 'unCo'
            const rowId = parts[2];

            const reasonTarget = document.getElementById(`reasonTarget-${type}-${rowId}`);
            if (reasonTarget) {
                reasonTarget.value = e.target.value;
            }
        }
    });


    document.addEventListener("input", function(e) {
        if (e.target && e.target.id.startsWith("numberDeviceSource-")) {
            const parts = e.target.id.split("-");
            const type = parts[1]; // 'main' or 'unComplete'
            const rowId = parts[2]; // the numeric ID

            const container = document.getElementById(`device-number-container-${type}-${rowId}`);
            if (container) {
                const firstInput = container.querySelector("input[type='text']");
                if (firstInput) {
                    firstInput.value = e.target.value;
                }
            }
        }
    });
</script>

<?php SC5() ?>
</body>

</html>