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
            width: 800px;
            transition: width 0.3s ease;
        }

        .job-modal.wide {
            width: 750px;
        }

        .job-modal-content {
            width: 800px;
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
        <h1 class="text-center my-4">⏰งานประจำวัน</h1>
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
                $sql = "SELECT dp.*, dt.depart_name, rt.id as repeat_id, rt.weekdays, rt.monthdays
                        FROM routine_template AS dp
                        LEFT JOIN depart AS dt ON dp.department = dt.depart_id
                        LEFT JOIN repeat_task AS rt ON dp.id = rt.report_id
                        ORDER BY dp.id ASC";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="d-flex justify-content-between align-items-center">
                    <form action="" method="post">
                        <div class="d-flex gap-4">
                            <input type="date" value="<?= isset($dateStart) ? $dateStart : ''; ?>" name="dateStart_my_work"
                                class="form-control" style="width: 250px;">
                            <input type="date" value="<?= isset($dateEnd) ? $dateEnd : ''; ?>" name="dateEnd_my_work"
                                class="form-control" style="width: 250px;">
                            <button type="submit" name="checkDate_my_work" class="btn btn-primary">ยืนยัน</button>
                        </div>
                    </form>
                    <button type="button" class="btn btn-success" onclick="toggleModal('#createModalTask')">+ สร้างงาน Routine</button>
                </div>

                <hr>

                <table id="dataAll" class="table table-primary">
                    <thead>
                        <tr>
                            <th class="text-center" scope="col">ลำดับ</th>
                            <th scope="col">วันที่สร้าง</th>
                            <th scope="col">เวลาที่แจ้ง</th>
                            <th scope="col">รูปแบบการทำงาน</th>
                            <th scope="col">อาการที่ได้รับแจ้ง</th>
                            <th scope="col">ผู้แจ้ง</th>
                            <th scope="col">หน่วยงาน</th>
                            <th scope="col">เบอร์โทร</th>
                            <th scope="col">ระดับความเร่งด่วน</th>
                            <th scope="col">สถานะ</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($result as $row) {
                            $timeString = $row['time_report'];
                            $timeFormatted = date('H:i', strtotime($timeString)) . ' น.';

                            $dateString = $row['date_create'];
                            $timestamp = strtotime($dateString);
                            $dateFormatted = date('d/m/Y', $timestamp);
                        ?>
                            <tr>
                                <td class="text-start" scope="row"><?= $row['id'] ?></td>
                                <td class="text-start"><?= $dateFormatted ?></td>
                                <td class="text-start"><?= $timeFormatted ?></td>
                                <td class="text-start"><?= $row['device'] ?></td>
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
                                <td class="align-middle">
                                    <?php if ($row['template_status'] == 1): ?>
                                        <div class="bg-warning rounded-2 text-white text-center">
                                            กำลังใช้งาน
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-secondary rounded-2 text-white text-center">
                                            ปิดการใช้งาน
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="align-middle">
                                    <button type="button"
                                        class="btn btn-primary"
                                        onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')">
                                        ดูข้อมูล
                                    </button>

                                    <form action="system/update.php" method="post">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <!-- modal -->
                                        <div id="workflowModalTask<?= $row['id'] ?>" class="modal" style="display: none;">
                                            <div class="p-5 d-flex justify-content-center gap-4">
                                                <div class="modal-content job-modal" id="job-modal-main-<?= $row['id'] ?>">
                                                    <div class="modal-header justify-content-between">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                                                        <div class="d-flex align-items-center">
                                                            <button type="button" class="btn-close" onclick="toggleModal('#workflowModalTask<?= $row['id'] ?>')"></button>
                                                        </div>
                                                    </div>
                                                    <div class="modal-body d-flex">
                                                        <div class="job-modal-content">
                                                            <div class="row">
                                                                <div class="col-4">
                                                                    <label>เลขงานประจำวัน</label>
                                                                    <input type="text" class="form-control"
                                                                        value="<?= $row['id'] ?>" disabled>
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>วันที่สร้าง</label>
                                                                    <input type="date" class="form-control" name="date_create"
                                                                        value="<?= $row['date_create'] ?>" disabled>
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>เวลาแจ้ง</label>
                                                                    <input type="time" class="form-control" name="time_report"
                                                                        value="<?= date('H:i', strtotime($row['time_report'])) ?>">
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-4">
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
                                                                <div class="col-4">
                                                                    <label>ประเภทงาน</label>
                                                                    <select name="work_type" class="form-select work-type">
                                                                        <option value="" <?= empty($row['work_type']) ? 'selected' : '' ?>>เลือก...</option>
                                                                        <option value="incident" <?= ($row['work_type'] === 'incident') ? 'selected' : '' ?>>อุบัติการณ์</option>
                                                                        <option value="อื่นๆ" <?= ($row['work_type'] === 'อื่นๆ') ? 'selected' : '' ?>>อื่นๆ</option>
                                                                    </select>
                                                                </div>

                                                                <div class="col-4">
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
                                                                <div class="col-4">
                                                                    <label>ผู้แจ้ง</label>
                                                                    <input type="text" class="form-control" name="reporter"
                                                                        value="<?= $row['reporter'] ?>">
                                                                </div>
                                                                <div class="col-4">
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
                                                                <div class="col-4">
                                                                    <label>เบอร์ติดต่อกลับ</label>
                                                                    <input type="text" class="form-control" name="tel"
                                                                        value="<?= $row['tel'] ?>">
                                                                </div>
                                                            </div>

                                                            <div class="row">

                                                                <div class="col-4">
                                                                    <label for="deviceInput">อุปกรณ์</label>
                                                                    <select class="form-select" id="deviceInput<?= $row['id'] ?>" name="deviceName" required>
                                                                        <option value="<?= $row['deviceName'] ?>" selected><?= $row['deviceName'] ?></option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-4">
                                                                    <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                                                    <input value="<?= $row['number_device'] ?>" type="text"
                                                                        class="form-control" name="number_devices" id="numberDeviceSource-main-<?= $row['id'] ?>">
                                                                </div>
                                                                <div class="col-4">
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
                                                                <div class="col-12">
                                                                    <div class="d-flex justify-content-between align-items-center">
                                                                        <label>รายละเอียด<span style="color: red;">*</span></label>
                                                                    </div>
                                                                    <textarea class="form-control" name="description" rows="2" id="descriptionSource-main-<?= $row['id'] ?>"><?= $row['description'] ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <label>หมายเหตุ</label>
                                                                    <input value="<?= $row['note'] ?>" type="text"
                                                                        class="form-control" name="noteTask">
                                                                </div>
                                                                <div class="col-6">
                                                                    <label>ผู้คีย์งาน</label>
                                                                    <input value="<?= $row['create_by'] ?>" type="text"
                                                                        class="form-control" name="create_by" disabled>
                                                                    <input value="<?= $row['create_by'] ?>" type="hidden"
                                                                        class="form-control" name="create_by">
                                                                </div>
                                                            </div>

                                                            <hr class="mb-2">
                                                            <!-- !!!!! -->
                                                            <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                                                            <div class="row">
                                                                <div class="col-4">
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
                                                                <div class="col-4">
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
                                                                <div class="col-4">
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
                                                            <hr class="mb-2">
                                                            <div class="row mt-3">
                                                                <h4 class="mt-0 mb-3" id="staticBackdropLabel">กำหนด Routine <span class="text-mute fs-6">(เลือกอย่างใดอย่างหนึ่ง)</span></h4>
                                                                <div class="col-6">
                                                                    <p class="mb-2">ทำซ้ำทุกวันในสัปดาห์</p>
                                                                    <?php
                                                                    $checkedWeekdays = !empty($row['weekdays']) ? explode(',', $row['weekdays']) : [];
                                                                    ?>
                                                                    <div class="list-group ms-5 me-5">
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Mon" value="Mon" name="weekdays[]" <?= in_array('Mon', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🟡ทุกวันจันทร์
                                                                        </label>
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Tue" value="Tue" name="weekdays[]" <?= in_array('Tue', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🩷ทุกวันอังคาร
                                                                        </label>
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Wed" value="Wed" name="weekdays[]" <?= in_array('Wed', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🟢ทุกวันพุธ
                                                                        </label>
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Thu" value="Thu" name="weekdays[]" <?= in_array('Thu', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🟠ทุกวันพฤหัส
                                                                        </label>
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Fri" value="Fri" name="weekdays[]" <?= in_array('Fri', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🔵ทุกวันศุกร์
                                                                        </label>
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Sat" value="Sat" name="weekdays[]" <?= in_array('Sat', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🟣ทุกวันเสาร์
                                                                        </label>
                                                                        <label class="list-group-item">
                                                                            <input class="form-check-input" type="checkbox" id="Sun" value="Sun" name="weekdays[]" <?= in_array('Sun', $checkedWeekdays) ? 'checked' : '' ?>>
                                                                            🔴ทุกวันอาทิตย์
                                                                        </label>
                                                                    </div>
                                                                </div>

                                                                <div class="col-6 border-start">
                                                                    <p class="mb-2" for="multiDate">ทำซ้ำทุกวันที่ในเดือน <span class="text-muted">(เลือกได้หลายวัน)</span></p>
                                                                    <div class="d-flex justify-content-center">
                                                                        <div id="multiDate<?= $row['id'] ?>"></div>
                                                                        <input type="hidden" name="monthdays" id="monthdays<?= $row['id'] ?>"
                                                                            value="<?= $row['monthdays'] ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer">

                                                        <input type="checkbox"
                                                            class="btn-check switchBtn"
                                                            id="switchBtn<?= $row['id'] ?>"
                                                            data-id="<?= $row['id'] ?>"
                                                            name="template_status"
                                                            value="1"
                                                            <?= $row['template_status'] == 1 ? 'checked' : '' ?>>

                                                        <label class="btn <?= $row['template_status'] == 1 ? 'btn-warning' : 'btn-secondary' ?> switchLabel"
                                                            for="switchBtn<?= $row['id'] ?>">
                                                            <?= $row['template_status'] == 1 ? 'เปิดการใช้งาน' : 'ปิดการใช้งาน' ?>
                                                        </label>

                                                        <button type="submit" class="btn btn-primary"
                                                            onclick="removeHiddenInputOnModalClose('#requisitionModal<?= $row['id'] ?>')"
                                                            name="updateTemplate">บันทึก</button>
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
        <!-- create modal -->
        <div id="createModalTask" class="modal" style="display: none;">
            <div class="p-5 d-flex justify-content-center gap-4">
                <div class="modal-content job-modal" id="job-modal-main-create">
                    <form action="system/insert.php" method="post">
                        <div class="modal-header justify-content-between">
                            <h1 class="modal-title fs-5" id="staticBackdropLabel">สร้างงานประจำวัน</h1>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn-close" onclick="toggleModal('#createModalTask')"></button>
                            </div>
                        </div>
                        <div class="modal-body d-flex">

                            <div class="job-modal-content">
                                <div class="row">
                                    <div class="col-6">
                                        <label>วันที่สร้าง</label>
                                        <input type="date" class="form-control auto-date" name="date_create">
                                    </div>
                                    <div class="col-6">
                                        <label>เวลาแจ้ง</label>
                                        <input type="time" class="form-control auto-time" name="time_report">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label>รูปแบบการทำงาน<span style="color: red;">*</span></label>
                                        <select class="form-select" name="device"
                                            aria-label="Default select example">
                                            <option value="">
                                                -
                                            </option>
                                            <?php
                                            $sql = "SELECT * FROM workinglist";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->execute();
                                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($checkD as $d) {
                                            ?>
                                                <option value="<?= $d['workingName'] ?>">
                                                    <?= $d['workingName'] ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label>ประเภทงาน</label>
                                        <select name="work_type" class="form-select work-type">
                                            <option value="">เลือก...</option>
                                            <option value="incident">อุบัติการณ์</option>
                                            <option value="อื่นๆ">อื่นๆ</option>
                                        </select>
                                    </div>

                                    <div class="col-4">
                                        <label>ระดับความเร่งด่วน</label>
                                        <select name="priority" class="form-select priority">
                                            <option value="">เลือก...</option>
                                            <option value="4">🔴เร่งด่วน</option>
                                            <option value="3">🟡กลาง</option>
                                            <option value="2">🔵ปกติ</option>
                                            <option value="1">⏰งานประจำวัน</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label>ผู้แจ้ง</label>
                                        <input type="text" class="form-control" name="reporter"
                                            value="">
                                    </div>
                                    <div class="col-4">
                                        <label>หน่วยงาน</label>
                                        <select class="form-select" name="department" id="departIdcreate" required>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label>เบอร์ติดต่อกลับ</label>
                                        <input type="text" class="form-control" name="tel"
                                            value="">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4">
                                        <label for="deviceInput">อุปกรณ์</label>
                                        <select class="form-select" id="deviceInputcreate" name="deviceName" required></select>
                                    </div>
                                    <div class="col-4">
                                        <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                                        <input value="" type="text"
                                            class="form-control" name="number_devices" id="numberDeviceSource-main-create">
                                    </div>
                                    <div class="col-4">
                                        <label>หมายเลข IP addrees</label>
                                        <input type="text" class="form-control" name="ip_address"
                                            value="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label>อาการที่ได้รับแจ้ง</label>
                                        <input type="text" class="form-control" name="report_work"
                                            value="">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label>รายละเอียด<span style="color: red;">*</span></label>
                                        </div>
                                        <textarea class="form-control" name="description" rows="2" id="descriptionSource-main-create"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label>หมายเหตุ</label>
                                        <input value="" type="text"
                                            class="form-control" name="noteTask">
                                    </div>
                                    <div class="col-6">
                                        <label>ผู้คีย์งาน</label>
                                        <input value="<?= $name ?>" type="text"
                                            class="form-control" name="create_by" disabled>
                                        <input value="<?= $name ?>" type="hidden"
                                            class="form-control" name="create_by">
                                    </div>
                                </div>

                                <hr class="mb-2">
                                <!-- !!!!! -->
                                <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                                <div class="row">
                                    <div class="col-4">
                                        <label>ปัญหาอยู่ใน SLA หรือไม่<span style="color: red;">*</span></label>
                                        <select class="form-select" name="sla"
                                            aria-label="Default select example">
                                            <option value="">
                                                -
                                            </option>
                                            <?php
                                            $sql = "SELECT * FROM sla";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->execute();
                                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                            foreach ($checkD as $d) {
                                            ?>
                                                <option value="<?= $d['sla_name'] ?>">
                                                    <?= $d['sla_name'] ?>
                                                </option>
                                            <?php
                                            }
                                            ?>

                                        </select>
                                    </div>
                                    <div class="col-4">
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
                                            <option value="" selected>
                                                -
                                            </option>
                                            <?php foreach ($allKpis as $kpiName): ?>
                                                <option value="<?= $kpiName ?>"><?= $kpiName ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <label>Activity Report<span style="color: red;">*</span></label>
                                        <select class="form-select" name="problem"
                                            aria-label="Default select example">
                                            <?php
                                            $sql = "SELECT * FROM problemlist";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->execute();
                                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                                            <option value=""
                                                selected>
                                                -
                                            </option>
                                            <?php foreach ($data as $d) {
                                            ?>
                                                <option value="<?= $d['problemName'] ?>">
                                                    <?= $d['problemName'] ?>
                                                </option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <hr class="mb-2">
                                <div class="row mt-3">
                                    <h4 class="mt-0 mb-3" id="staticBackdropLabel">กำหนด Routine <span class="text-mute fs-6">(เลือกอย่างใดอย่างหนึ่ง)</span></h4>
                                    <div class="col-6">
                                        <p class="mb-2">ทำซ้ำทุกวันในสัปดาห์</p>
                                        <div class="list-group ms-5 me-5">
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Mon" value="Mon" name="weekdays[]">
                                                🟡ทุกวันจันทร์
                                            </label>
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Tue" value="Tue" name="weekdays[]">
                                                🩷ทุกวันอังคาร
                                            </label>
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Wed" value="Wed" name="weekdays[]">
                                                🟢ทุกวันพุธ
                                            </label>
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Thu" value="Thu" name="weekdays[]">
                                                🟠ทุกวันพฤหัส
                                            </label>
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Fri" value="Fri" name="weekdays[]">
                                                🔵ทุกวันศุกร์
                                            </label>
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Sat" value="Sat" name="weekdays[]">
                                                🟣ทุกวันเสาร์
                                            </label>
                                            <label class="list-group-item">
                                                <input class="form-check-input" type="checkbox" id="Sun" value="Sun" name="weekdays[]">
                                                🔴ทุกวันอาทิตย์
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-6 border-start">
                                        <p class="mb-2" for="multiDate">ทำซ้ำทุกวันที่ในเดือน <span class="text-muted">(เลือกได้หลายวัน)</span></p>
                                        <div class="d-flex justify-content-center">
                                            <div id="multiDate"></div>
                                        </div>
                                        <input type="hidden" name="monthdays" id="monthdays" class="form-control mt-3">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"
                                onclick="removeHiddenInputOnModalClose('#createModalTask')"
                                name="insertTemplate">บันทึก</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        </div>
    </div>
    <script>
        document.querySelectorAll('.switchBtn').forEach(input => {
            input.addEventListener('change', function() {
                const label = document.querySelector(`label[for="${this.id}"]`);
                if (this.checked) {
                    label.classList.remove('btn-secondary');
                    label.classList.add('btn-warning');
                    label.textContent = 'เปิดการใช้งาน';
                } else {
                    label.classList.remove('btn-warning');
                    label.classList.add('btn-secondary');
                    label.textContent = 'ปิดการใช้งาน';
                }
            });
        });
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        <?php foreach ($result as $row): ?>
            flatpickr("#multiDate<?= $row['id'] ?>", {
                inline: true,
                mode: "multiple",
                dateFormat: "Y-m-d", // let flatpickr work with real dates
                defaultDate: [
                    <?php
                    if (!empty($row['monthdays'])) {
                        $days = explode(',', $row['monthdays']);
                        foreach ($days as $d) {
                            $day = (int)trim($d);
                            echo "'2025-01-" . str_pad($day, 2, '0', STR_PAD_LEFT) . "',";
                        }
                    }
                    ?>
                ],
                onReady: function(selectedDates, dateStr, instance) {
                    // prefill hidden field on load
                    const days = selectedDates.map(d => d.getDate());
                    document.getElementById("monthdays<?= $row['id'] ?>").value = days.join(",");
                },
                onChange: function(selectedDates, dateStr, instance) {
                    const days = selectedDates.map(d => d.getDate());
                    document.getElementById("monthdays<?= $row['id'] ?>").value = days.join(",");
                }
            });
        <?php endforeach; ?>

        flatpickr("#multiDate", {
            inline: true,
            mode: "multiple",
            dateFormat: "d",
            onChange: function(selectedDates, dateStr, instance) {
                const days = selectedDates.map(d => d.getDate());
                document.getElementById("monthdays").value = days.join(",");
                console.log("Selected days:", days.join(",")); // for debug
            }
        });
    </script>

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

    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $('#dataAll').DataTable({
            order: [
                [10, 'asc']
            ]
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const now = new Date();
            // Format current date
            const formattedDate = now.toLocaleDateString('en-CA');
            document.querySelectorAll('.auto-date').forEach(input => {
                input.value = formattedDate;
            });

            // Format current time as HH:mm
            const currentTime = now.toTimeString().slice(0, 5); // HH:mm
            document.querySelectorAll('.auto-time').forEach(input => {
                input.value = currentTime;
            });
        });
    </script>
    <script>
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