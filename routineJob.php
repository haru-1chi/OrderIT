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
                        ORDER BY dp.id DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="d-flex justify-content-end align-items-center">
                    <form action="" method="post">
                        <div class="d-flex gap-4">
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
                            <th scope="col">วันที่สร้าง</th>
                            <th scope="col">เวลาที่แจ้ง</th>
                            <th scope="col">รูปแบบการทำงาน</th>
                            <th scope="col">หมายเลขครุภัณฑ์</th>
                            <th scope="col">อาการที่ได้รับแจ้ง</th>
                            <th scope="col">ผู้แจ้ง</th>
                            <th scope="col">หน่วยงาน</th>
                            <th scope="col">เบอร์โทร</th>
                            <th scope="col">ระดับความเร่งด่วน</th>
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
                                <td>
                                    <button type="button"
                                        class="btn mb-3 btn-primary"
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
                                                                <h4 class="mt-0 mb-3" id="staticBackdropLabel">กำหนดการทำซ้ำ</h4>
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

        </div>
    </div>
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