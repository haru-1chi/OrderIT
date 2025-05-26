<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

if (isset($_SESSION['admin_log'])) {
  $admin = $_SESSION['admin_log'];
  $sql = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admin WHERE username = :admin";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":admin", $admin);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $fullname = $result['full_name'];
}
if (!isset($_SESSION["admin_log"])) {
  $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
  header("location: login.php");
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php bs5() ?>
  <title>ตรวจสอบ | IT ORDER PRO</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    body {
      background-color: #F9FDFF;
    }

    .ui-autocomplete {
      z-index: 1055 !important;
    }

    .container-custom {
      max-width: 1500px;
      margin-left: auto;
      margin-right: auto;
    }

    .modal-container {
      background-color: rgba(0, 0, 0, 0.5);
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
  </style>
</head>

<body>

  <?php navbar();
  ?>

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

  $sql = "SELECT * FROM orderdata_new ORDER BY id DESC";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $d = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Step 2: Default to the latest numberWork if none is provided
  $numberWork = $_GET['numberWork'] ?? $d[0]['numberWork'] ?? null;

  $sql = "
SELECT 
od.*, 
wd.withdraw_name, 
lw.work_name, 
dv.device_name, 
dv.device_id, 
dp.depart_name, 
of.offer_name,
nd.numberDevice, 
nd.id AS numberDevice_id, 
nd.is_deleted AS deleted_numberDevice, 
oi.id AS item_id, 
oi.list, 
oi.quality, 
oi.amount, 
oi.price, 
oi.unit,
oi.is_deleted AS deleted_item
FROM 
orderdata_new AS od
LEFT JOIN 
withdraw AS wd ON od.refWithdraw = wd.withdraw_id
LEFT JOIN 
offer AS of ON od.refOffer = of.offer_id
LEFT JOIN 
depart AS dp ON od.refDepart = dp.depart_id
LEFT JOIN 
listwork AS lw ON od.refWork = lw.work_id
LEFT JOIN 
device AS dv ON od.refDevice = dv.device_id
LEFT JOIN 
order_numberdevice AS nd ON od.id = nd.order_item
LEFT JOIN 
order_items AS oi ON od.id = oi.order_id
WHERE 
(od.numberWork = :numberWork)
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
      if (!empty($row['numberDevice_id']) && !isset($devices[$row['numberDevice_id']]) && ($row['deleted_numberDevice'] != '1')) {
        $devices[$row['numberDevice_id']] =
          [
            'numberDevice_id' => $row['numberDevice_id'],
            'numberDevice' => $row['numberDevice'],
          ];
      }

      // Collect unique items
      if (!empty($row['item_id']) && !isset($items[$row['item_id']]) && ($row['deleted_item'] != '1')) {
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
  ?>


  <div class="container-custom mt-5">
    <div class="mt-5">
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
      <h1 class="text-center mt-5">ตรวจสอบใบเบิก</h1>
      <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#requisitionModal">+ สร้างใบเบิก</button>
      </div>
      <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-6">
          <div class="row me-1">
            <div class="card col-sm-12">
              <form action="" method="GET">
                <div class="row p-3">
                  <div class="col-6">
                    <label class="form-label" for="assetInput">ค้นหา</label>
                    <input class="form-control" type="text" id="assetInput">
                    <input type="hidden" id="assetInputName" name="">
                  </div>
                  <div class="col-6">
                    <label class="form-label">หมายเลขออกงาน</label>
                    <?php
                    $sql = "
                   SELECT od.id AS order_id, od.numberWork, os.status
                   FROM orderdata_new AS od
                   LEFT JOIN (
                     SELECT order_id, status
                     FROM order_status AS os1
                     WHERE (os1.timestamp, os1.status) IN (
                       SELECT MAX(os2.timestamp) AS latest_timestamp, MAX(os2.status) AS latest_status
                       FROM order_status AS os2
                       WHERE os2.order_id = os1.order_id
                     )
                   ) AS os ON os.order_id = od.id
                   ORDER BY od.id DESC
                 ";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $d = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    ?>
                    <select class="form-select" id="numberWork" name="numberWork">
                      <?php foreach ($d as $row) {
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
                      ?>
                        <option value="<?= $row['numberWork'] ?>" <?php echo ($numberWork == $row['numberWork']) ? 'selected' : ''; ?>>
                          <?= $row['numberWork'] . ' ' . "( " . $statusTxtSlect . " )" ?>
                        </option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <script>
                  document.getElementById('numberWork').addEventListener('change', function() {
                    var numberWork = this.value;
                    window.location.href = '?numberWork=' + numberWork;
                  });
                </script>
                <?php
                // Check if numberWork is set in the query string
                if (isset($_GET['numberWork'])) {
                  $numberWork = $_GET['numberWork'];

                  // Check if the selected numberWork exists in the database
                  $isValidNumberWork = false;
                  foreach ($d as $row) {
                    if ($numberWork == $row['numberWork']) {
                      $isValidNumberWork = true;
                      break;
                    }
                  }

                  if (!$isValidNumberWork) {
                    // Redirect to a default page or display an error message
                    header("Location: check.php");
                    exit();
                  }
                }


                // Buttons for navigation
                $currentWorkId = isset($_GET['numberWork']) ? $_GET['numberWork'] : (isset($d[0]) ? $d[0]['numberWork'] : null);

                // Find the current index of numberWork
                $currentIndex = array_search($currentWorkId, array_column($d, 'numberWork'));

                // Function to render navigation buttons
                function renderNavigationButton($label, $newIndex, $d, $isDisabled, $btnClass)
                {
                  $url = $isDisabled ? '#' : '?numberWork=' . $d[$newIndex]['numberWork'];
                  $disabledAttr = $isDisabled ? 'disabled' : '';
                  echo '<button type="button" class="btn ' . $btnClass . '  mb-3"' . $disabledAttr . ' onclick="window.location.href=\'' . $url . '\'">' . $label . '</button>';
                }

                // Render the "Previous" button
                $prevIndex = $currentIndex - 1;
                $isPrevDisabled = $prevIndex < 0;

                // Render the "Next" button
                $nextIndex = $currentIndex + 1;
                $isNextDisabled = $nextIndex >= count($d);
                ?>

                <div class="col-12">
                  <div class="d-flex justify-content-end">
                    <?php
                    renderNavigationButton('ย้อนกลับ', $nextIndex, $d, $isNextDisabled, 'btn-secondary me-3');
                    renderNavigationButton('ถัดไป', $prevIndex, $d, $isPrevDisabled, 'btn-primary');
                    ?>
                  </div>
                </div>
              </form>
            </div>
            <form action="export.php" method="post">
              <div class="d-flex justify-content-end">
                <button type="submit" name="DataAll" class="btn btn-primary my-3 p-3">Export to Excel</button>
              </div>
            </form>
            <?php

            $sql = "
SELECT os.status, COUNT(*) AS count
FROM orderdata_new AS od
LEFT JOIN (
    SELECT order_id, status
    FROM order_status AS os1
    WHERE (os1.timestamp, os1.status) IN (
                       SELECT MAX(os2.timestamp) AS latest_timestamp, MAX(os2.status) AS latest_status
                       FROM order_status AS os2
                       WHERE os2.order_id = os1.order_id
                     )
) AS os ON os.order_id = od.id
WHERE os.status IS NOT NULL  
GROUP BY os.status
ORDER BY os.status;

";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $statusOptions = array(
              1 => array(
                'text' => "รอรับเอกสารจากหน่วยงาน",
                'color' => "#FFAE2C"
              ),
              2 => array(
                'text' => "รอส่งเอกสารไปพัสดุ",
                'color' => "#6CB1FF"
              ),
              3 => array(
                'text' => "รอพัสดุสั่งของ",
                'color' => "#7ECC7A"
              ),
              4 => array(
                'text' => "รอหมายเลขครุภัณฑ์",
                'color' => "#FF9359"
              ),
              5 => array(
                'text' => "ปิดงาน",
                'color' => "#51A075"
              ),
              6 => array(
                'text' => "ยกเลิก",
                'color' => "#FF7575"
              )
            );

            foreach ($statusCounts as $statusCount) {
              $status = $statusCount['status'];
              $count = $statusCount['count'];

              // Use default values if status is not mapped
              $textS = isset($statusOptions[$status]['text']) ? $statusOptions[$status]['text'] : "ไม่ระบุสถานะ";
              $color = isset($statusOptions[$status]['color']) ? $statusOptions[$status]['color'] : sprintf('#%06X', rand(0, 0xFFFFFF));
            ?>

              <div class="col-sm-6">
                <div class="card text-white mb-3" style="background-color: <?= $color ?>">
                  <div class="card-body">
                    <h1 class="card-title" style="font-size: 50px;"><?= $count ?></h1>
                    <h5 class="m-0"><?= htmlspecialchars($textS) ?></h5>
                  </div>
                  <div class="card-footer">
                    <h5 class="card-text text-end">
                      <a href="checkStatus.php?status=<?= urlencode($status) ?>" class="text-white" style="text-decoration: none;"> ▽ รายละเอียดเพิ่มเติม</a>
                    </h5>
                  </div>
                </div>
              </div>

            <?php
            }
            $orderId = $order['id'];
            $sqlHistory = "SELECT * FROM order_history WHERE order_id = :order_id ORDER BY edited_at DESC";
            $stmtHistory = $conn->prepare($sqlHistory);
            $stmtHistory->bindParam(":order_id", $orderId);
            $stmtHistory->execute();
            $orderHistories = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

            // Group history by field_name for easier lookup
            $historyByField = [];
            foreach ($orderHistories as $history) {
              $field = $history['field_name'];
              $historyByField[$field][] = $history;
            }

            function renderEditHistory($fieldName, $historyByField)
            {
              if (!empty($historyByField[$fieldName])) {
                $log = $historyByField[$fieldName][0];
                echo '<div class="small text-danger text-end">*' . htmlspecialchars($log['edited_by']) . '</div>';
              }
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $orderId = $order['id'];
            $sqlHistory = "SELECT * FROM order_history WHERE order_id = :order_id AND table_name = 'order_items' ORDER BY edited_at DESC";
            $stmtHistory = $conn->prepare($sqlHistory);
            $stmtHistory->bindParam(":order_id", $orderId);
            $stmtHistory->execute();
            $orderItemHistories = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

            // Group by item_id (table_id)
            $itemHistoryByItemId = [];
            foreach ($orderItemHistories as $history) {
              $itemId = $history['table_id'];
              if (!isset($itemHistoryByItemId[$itemId])) {
                $itemHistoryByItemId[$itemId] = $history; // Only keep latest edit
              }
            }

            function renderItemEditorInfo($itemId, $itemHistoryByItemId)
            {
              if (!empty($itemHistoryByItemId[$itemId])) {
                $action = htmlspecialchars($itemHistoryByItemId[$itemId]['action']);
                $editor = htmlspecialchars($itemHistoryByItemId[$itemId]['edited_by']);
                echo '<div class="small text-danger text-end">*' . $action . ': ' . $editor . '</div>';
              }
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $orderId = $order['id'];
            $sqlHistory = "SELECT * FROM order_history WHERE order_id = :order_id AND table_name = 'order_numberdevice' ORDER BY edited_at DESC";
            $stmtHistory = $conn->prepare($sqlHistory);
            $stmtHistory->bindParam(":order_id", $orderId);
            $stmtHistory->execute();
            $orderItemHistories = $stmtHistory->fetchAll(PDO::FETCH_ASSOC);

            // Group by item_id (table_id)
            $numberdeviceHistoryByItemId = [];
            foreach ($orderItemHistories as $history) {
              $itemId = $history['table_id'];
              if (!isset($numberdeviceHistoryByItemId[$itemId])) {
                $numberdeviceHistoryByItemId[$itemId] = $history; // Only keep latest edit
              }
            }

            function renderNumberEditorInfo($itemId, $numberdeviceHistoryByItemId)
            {
              if (!empty($numberdeviceHistoryByItemId[$itemId])) {
                $action = htmlspecialchars($numberdeviceHistoryByItemId[$itemId]['action']);
                $editor = htmlspecialchars($numberdeviceHistoryByItemId[$itemId]['edited_by']);
                echo '<div class="small text-danger text-end">*' . $action . ': ' . $editor . '</div>';
              }
            }
            ?>
          </div>
        </div>

        <div class="card col-sm-12 col-md-12 col-lg-6">
          <?php
          if (!empty($order)) {
          ?>
            <form action="system/update.php" method="POST">
              <div class="d-flex justify-content-between align-items-center my-3">
                <div>
                  <h4><?= $numberWork ?></h4>
                </div>

                <div>
                  <input type="hidden" name="id" value="<?= $order['id'] ?>">
                  <input type="hidden" name="numberWork" value="<?= $numberWork ?>">
                  <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#copiedRequisitionModal">คัดลอกไปยังใบเบิกใหม่</button>
                  <button id="editData" type="button" class="btn btn-warning p-2" style="display: inline-block;">แก้ไข</button>
                  <button id="saveData" type="submit" name="updateData" class="btn btn-success p-2" style="display: none;">บันทึก</button>
                </div>

              </div>

              <div class="row">
                <div class="col-3">
                  <label>ผูกหมายเลขงาน(ถ้ามี)</label>
                  <input type="text" class="form-control" name="id_ref"
                    value="<?= $order['id_ref'] ?? '' ?>" disabled>
                  <?php renderEditHistory('id_ref', $historyByField); ?>
                </div>

                <div class="col-3">
                  <label>วันที่ออกใบเบิก</label>
                  <input type="date" class="form-control"
                    value="<?= $order['dateWithdraw'] ?? '' ?>" disabled>
                </div>

                <div class="col-3">
                  <div class="d-flex justify-content-between">
                    <label for="inputGroupSelect01">ประเภทการเบิก</label>
                    <?php renderEditHistory('refWithdraw', $historyByField); ?>
                  </div>
                  <select disabled class="form-select" name="refWithdraw" id="inputGroupSelect01">
                    <?php
                    $sql = 'SELECT * FROM withdraw';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $withdraws = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($withdraws as $withdraw) {
                      $selected = ($withdraw['withdraw_id'] == $order['refWithdraw']) ? 'selected' : ''; ?>
                      <option value="<?= $withdraw['withdraw_id'] ?>" <?= $selected ?>><?= $withdraw['withdraw_name'] ?></option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-3">
                  <div class="d-flex justify-content-between">
                    <label for="inputGroupSelect01">ประเภทงาน</label>
                    <?php renderEditHistory('refWork', $historyByField); ?>
                  </div>
                  <select disabled class="form-select" name="refWork" id="inputGroupSelect01">
                    <?php
                    $sql = 'SELECT * FROM listwork';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $listworks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($listworks as $listwork) {
                      $selected = ($listwork['work_id'] == $order['refWork']) ? 'selected' : '';
                    ?>
                      <option value="<?= $listwork['work_id'] ?>" <?= $selected ?>><?= $listwork['work_name'] ?></option>
                    <?php }
                    ?>
                  </select>
                </div>
              </div>

              <div class="row">
                <div class="col-6">
                  <div class="d-flex justify-content-between">
                    <label>ส่งซ่อมอุปกรณ์ คอมพิวเตอร์</label>
                    <?php renderEditHistory('refDevice', $historyByField); ?>
                  </div>
                  <select disabled required class="form-select" name="refDevice" id="inputGroupSelect01">
                    <?php
                    $sql = 'SELECT * FROM device';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $deviceLists = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($deviceLists as $device) {
                      $isSelected = ($device['device_id'] === $order['device_id']) ? 'selected' : '';
                    ?>
                      <option value="<?= $device['device_id'] ?>" <?= $isSelected ?>>
                        <?= $device['device_name'] ?>
                      </option>
                    <?php } ?>
                  </select>
                </div>

                <div class="col-6">
                  <label>หมายเลขพัสดุ / ครุภัณฑ์</label>
                  <div id="device-number-container-main">
                    <?php if (!empty($devices)) { ?>
                      <?php foreach ($devices as $index => $device): ?>
                        <div class="d-flex device-number-row">
                          <input type="text" name="update_number_device[<?= $order['id'] ?>][<?= $device['numberDevice_id'] ?>]" class="form-control mb-2" value="<?= htmlspecialchars($device['numberDevice']) ?>" disabled>
                          <button type="button" class="btn btn-warning p-2 ms-3 mb-2 remove-field"
                            data-device-id="<?= $device['numberDevice_id'] ?>"
                            data-row-id="<?= $order['id'] ?>"
                            style="visibility: <?= $index === 0 ? 'hidden' : 'visible' ?>;">ลบ</button>
                        </div>
                        <?php renderNumberEditorInfo($device['numberDevice_id'], $numberdeviceHistoryByItemId); ?>
                      <?php endforeach; ?>
                    <?php  } else { ?>
                      <div class="d-flex device-number-row">
                        <input type="text" name="device_numbers[]" class="form-control mb-2" value="" disabled>
                        <button type="button" class="btn btn-danger p-2 ms-3 remove-field" style="visibility: hidden;">ลบ</button>
                      </div>
                    <?php } ?>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="button" id="add-device-number-main" class="btn btn-success mt-2 align-self-end" style="display: none;">+ เพิ่มหมายเลขครุภัณฑ์</button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-6">
                  <div class="d-flex justify-content-between">
                    <label>อาการที่รับแจ้ง</label>
                    <?php renderEditHistory('report', $historyByField); ?>
                  </div>
                  <input type="text" class="form-control" name="report"
                    value="<?= $order['report'] ?>" disabled>
                </div>

                <div class="col-6">
                  <label>ผู้รับเรื่อง</label>
                  <input type="text" class="form-control"
                    value="<?= $order['refUsername'] ?? '' ?>" disabled>
                </div>
              </div>

              <div class="row">
                <div class="col-6">
                  <div class="d-flex justify-content-between">
                    <label>รายละเอียด</label>
                    <?php renderEditHistory('reason', $historyByField); ?>
                  </div>
                  <input type="text" class="form-control" name="reason"
                    value="<?= $order['reason'] ?>" disabled>
                </div>

                <div class="col-6">
                  <div class="d-flex justify-content-between">
                    <label>หมายเหตุ</label>
                    <?php renderEditHistory('note', $historyByField); ?>
                  </div>
                  <input type="text" class="form-control" name="note"
                    value="<?= $order['note'] ?>" disabled>
                </div>
              </div>

              <div class="row">

                <div class="col-4">
                  <label>หน่วยงานที่แจ้ง</label>
                  <input type="text" class="form-control"
                    value="<?= $order['depart_name'] ?>" disabled>
                </div>

                <div class="col-4">
                  <div class="d-flex justify-content-between">
                    <label for="inputGroupSelect01">ร้านที่เสนอราคา</label>
                    <?php renderEditHistory('refOffer', $historyByField); ?>
                  </div>
                  <select disabled class="form-select" name="refOffer" id="inputGroupSelect01">
                    <?php
                    $sql = 'SELECT * FROM offer';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($offers as $offer) {
                      $selected = ($offer['offer_id'] == $order['refOffer']) ? 'selected' : '';
                    ?>
                      <option value="<?= $offer['offer_id'] ?>" <?= $selected ?>><?= $offer['offer_name'] ?></option>
                    <?php }
                    ?>
                  </select>

                </div>

                <div class="col-4">
                  <div class="d-flex justify-content-between">
                    <label>เลขที่ใบเสนอราคา</label>
                    <?php renderEditHistory('quotation', $historyByField); ?>
                  </div>

                  <input disabled type="text" name="quotation" class="form-control" value="<?= $order['quotation'] ?>">

                </div>
              </div>

              <div class="">
                <h4 class="m-0 my-3">สถานะ</h4>

                <div class="accordion" id="accordionExample">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <a href="ใบเบิกวัสดุอะไหล่.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">รอรับเอกสารจากหน่วยงาน</a>
                      </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                      <div class="accordion-body">
                        <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center my-2">
                <h4 class="m-0">รายการเบิก</h4>
                <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-main" class="fs-4 fw-bold text-primary">0</span> บาท</p>
              </div>
              <div>
                <a href="แบบฟอร์มคำขอส่งซ่อมบำรุงอุปกรณ์คอมพิวเตอร์.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">ใบซ่อม</a>
                <a href="ใบเบิกวัสดุอะไหล่.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">ใบเบิกวัสดุอะไหล่</a>
                <a href="ใบเบิกครุภัณฑ์ในแผน_รวม.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">ใบเบิกคุรภัณฑ์ในแผน</a>
                <a href="ใบเบิกปรับแผน_รวม.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">ใบเบิกคุรภัณฑ์นอกแผน</a>
                <a href="พิมพ์สติ๊กเกอร์.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">สติ๊กเกอร์งาน</a>
              </div>


              <table id="pdf" style="width: 100%;" class="table">
                <thead class="text-center table-primary">
                  <tr>
                    <th scope="col">ลำดับ</th>
                    <th scope="col">รายการ</th>
                    <th scope="col">คุณสมบัติ</th>
                    <th scope="col">จำนวน</th>
                    <th scope="col">ราคา</th>
                    <th scope="col">รวม</th>
                    <th scope="col">หน่วย</th>
                    <th scope="col" style="display: none;"></th>
                  </tr>
                </thead>

                <tbody id="table-body-main" class="text-center">
                  <?php if (!empty($items)) { ?>
                    <?php foreach ($items as $index => $item): ?>
                      <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                          <select
                            disabled
                            style="width: 150px"
                            class="form-select device-select"
                            name="update_list[<?= $order['id'] ?>][<?= $item['item_id'] ?>]">
                            <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                            <?php
                            $deviceSql = "SELECT * FROM device_models ORDER BY models_name ASC";
                            $deviceStmt = $conn->prepare($deviceSql);
                            $deviceStmt->execute();
                            $devices = $deviceStmt->fetchAll(PDO::FETCH_ASSOC);
                            $deviceOptions = '';
                            foreach ($devices as $device) {
                              $deviceOptions .= "<option value='{$device['models_id']}'>{$device['models_name']}</option>";
                              $selected = $device['models_id'] == $item['list'] ? 'selected' : '';
                              echo "<option value='{$device['models_id']}' $selected>{$device['models_name']}</option>";
                            }
                            ?>
                          </select>
                        </td>
                        <td>
                          <textarea disabled class="form-control" name="update_quality[<?= $order['id'] ?>][<?= $item['item_id'] ?>]"><?= htmlspecialchars($item['quality']) ?></textarea>
                        </td>
                        <td>
                          <input disabled name="update_amount[<?= $order['id'] ?>][<?= $item['item_id'] ?>]" value="<?= htmlspecialchars($item['amount']) ?>" style="width: 3rem;" type="text" class="form-control">
                        </td>
                        <td>
                          <input disabled name="update_price[<?= $order['id'] ?>][<?= $item['item_id'] ?>]" value="<?= htmlspecialchars($item['price']) ?>" style="width: 5rem;" type="text" class="form-control">
                        </td>
                        <td>
                          <input disabled value="<?= htmlspecialchars($item['total']) ?>" style="width: 5rem;" type="text" class="form-control no-toggle">
                        </td>
                        <td>
                          <input disabled name="update_unit[<?= $order['id'] ?>][<?= $item['item_id'] ?>]" value="<?= htmlspecialchars($item['unit']) ?>" style="width: 4rem;" type="text" class="form-control">
                          <div style="position: absolute; right: 20px;">
                            <?php renderItemEditorInfo($item['item_id'], $itemHistoryByItemId); ?>
                          </div>
                        </td>
                        <td>
                          <button type="button" class="btn btn-warning remove-row"
                            data-items-id="<?= $item['item_id'] ?>"
                            data-items-row-id="<?= $order['id'] ?>"
                            style="display: none;">ลบ</button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php  } else { ?>
                    <tr>
                      <th scope="row">1</th>
                      <td>
                        <select
                          disabled
                          style="width: 120px"
                          class="form-select device-select"
                          name="list[]">
                          <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                          <!-- Populate options dynamically -->
                          <?php
                          $sql = "SELECT * FROM device_models ORDER BY models_name ASC";
                          $stmt = $conn->prepare($sql);
                          $stmt->execute();
                          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                          $deviceOptions = '';
                          foreach ($result as $d) {
                            $deviceOptions .= "<option value='{$d['models_id']}'>{$d['models_name']}</option>";
                          ?>

                            <option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
                          <?php
                          }
                          ?>
                        </select>
                      </td>
                      <td>
                        <textarea disabled class="form-control" name="quality[]"></textarea>
                      </td>
                      <td>
                        <input disabled name="amount[]" value="" style="width: 3rem;" type="text" class="form-control">
                      </td>
                      <td>
                        <input disabled name="price[]" value="" style="width: 4rem;" type="text" class="form-control">
                      </td>
                      <td>
                        <input disabled style="width: 4rem;" type="text" class="form-control no-toggle">
                      </td>
                      <td>
                        <input disabled name="unit[]" value="" style="width: 5rem;" type="text" class="form-control">
                      </td>
                      <td>
                        <button type="button" class="btn btn-warning remove-row"
                          style="display: none;">ลบ</button>
                      </td>
                    </tr>
                  <?php } ?>
                </tbody>
              </table>
              <div class="d-flex justify-content-end">
                <button type="button" id="add-row-main" class="btn btn-success" style="display: none;">+ เพิ่มแถว</button>
              </div>
            </form>
          <?php
          } else {
            $sql = "SELECT * FROM device_models ORDER BY models_name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $deviceOptions = '';
            foreach ($result as $d) {
              $deviceOptions .= "<option value='{$d['models_id']}'>{$d['models_name']}</option>";
            }
          ?>
            <h2 class="mt-4 text-center">*ไม่พบข้อมูล*</h2>
          <?php
          }
          ?>
        </div>
      </div>
    </div>
  </div>
  <!-- modal - create mode -->
  <div id="requisitionModal" class="modal fade" tabindex="-1" aria-labelledby="requisitionModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">สร้างใบเบิก</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-3">
          <form id="formRequisitionModal" action="system/insert.php" method="post">
            <div class="row">
              <div class="col-sm-6">
                <div class="mb-3">
                  <label id="basic-addon1">วันที่ออกใบเบิก</label>
                  <input type="date" name="dateWithdraw" class="form-control thaiDateInput">
                </div>
              </div>

              <div class="col-sm-6">
                <label>ผูกหมายเลขงาน(ถ้ามี)</label>
                <input type="text" class="form-control" name="id_ref"
                  value="">
              </div>

              <div class="col-sm-6">
                <div class="mb-3">
                  <label for="inputGroupSelect01">ประเภทการเบิก</label>
                  <select required class="form-select" name="refWithdraw" id="inputGroupSelect01">
                    <?php
                    $sql = 'SELECT * FROM withdraw';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($result as $row) { ?>

                      <option value="<?= $row['withdraw_id'] ?>"><?= $row['withdraw_name'] ?></option>
                    <?php }
                    ?>

                  </select>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="mb-3">
                  <label for="inputGroupSelect01">ประเภทงาน</label>
                  <select required class="form-select" name="refWork" id="inputGroupSelect01">
                    <?php
                    $sql = 'SELECT * FROM listwork';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($result as $row) { ?>

                      <option value="<?= $row['work_id'] ?>"><?= $row['work_name'] ?></option>
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
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($result as $row) { ?>
                      <option value="<?= $row['device_id'] ?>"><?= $row['device_name'] ?></option>
                    <?php }
                    ?>

                  </select>
                </div>
              </div>

              <div class="col-6">
                <div class="mb-3">
                  <label>หมายเลขพัสดุ / ครุภัณฑ์</label>
                  <div id="device-number-container-modal">
                    <div class="d-flex device-number-row">
                      <input type="text" name="device_numbers[]" class="form-control mb-2" value="">
                      <button type="button" class="btn btn-warning p-2 ms-3 mb-2 remove-field"
                        style="visibility: hidden;">ลบ</button>
                    </div>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="button" id="add-device-number-modal" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
                  </div>
                </div>
              </div>

              <div class="col-sm-6">
                <div class="mb-3">
                  <label for="departInput">หน่วยงาน</label>
                  <input type="text" class="form-control" id="departInput" name="ref_depart">
                  <input type="hidden" id="departId" name="depart_id">
                </div>

                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.29/dist/sweetalert2.min.css">

                <!-- Add SweetAlert2 JS -->
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.29/dist/sweetalert2.min.js"></script>

                <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

                <script>
                  $(function() {
                    function setupAutocomplete(type, inputId, hiddenInputId, url, addDataUrl, confirmMessage) {
                      let inputChanged = false;
                      let alertShown = false; // Flag to track if the alert has been shown already

                      $(inputId).autocomplete({
                          source: function(request, response) {
                            $.ajax({
                              url: url,
                              dataType: "json",
                              data: {
                                term: request.term,
                                type: type
                              },
                              success: function(data) {
                                response(data); // Show suggestions
                              }
                            });
                          },
                          minLength: 1,
                          autoFocus: true,
                          select: function(event, ui) {
                            $(inputId).val(ui.item.label); // Fill input with label
                            $(hiddenInputId).val(ui.item.value); // Fill hidden input with ID
                            return false; // Prevent default behavior
                          }
                        })
                        .data("ui-autocomplete")._renderItem = function(ul, item) {
                          return $("<li>")
                            .append("<div>" + item.label + "</div>")
                            .appendTo(ul);
                        };

                      $(inputId).on("autocompletefocus", function(event, ui) {
                        console.log("Item highlighted: ", ui.item.label);
                        return false;
                      });

                      $(inputId).on("keyup", function() {
                        inputChanged = true;
                      });

                      $(inputId).on("blur", function() {
                        if (inputChanged && !alertShown) {
                          const userInput = $(this).val().trim();
                          const hiddenValue = $(hiddenInputId).val();
                          if (userInput === "") return;
                          if (hiddenValue !== "") {
                            inputChanged = false;
                            return;
                          }
                          let found = false;
                          $(this).autocomplete("instance").menu.element.find("div").each(function() {
                            if ($(this).text() === userInput) {
                              found = true;
                              return false;
                            }
                          });

                          if (!found) {
                            alertShown = true; // Prevent the alert from firing again
                            // Show SweetAlert to confirm insert data
                            Swal.fire({
                              title: confirmMessage,
                              icon: "info",
                              showCancelButton: true,
                              confirmButtonText: "ใช่",
                              cancelButtonText: "ไม่"
                            }).then((result) => {
                              if (result.isConfirmed) {
                                $.ajax({
                                  url: addDataUrl,
                                  method: "POST",
                                  data: {
                                    dataToInsert: userInput
                                  },
                                  success: function(response) {
                                    console.log("Data inserted successfully!");
                                    $(hiddenInputId).val(response); // Set inserted ID
                                  },
                                  error: function(xhr, status, error) {
                                    console.error("Error inserting data:", error);
                                  }
                                });
                              } else {
                                $(inputId).val(""); // Clear input if canceled
                                $(hiddenInputId).val("");
                              }
                              alertShown = false; // Reset the flag after the action
                            });
                          }
                        }
                        inputChanged = false; // Reset the flag
                      });
                    }

                    setupAutocomplete(
                      "depart",
                      "#departInput",
                      "#departId",
                      "autocomplete.php",
                      "insertDepart.php",
                      "คุณต้องการเพิ่มข้อมูลนี้หรือไม่?"
                    );
                  });
                </script>
              </div>

              <div class="col-sm-6">
                <div class="mb-3">
                  <label for="inputGroupSelect01">ผู้รับเรื่อง</label>
                  <input type="text" class="form-control" value="<?= $fullname ?>" disabled>
                  <input type="hidden" name="ref_username" value="<?= $fullname ?>">
                </div>
              </div>

              <div class="col-sm-6">
                <div class="mb-3">
                  <label id="basic-addon1">อาการรับแจ้ง</label>
                  <input type="text" name="report" class="form-control">
                </div>
              </div>
              <div class="col-sm-6">
                <div class="mb-3">
                  <label id="basic-addon1">เหตุผลและความจำเป็น</label>
                  <input type="text" name="reason" class="form-control">
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
                      <option value="<?= $offer['offer_id'] ?>"><?= $offer['offer_name'] ?></option>
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
                  <select required class="form-select" name="status" id="inputGroupSelect01">
                    <option value="1">รอรับเอกสารจากหน่วยงาน</option>
                    <option value="2">รอส่งเอกสารไปพัสดุ</option>
                    <option value="3">รอพัสดุสั่งของ</option>
                    <option value="4">รอหมายเลขครุภัณฑ์</option>
                    <option value="5">ปิดงาน</option>
                    <option value="6">ยกเลิก</option>
                  </select>
                </div>
              </div>

              <div class="col-sm-12">
                <div class="mb-3">
                  <label for="inputGroupSelect01">หมายเหตุ
                  </label>
                  <input value="-" type="text" name="note" class="form-control">
                </div>
              </div>
              <div class="d-flex justify-content-end align-items-center my-2">
                <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-modal" class="fs-4 fw-bold text-primary">0</span> บาท</p>
              </div>
              <table id="pdf" style="width: 100%;" class="table">
                <thead class="table-primary">
                  <tr class="text-center">
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

                <tbody id="table-body-modal" class="text-center">
                  <tr>
                    <th scope="row">1</th>
                    <td>
                      <select
                        style="width: 150px; margin: 0 auto;"
                        class="form-select device-select"
                        name="list[]">
                        <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                        <!-- Populate options dynamically -->
                        <?php
                        $sql = "SELECT * FROM device_models ORDER BY models_name ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result as $d) {
                        ?>
                          <option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
                        <?php
                        }
                        ?>
                      </select>
                    </td>
                    <td>
                      <textarea rows="2" maxlength="60" class="form-control" name="quality[]"></textarea>
                    </td>
                    <td>
                      <input name="amount[]" value="" style="width: 3rem; margin: 0 auto;" type="text" class="form-control">
                    </td>
                    <td>
                      <input name="price[]" value="" style="width: 5rem; margin: 0 auto;" type="text" class="form-control">
                    </td>
                    <td><input disabled value="" style="width: 5rem;" type="text" class="form-control no-toggle"></td>
                    <td>
                      <input name="unit[]" value="" style="width: 4rem; margin: 0 auto;" type="text" class="form-control">
                    </td>
                    <td><button type="button" class="btn btn-warning remove-row" style="visibility: hidden;">ลบ</button></td>
                  </tr>
                </tbody>
              </table>
              <div class="d-flex justify-content-end">
                <button type="button" id="add-row-modal" class="btn btn-success">+ เพิ่มแถว</button>
              </div>

              <input type="hidden" name="submitWithdraw" value="1">

              <div class="w-100 d-flex justify-content-center">
                <button data-id="submit-modal" type="submit" name="submitWithdraw" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
              </div>
            </div>

            <!-- -------------------------------overlayModal------------------------------------- -->
            <div id="overlayModalTask-modal" class="modal modal-container" style="display: none;">
              <div class="p-5 d-flex justify-content-center gap-4">
                <div class="modal-content overlay-modal">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">พบรายการที่เคยเบิกไปแล้ว</h1>
                    <button type="button" class="btn-close"
                      onclick="toggleModal('#overlayModalTask-modal')">
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
                        <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-sub" class="fs-4 fw-bold text-primary">0</span> บาท</p>
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

                        <tbody id="table-body-sub">
                        </tbody>
                      </table>
                      <div class="col-sm-6">
                        <button type="button" class="w-100 btn btn-secondary" onclick="toggleModal('#overlayModalTask-modal')">ย้อนกลับ</button>
                      </div>
                      <div class="col-sm-6">
                        <button type="submit" class="w-100 btn btn-success">ยืนยันที่จะเบิก</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- modal - copied mode -->
  <div id="copiedRequisitionModal" class="modal fade" tabindex="-1" aria-labelledby="copiedRequisitionModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="staticBackdropLabel">สร้างใบเบิก</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-3">
          <form id="formCopiedRequisitionModal" action="system/insert.php" method="post">
            <div class="row">
              <?php
              // Check if records exist in `orderdata_new` for the current `id`
              $checkQuery = "SELECT * FROM orderdata_new WHERE numberWork = :numberWork";
              $checkStmt = $conn->prepare($checkQuery);
              $checkStmt->bindParam(":numberWork", $numberWork);
              $checkStmt->execute();
              $requisitionData = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
              ?>
              <?php foreach ($requisitionData as $rowData) { ?>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label id="basic-addon1">วันที่ออกใบเบิก</label>
                    <input required type="date" name="dateWithdraw" value="<?= $rowData['dateWithdraw'] ?>" class="form-control thaiDateInput">
                  </div>
                </div>

                <div class="col-sm-6">
                  <label>ผูกหมายเลขงาน(ถ้ามี)</label>
                  <input type="text" class="form-control" name="id_ref"
                    value="">
                </div>

                <div class="col-sm-6">
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

                <div class="col-sm-6">
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
                    <label id="basic-addon1">หมายเลขพัสดุ / ครุภัณฑ์</label>
                    <div id="device-number-container-copied">
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
                            <input type="text" name="device_numbers[]" class="form-control mb-2" value="<?= htmlspecialchars($device['numberDevice']) ?>">
                            <button type="button" class="btn btn-warning p-2 mb-2 ms-3 remove-field"
                              style="visibility: <?= $isFirst ? 'hidden' : 'visible' ?>;">ลบ</button>
                          </div>
                        <?php
                          $isFirst = false;
                        }
                      } else { ?>
                        <div class="d-flex device-number-row">
                          <input type="text" name="device_numbers[]" class="form-control mb-2" value="">
                          <button type="button" class="btn btn-danger p-2 ms-3 remove-field" style="visibility: hidden;">ลบ</button>
                        </div>
                      <?php } ?>
                    </div>
                    <div class="d-flex justify-content-end">
                      <button type="button" id="add-device-number-copied" class="btn btn-success mt-2 align-self-end">+ เพิ่มหมายเลขครุภัณฑ์</button>
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
                    <input type="text" class="form-control" id="departInput1" name="ref_depart" value="<?= $departRow['depart_name'] ?>">
                    <input type="hidden" name="depart_id" id="departId1" value="<?= $rowData['refDepart'] ?>">
                  </div>

                  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.29/dist/sweetalert2.min.css">

                  <!-- Add SweetAlert2 JS -->
                  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.29/dist/sweetalert2.min.js"></script>

                  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
                  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
                  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

                  <script>
                    $(function() {
                      function setupAutocomplete(type, inputId, hiddenInputId, url, addDataUrl, confirmMessage) {
                        let inputChanged = false;
                        let alertShown = false; // Flag to track if the alert has been shown already

                        $(inputId).autocomplete({
                            source: function(request, response) {
                              $.ajax({
                                url: url,
                                dataType: "json",
                                data: {
                                  term: request.term,
                                  type: type
                                },
                                success: function(data) {
                                  response(data); // Show suggestions
                                }
                              });
                            },
                            minLength: 1,
                            autoFocus: true,
                            select: function(event, ui) {
                              $(inputId).val(ui.item.label); // Fill input with label
                              $(hiddenInputId).val(ui.item.value); // Fill hidden input with ID
                              return false; // Prevent default behavior
                            }
                          })
                          .data("ui-autocomplete")._renderItem = function(ul, item) {
                            return $("<li>")
                              .append("<div>" + item.label + "</div>")
                              .appendTo(ul);
                          };

                        $(inputId).on("autocompletefocus", function(event, ui) {
                          console.log("Item highlighted: ", ui.item.label);
                          return false;
                        });

                        $(inputId).on("keyup", function() {
                          inputChanged = true;
                        });

                        $(inputId).on("blur", function() {
                          if (inputChanged && !alertShown) {
                            const userInput = $(this).val().trim();
                            const hiddenValue = $(hiddenInputId).val();
                            if (userInput === "") return;
                            if (hiddenValue !== "") {
                              inputChanged = false;
                              return;
                            }
                            let found = false;
                            $(this).autocomplete("instance").menu.element.find("div").each(function() {
                              if ($(this).text() === userInput) {
                                found = true;
                                return false;
                              }
                            });

                            if (!found) {
                              alertShown = true; // Prevent the alert from firing again
                              // Show SweetAlert to confirm insert data
                              Swal.fire({
                                title: confirmMessage,
                                icon: "info",
                                showCancelButton: true,
                                confirmButtonText: "ใช่",
                                cancelButtonText: "ไม่"
                              }).then((result) => {
                                if (result.isConfirmed) {
                                  $.ajax({
                                    url: addDataUrl,
                                    method: "POST",
                                    data: {
                                      dataToInsert: userInput
                                    },
                                    success: function(response) {
                                      console.log("Data inserted successfully!");
                                      $(hiddenInputId).val(response); // Set inserted ID
                                    },
                                    error: function(xhr, status, error) {
                                      console.error("Error inserting data:", error);
                                    }
                                  });
                                } else {
                                  $(inputId).val(""); // Clear input if canceled
                                  $(hiddenInputId).val("");
                                }
                                alertShown = false; // Reset the flag after the action
                              });
                            }
                          }
                          inputChanged = false; // Reset the flag
                        });
                      }

                      setupAutocomplete(
                        "depart",
                        "#departInput1",
                        "#departId1",
                        "autocomplete.php",
                        "insertDepart.php",
                        "คุณต้องการเพิ่มข้อมูลนี้หรือไม่?"
                      );
                    });
                  </script>
                </div>

                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="inputGroupSelect01">ผู้รับเรื่อง</label>
                    <input type="text" class="form-control" value="<?= $fullname ?>" disabled>
                    <input type="hidden" name="ref_username" value="<?= $fullname ?>">
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
                    <label for="inputGroupSelect01">สถานะ
                    </label>
                    <select required class="form-select" name="status" id="inputGroupSelect01">
                      <option value="1">รอรับเอกสารจากหน่วยงาน</option>
                      <option value="2">รอส่งเอกสารไปพัสดุ</option>
                      <option value="3">รอพัสดุสั่งของ</option>
                      <option value="4">รอหมายเลขครุภัณฑ์</option>
                      <option value="5">ปิดงาน</option>
                      <option value="6">ยกเลิก</option>
                    </select>
                  </div>
                </div>

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
                  <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-copied" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                </div>
                <table id="pdf" style="width: 100%;" class="table">
                  <thead class="table-primary">
                    <tr class="text-center">
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
                  <tbody id="table-body-copied">
                    <?php
                    $rowNumber = 1;
                    $isFirstRow = true;
                    foreach ($orderItems as $item) { //สร้าง case ถ้า orderItems is null
                    ?>
                      <tr class="text-center">
                        <th scope="row"><?= $rowNumber++; ?></th>
                        <td>
                          <select style="width: 150px; margin: 0 auto;" class="form-select device-select" name="list[]" data-row="1">
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
                        <td><textarea rows="2" maxlength="60" name="quality[]" class="form-control"><?= htmlspecialchars($item['quality']); ?></textarea></td>
                        <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[]" class="form-control" value="<?= htmlspecialchars($item['amount']); ?>"></td>
                        <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[]" class="form-control" value="<?= htmlspecialchars($item['price']); ?>"></td>
                        <td><input disabled value="<?= htmlspecialchars($item['amount'] * $item['price']) ?>" style="width: 5rem;" type="text" class="form-control no-toggle"></td>
                        <td><input style="width: 4rem; margin: 0 auto;" type="text" name="unit[]" class="form-control" value="<?= htmlspecialchars($item['unit']); ?>"></td>
                        <td><button type="button" class="btn btn-warning remove-row" style="visibility: <?= $isFirstRow ? 'hidden' : 'visible' ?>;">ลบ</button></td>
                      </tr>
                    <?php
                      $isFirstRow = false;
                    } ?>
                  </tbody>
                </table>
                <div class="d-flex justify-content-end">
                  <button type="button" id="add-row-copied" class="btn btn-success">+ เพิ่มแถว</button>
                </div>

                <input type="hidden" name="submitWithdraw" value="1">

                <div class="w-100 d-flex justify-content-center">
                  <button data-id="submit-copied" type="submit" name="submitWithdraw" class="w-100 btn btn-primary mt-3">บันทึกข้อมูล</button>
                </div>
              <?php } ?>
            </div>
            <!-- -------------------------------overlayModal------------------------------------- -->
            <div id="overlayModalTask-copied" class="modal modal-container" style="display: none;">
              <div class="p-5 d-flex justify-content-center gap-4">
                <div class="modal-content overlay-modal">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">พบรายการที่เคยเบิกไปแล้ว</h1>
                    <button type="button" class="btn-close"
                      onclick="toggleModal('#overlayModalTask-copied')">
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
                        <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount-sub" class="fs-4 fw-bold text-primary">0</span> บาท</p>
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

                        <tbody id="table-body-sub">
                        </tbody>
                      </table>
                      <div class="col-sm-6">
                        <button type="button" class="w-100 btn btn-secondary" onclick="toggleModal('#overlayModalTask-copied')">ย้อนกลับ</button>
                      </div>
                      <div class="col-sm-6">
                        <button type="submit" class="w-100 btn btn-success">ยืนยันที่จะเบิก</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>



  <script>
    //ฟังก์ชั่น sumTotal ที่ทำงานเฉพาะ main
    // function calculateSumTotal() {
    //   let total = 0;
    //   const sumInputs = document.querySelectorAll('input.no-toggle'); //กำหนดได้ว่าเป็น sumTotal ของ field ไหน
    //   sumInputs.forEach(input => {
    //     total += parseFloat(input.value) || 0; // Make sure to handle NaN if value is empty
    //   });
    //   // Update the total display
    //   document.getElementById('total-amount').textContent = total.toLocaleString(); //กำหนดได้ว่าเป็น sumTotal ของ field ไหน
    // }

    function calculateSumTotal(tableBodyId) {
      let total = 0;
      const sumInputs = document.querySelectorAll(`#${tableBodyId} input.no-toggle`);
      sumInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
      });

      // Dynamically get the specific total-amount element
      const totalAmount = document.querySelector(`#total-amount-${tableBodyId.split('-').pop()}`);
      if (totalAmount) {
        totalAmount.textContent = total.toLocaleString();
      }

      console.log(`Total for ${tableBodyId}: `, total);
    }

    //alear บันทึก
    document.getElementById('saveData')?.addEventListener('click', function(event) {
      const isConfirmed = confirm('คุณต้องการบันทึกใช่หรือไม่');
      if (!isConfirmed) {
        event.preventDefault(); // Prevents the form from submitting
      }
    });

    document.addEventListener("DOMContentLoaded", function() {
      const editButton = document.getElementById("editData");
      const saveButton = document.getElementById("saveData");
      const manageColumnHeader = document.querySelector("th[scope='col'][style='display: none;']");

      //v device number field
      const addDeviceButton = document.getElementById("add-device-number-main");
      const removeFields = document.querySelectorAll(".remove-field");

      const tableRows = document.querySelectorAll('[id^="table-body-"] tr');
      tableRows.forEach((row) => {
        const tableBodyId = row.closest('tbody').id;
        calculateRowTotal(row, tableBodyId);
      });

      editButton?.addEventListener("click", function(event) { //*
        event.preventDefault();
        toggleEditMode();
      });

      calculateSumTotal('table-body-main')
      calculateSumTotal('table-body-modal')
      calculateSumTotal('table-body-copied')

      function toggleEditMode() { //toggle device field in case standard blank and edit mode

        editButton.style.display = "none";
        saveButton.style.display = "inline-block";

        //v device number field
        const deviceInputs = document.querySelectorAll("#device-number-container-main input");
        const isDisabled = document.querySelector("#device-number-container-main input").disabled;
        deviceInputs.forEach(function(input) {
          input.disabled = !input.disabled; //ทำให้ device field can toggle
        });
        removeFields.forEach(function(button) {
          button.style.display = isDisabled ? "inline-block" : "none"; //ปุ่ม ลบ device
        });
        addDeviceButton.style.display = isDisabled ? "inline-block" : "none"; //ปุ่ม เพิ่ม device
        //^ device number field

        manageColumnHeader.style.display = isDisabled ? "table-cell" : "none"; //ปุ่มเพิ่ม column list table //x เอาออกจาก function นี้

        //field อื่นๆ
        ["report", "reason", "note", "refWithdraw", "refWork", "refDevice", "refOffer", "quotation", "id_ref"].forEach(function(name) {
          const input = document.querySelector(`[name='${name}']`);
          if (input) {
            input.disabled = !input.disabled;
          }
        });

        //table
        const tableInputs = document.querySelectorAll(
          "#table-body-main input, #table-body-main textarea, #table-body-main select"
        );

        tableInputs.forEach(function(input) {
          if (!input.classList.contains("no-toggle")) {
            input.disabled = !input.disabled;
          }
        });

        const removeButtons = document.querySelectorAll(".remove-row");
        const addRowButton = document.getElementById("add-row-main");

        removeButtons.forEach(function(button) {
          button.style.display = isDisabled ? "inline-block" : "none";
        });
        addRowButton.style.display = isDisabled ? "inline-block" : "none";
      }

      document.addEventListener('click', function(e) { //ตรวจจับ event คลิ๊ก 
        const deviceOptions = `<?= $deviceOptions ?>`; //สำหรับ <select>

        if (e.target && e.target.id.startsWith('add-device-number-')) { //add-device-number
          const modalId = e.target.id.split('-').pop();
          const container = document.querySelector(`#device-number-container-${modalId}`); //แยกว่าเป็น field ของอะไร
          const newRow = document.createElement('div');
          newRow.className = 'd-flex device-number-row';
          newRow.innerHTML = `
<input type="text" name="device_numbers[]" class="form-control mb-2" value="">
<button type="button" class="btn btn-warning p-2 ms-3 remove-field mb-2">ลบ</button>
        `;
          container.appendChild(newRow);
        }

        if (e.target && e.target.classList.contains('remove-field')) { //remove-field
          const row = e.target.closest('.device-number-row');
          const hiddenInput = row.querySelector('input[type="text"]');

          if (hiddenInput && hiddenInput.name.startsWith('update_number_device')) { //check ว่าเป็น field update มั้ย
            // Case 1: Soft delete
            const modalId = e.target.getAttribute('data-row-id');
            const deviceId = e.target.getAttribute('data-device-id');
            const container = document.querySelector(`#device-number-container-main`);
            const deletedInput = document.createElement('input');
            deletedInput.type = 'hidden';
            deletedInput.name = `deleted_devices[${modalId}][${deviceId}]`;
            deletedInput.value = hiddenInput.value;
            container.appendChild(deletedInput);
          }

          // Remove row for both cases, remove row ตามปกติ
          row.remove();
        }

        if (e.target && e.target.id.startsWith('add-row-')) { //เพิ่มแถว
          const modalId = e.target.id.split('-').pop();
          const tableBody = document.querySelector(`#table-body-${modalId}`);
          const rowIndex = tableBody.querySelectorAll("tr").length + 1;
          const newRow = document.createElement("tr");
          newRow.innerHTML = `
            <td>${rowIndex}</td>
            <td>
          <select style="width: 150px; margin: 0 auto;" class="form-select device-select" name="list[]">
            <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
            ${deviceOptions}
          </select>
    </td>
            <td><textarea class="form-control" name="quality[]"></textarea></td>
            <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
            <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[]" class="form-control"></td>
            <td><input disabled style="width: 5rem;" type="text" class="form-control no-toggle"></td>
            <td><input style="width: 4rem; margin: 0 auto;" type="text" name="unit[]" class="form-control"></td>
            <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
        `;
          tableBody.appendChild(newRow);

          calculateRowTotal(newRow, `table-body-${modalId}`);
          bindAutoList();
        }

        if (event.target && event.target.classList.contains("remove-row")) { //ลบแถว
          var row = event.target.closest("tr");
          const tableBody = row.closest("tbody");
          const tableBodyId = tableBody.id;

          var hiddenInput = row.querySelector('select');
          if (hiddenInput && hiddenInput.name.startsWith('update_list')) {
            var rowId = event.target.getAttribute('data-items-row-id');
            var itemId = event.target.getAttribute('data-items-id');
            var mainTableBody = document.querySelector(`#table-body-main`);
            if (mainTableBody) {
              var deletedInput = document.createElement('input');
              deletedInput.type = 'hidden';
              deletedInput.name = `deleted_items[${rowId}][${itemId}]`;
              deletedInput.value = itemId;
              mainTableBody.appendChild(deletedInput);
            }
          }

          row.remove();
          calculateSumTotal(tableBodyId)
        }
      });
    });

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

    function bindAutoList() {
      const deviceSelects = document.querySelectorAll(".device-select");
      deviceSelects.forEach(function(select) {
        select.removeEventListener("change", handleDeviceChange); // Prevent duplicate binding
        select.addEventListener("change", handleDeviceChange);
      });
    }

    // Handle the onchange event for device-select
    function handleDeviceChange(event) {
      const models_id = event.target.value;
      const rowElement = event.target.closest("tr");
      const mode_field = $(event.target).closest('tbody').attr('id').split('-').pop();
      const nameAttr = $(event.target).attr('name');
      const matches = nameAttr.match(/\[(\d+)\]\[(\d+)\]/);
      let modalId = null;
      let itemId = null;
      if (matches != null) {
        modalId = matches[1];
        itemId = matches[2];
      }
      if (models_id) {
        $.ajax({
          url: "autoList.php",
          type: "POST",
          data: {
            models_id: models_id
          },
          success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
              if (modalId != null) {
                rowElement.querySelector(`textarea[name="update_quality[${modalId}][${itemId}]"]`).value = data.quality;
                rowElement.querySelector(`input[name="update_price[${modalId}][${itemId}]"]`).value = data.price;
                rowElement.querySelector(`input[name="update_unit[${modalId}][${itemId}]"]`).value = data.unit;
              } else {
                rowElement.querySelector('textarea[name*="quality[]"]').value = data.quality;
                rowElement.querySelector('input[name*="price[]"]').value = data.price;
                rowElement.querySelector('input[name*="unit[]"]').value = data.unit;
              }
              calculateRowTotalAutoList(rowElement, `table-body-${mode_field}`);
            } else {
              alert("ไม่สามารถดึงข้อมูลได้");
            }
          },
          error: function() {
            alert("เกิดข้อผิดพลาดในการเชื่อมต่อ");
          },
        });
      }
    }
    bindAutoList();
  </script>

  <script>
    document.addEventListener('input', function(event) {
      if (event.target && event.target.classList.contains('limitedTextarea')) {
        const lines = event.target.value.split('\n');
        const maxRows = 2; // จำนวนแถวสูงสุดที่ต้องการ
        if (lines.length > maxRows) {
          event.target.value = lines.slice(0, maxRows).join('\n');
        }
      }
    });
  </script>



  <script>
    // ฟังก์ชันสำหรับแปลงปีคริสต์ศักราชเป็นปีพุทธศักราช
    function convertToBuddhistYear(englishYear) {
      return englishYear;
    }

    // ชื่อเดือนภาษาไทย
    const thaiMonthNames = [
      'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
      'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];

    function toggleDateInput(inputName) {
      const checkbox = document.querySelector(`[name="show_${inputName}"]`);
      const input = document.querySelector(`[name="${inputName}"]`);
      const thaiDateElement = input.parentElement.querySelector('.thaiDATE');

      if (checkbox.checked) {
        // ดึงอินพุทธศักราชปัจจุบัน
        const currentGregorianYear = new Date().getFullYear();
        const currentBuddhistYear = convertToBuddhistYear(currentGregorianYear);

        // แปลงปีปัจจุบันเป็นปีพุทธศักราชแล้วกำหนดค่าให้กับ input
        const currentDate = new Date();
        const thaiDate = currentBuddhistYear + '-' +
          ('0' + (currentDate.getMonth() + 1)).slice(-2) + '-' +
          ('0' + currentDate.getDate()).slice(-2);
        input.value = thaiDate; // เพิ่มบรรทัดนี้เพื่อกำหนดค่าลงใน input
        // แปลงชื่อเดือนเป็นภาษาไทย
        const thaiMonth = thaiMonthNames[currentDate.getMonth()];

        // แสดงค่าวันที่และปีพุทธศักราชใน element p
        const formattedDate = currentDate.toLocaleDateString('th-TH', {
          day: 'numeric',
          month: 'long',
          year: 'numeric'
        });
        thaiDateElement.textContent = formattedDate;
      } else {
        input.value = '';
        thaiDateElement.textContent = '';
      }
    }
  </script>

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script>
    $(function() {
      $("#assetInput").autocomplete({
        source: function(request, response) {
          $.ajax({
            url: "autoNumberDevice.php",
            dataType: "json",
            data: {
              term: request.term
            },
            success: function(data) {
              response(data);
            }
          });
        },
        minLength: 1,
        select: function(event, ui) {
          $("#assetInput").val(ui.item.label);
          $("#assetInputName").val(ui.item.value);

          // Redirect to another page
          var url = "?numberWork=" + ui.item.value; // แก้ไข URL ตามที่คุณต้องการ
          window.location.href = url;

          return false;
        },
      }).data("ui-autocomplete")._renderItem = function(ul, item) {
        return $("<li>")
          .append("<div>" + item.label + "</div>")
          .appendTo(ul);
      };

      // Trigger select event when an item is highlighted
      $("#assetInput").on("autocompletefocus", function(event, ui) {
        $("#assetInput").val(ui.item.label);
        $("#assetInputName").val(ui.item.value);
        return false;
      });

      // Check if the entered value is not in autocomplete
    });
  </script>

  <script>
    document.querySelectorAll('.confirm-btn, .cancel-btn').forEach(button => {
      button.addEventListener('click', function() {
        const status = this.dataset.status;
        const orderId = this.dataset.orderId;
        const statusName = this.closest('tr').querySelector('td').textContent.trim();

        Swal.fire({
          title: 'คุณแน่ใจหรือไม่?',
          text: `คุณต้องการอัพเดตสถานะ "${statusName}" ใช่หรือไม่`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'ใช่',
          cancelButtonText: 'ไม่'
        }).then((result) => {
          if (result.isConfirmed) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                  status,
                  order_id: orderId
                })
              })
              .then(response => response.text())
              .then(data => {
                Swal.fire('สำเร็จ', data, 'success').then(() => {
                  location.reload();
                });
              });
          }
        });
      });
    });

    document.querySelectorAll('.undo-btn').forEach(button => {
      button.addEventListener('click', function() {
        const id = this.dataset.id;

        Swal.fire({
          title: 'ยืนยันการย้อนสถานะ',
          text: 'คุณต้องการย้อนสถานะ ใช่หรือไม่',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก'
        }).then(result => {
          if (result.isConfirmed) {
            fetch('update_status.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                  id
                })
              })
              .then(response => response.text())
              .then(data => {
                Swal.fire('สำเร็จ', data, 'success').then(() => location.reload());
              });
          }
        });
      });
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
  <script>
    function toggleModal(modalId) {
      const modal = document.querySelector(modalId);
      if (modal) {
        modal.style.display = modal.style.display === "none" || modal.style.display === "" ? "block" : "none";
      } else {
        console.error("Modal not found:", modalId);
      }
    }

    document.addEventListener('click', async function(event) {
      if (event.target.matches('[name="submitWithdraw"]')) {
        event.preventDefault();

        // Get the form and device number inputs
        const button = event.target;
        const form = button.closest('form');
        console.log(form)
        const deviceNumberInputs = form.querySelectorAll('input[name^="device_numbers"]');

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
          const response = await fetch('check_duplicate.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `number_device=${encodeURIComponent(JSON.stringify(deviceNumbers))}`
          });

          const result = await response.json();
          console.log(result)
          if (result.found) {
            duplicateFound = true;

            const getModalId = event.target.getAttribute('data-id');
            const status = getModalId.split('-')[1];
            // Show modal and populate fields dynamically
            const modal = document.querySelector(`#overlayModalTask-${status}`);
            modal.style.display = 'block';
            console.log('modal', modal)
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

            console.log('Modal:', modal);
            console.log('Order Radio Group:', orderRadioGroup);


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

            console.log('ordersByNumberWork', ordersByNumberWork);

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

              console.log('radioButton', radioButton);
              console.log('label', label);

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

        if (!duplicateFound) {
          // If no duplicate is found, you can submit the form if needed
          form.submit();
        }
      }
    });

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
      modal.querySelector('#total-amount-sub').textContent = totalAmount;
    }
  </script>
  <?php SC5() ?>
</body>

</html>