<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

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
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php bs5() ?>
  <title>ตรวจสอบ | IT ORDER PRO</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

  <?php navbar();
  ?>

  <?php if (!isset($_GET['numberWork'])) { ?>
    <div class="container mt-4">
      <div class="container mt-4">
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
        <h1 class="text-center my-5">ตรวจสอบใบเบิก</h1>
        <form action="" method="GET">
          <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-6">
              <div class="row">
                <div class="card col-sm-12">
                  <div class="d-flex flex-row justify-content-between p-3 mb-2">
                    <div>
                      <label class="form-label" for="assetInput">ค้นหาหมายเลขครุภัณฑ์</label>
                      <input class="form-control" type="text" id="assetInput">
                      <input type="hidden" id="assetInputName" name="">
                    </div>

                    <div>
                      <label class="form-label" for="inputGroupSelect01">หมายเลขออกงาน</label>
                      <?php
                      $sql = "SELECT * FROM orderdata_new ORDER BY id DESC";
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
                          <option value="<?= $row['numberWork'] ?>">
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
                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary p-2">ดูข้อมูล</button>
                  </div>
        </form>
      </div>
      <form action="export.php" method="post">
        <div class="d-flex justify-content-end">
          <button type="submit" name="DataAll" class="btn btn-primary my-3 p-3">Export to Excel</button>
        </div>
      </form>
      <?php
      $sql = "SELECT status, COUNT(*) as count FROM orderdata GROUP BY status";
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

        $textS = isset($statusOptions[$status]['text']) ? $statusOptions[$status]['text'] : "ไม่ระบุสถานะ";
        $color = isset($statusOptions[$status]['color']) ? $statusOptions[$status]['color'] : sprintf('#%06X', rand(0, 0xFFFFFF));

      ?>
        <div class="col-sm-6">
          <div class="card text-white me-3 mb-3" style="max-width: 18rem; background-color: <?= $color ?>">
            <div class="card-body">
              <h5 class="card-title"><?= $count ?></h5>
              <?= $textS ?>
            </div>
            <div class="card-footer">
              <p class="card-text text-end">
                <a href="checkStatus.php?status=<?= $status ?>" class="text-white" style="text-decoration: none;"> รายละเอียดเพิ่มเติม</a>
              </p>
            </div>
          </div>
        </div>
      <?php
      }
      ?>
    </div>
    </div>
    <div class="card col-sm-12 col-lg-6 col-md-12  mb-5">
      <?php
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
    dm.models_name AS list, 
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
  LEFT JOIN 
    device_models AS dm ON oi.list = dm.models_id
  WHERE 
    (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
    AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
  AND od.id = (SELECT MAX(id) FROM orderdata_new) -- Fetch the latest record
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
            $devices[$row['numberDevice_id']] = $row['numberDevice'];
          }

          // Collect unique items
          if (!empty($row['item_id']) && !isset($items[$row['item_id']])) {
            $items[$row['item_id']] = [
              'list' => $row['list'],
              'quality' => $row['quality'],
              'amount' => $row['amount'],
              'price' => $row['price'],
              'unit' => $row['unit']
            ];
          }
        }

        // Reset keys for JSON encoding or numeric indexing
        $devices = array_values($devices);
        $items = array_values($items);
      } else {
        echo "No records found.";
        $order = [];
        $devices = [];
        $items = [];
      }
      ?>
      <?php
      $columns = [];

      for ($i = 1; $i <= 15; $i++) {
        $columns[] = "`list$i`, `quality$i`, `amount$i`, `price$i` , `unit$i`";
      }
      $columnString = implode(", ", $columns);

      $sql = "SELECT $columnString FROM `orderdata`";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!$result) { ?>
        <div class="alert alert-danger" role="alert">
          ไม่พบข้อมูล
        </div>
      <?php    } else {

        $sum = 0;
        for ($i = 1; $i <= 15; $i++) {
          $list = $result["list$i"];
          $quality = $result["quality$i"];
          $amount = $result["amount$i"];
          $price = $result["price$i"];
          $amount = intval($amount);
          $price = intval($price);
          // คำนวณ $sum
          $currentSum = $amount * $price;

          $sum += intval($currentSum);
          // ตรวจสอบว่า $currentSum เป็น 0 หรือไม่
          if ($currentSum == 0) {
            $currentSum = ""; // กำหนดให้ $currentSum เป็นค่าว่าง
          }
          if ($result["list$i"] == "" || $result["quality$i"] == "" || $result["amount$i"] == "" || $result["price$i"] == "" && $result["unit$i"] == "") {
            $list = "";
            $quality = "";
            $amount = "";
            $price = "";
            $unit = "";
          }
        }

        $sql = "SELECT od.*, dp.depart_name, lw.work_name, dv.device_name, ad.fname, ad.lname";

        for ($i = 1; $i <= 15; $i++) {
          $sql .= ", dm{$i}.models_name AS model{$i}";
        }

        $sql .= " FROM orderdata AS od
    INNER JOIN depart AS dp ON od.refDepart = dp.depart_id
    INNER JOIN listwork AS lw ON od.refWork = lw.work_id
    INNER JOIN device AS dv ON od.refDevice = dv.device_id
    INNER JOIN admin AS ad ON od.refUsername = ad.username";

        for ($i = 1; $i <= 15; $i++) {
          $sql .= " LEFT JOIN device_models AS dm{$i} ON od.list{$i} = dm{$i}.models_id";
        }
        $sql .= " ORDER BY od.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $Device2 = "";
        $Device3 = "";

        if ($data['numberDevice2'] == "") {
          $Device2 = "";
        } else {
          $Device2 = ', ' . $data["numberDevice2"];
        }
        if ($data['numberDevice3'] == "") {
          $Device3 = "";
        } else {
          $Device3 = ', ' . $data["numberDevice3"];
        }
        ////////^^^^ จัดการเลขครุภัณฑ์กับรายการ
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

        $dateWithdrawFromDB = $data['dateWithdraw'];
        $receiptDateFromDB = $data['receiptDate'];
        $deliveryDateFromDB = $data['deliveryDate'];
        $closeDateFromDB = $data['closeDate'];

        // แปลงวันที่ในรูปแบบ Y-m-d เป็นรูปแบบไทย
        $dateWithdrawThai = formatDateThai($dateWithdrawFromDB);
        $receiptThai = formatDateThai($receiptDateFromDB);
        $deliveryThai = formatDateThai($deliveryDateFromDB);
        $closeThai = formatDateThai($closeDateFromDB); ?>


        <div class="row">
          <div class="col-6">
            <label>วันที่ออกใบเบิก</label>
            <input type="date" class="form-control"
              value="<?= $order['dateWithdraw'] ?? '' ?>" disabled>
          </div>

          <div class="col-6">
            <label>ผู้รับเรื่อง</label>
            <input type="text" class="form-control"
              value="<?= $order['refUsername'] ?? '' ?>" disabled>
          </div>
        </div>

        <div class="row">
          <div class="col-6">
            <label>ส่งซ่อมอุปกรณ์ คอมพิวเตอร์</label>
            <input type="text" class="form-control"
              value="<?= $order['device_name'] ?? '' ?>" disabled>
          </div>
          <div class="col-6">
            <label>หมายเลขพัสดุ / ครุภัณฑ์</label>
            <input type="text" class="form-control"
              value="<?= htmlspecialchars(implode(', ', $devices)) ?>" disabled>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <label>อาการที่รับแจ้ง</label>
            <input type="text" class="form-control"
              value="<?= $order['report'] ?>" disabled>
          </div>
        </div>

        <div class="row">
          <div class="col-6">
            <label>รายละเอียด</label>
            <input type="text" class="form-control"
              value="<?= $order['reason'] ?>" disabled>
          </div>
          <div class="col-6">
            <label>หน่วยงานที่แจ้ง</label>
            <input type="text" class="form-control"
              value="<?= $order['depart_name'] ?>" disabled>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <label>หมายเหตุ</label>
            <input type="text" class="form-control"
              value="<?= $order['note'] ?>" disabled>
          </div>
        </div>
        <?php
        $order_id = $order['id'];
        $sql = "
    SELECT status, timestamp 
    FROM order_status 
    WHERE order_id = :order_id 
    ORDER BY status";
        $stmt = $conn->prepare(query: $sql);
        $stmt->execute(['order_id' => $order_id]);
        $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if the order is canceled (status = 6 exists)
        $isCanceled = in_array(6, array_column($statuses, 'status'));

        // Define status names
        $statusNames = [
          1 => "รอรับเอกสารจากหน่วยงาน",
          2 => "รอส่งเอกสารไปพัสดุ",
          3 => "รอพัสดุสั่งของ",
          4 => "รอหมายเลขครุภัณฑ์",
          5 => "ปิดงาน",
          6 => "ยกเลิก"
        ];

        // Current status from records
        $currentStatus = !empty($statuses) ? max(array_column($statuses, 'status')) : 0;
        ?>
        <h4 class="mt-3">สถานะ</h4>
        <table id="pdf" style="width: 100%;" class="table">
          <thead class="table-warning">
            <tr class="text-center">
              <th scope="col">สถานะ</th>
              <th scope="col">วันที่อัพเดตสถานะ</th>
              <th scope="col">ปุ่มยืนยัน</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php
            foreach ($statusNames as $key => $name) {
              $record = array_filter($statuses, fn($row) => $row['status'] == $key);
              $timestamp = $record ? reset($record)['timestamp'] : null;

              echo "<tr>";
              echo "<td>{$name}</td>";
              echo "<td>" . ($timestamp
                ? date('d/m/Y', strtotime($timestamp))
                : (($key == $currentStatus + 1 || $key == 6) && !$isCanceled ? date('d/m/Y') : '-')) . "</td>";
              echo "<td>";

              if ($isCanceled) {
                if ($key == 6) {
                  echo "<p class='text-danger'>ยกเลิกใบเบิกแล้ว</p>";
                } else {
                  if ($timestamp) {
                    echo "<p>ยืนยันแล้ว</p>";
                  } else {
                    echo "<p >-</p>";
                  }
                }
              } else {
                if ($timestamp) {
                  echo "<p>ยืนยันแล้ว</p>";
                } elseif ($key == $currentStatus + 1 && $key <= 5) {
                  echo "<button type='button' class='btn mb-3 btn-success confirm-btn' data-status='{$key}' data-order-id='{$order_id}'>รอการยืนยัน</button>";
                } elseif ($key > $currentStatus + 1 && $key <= 5) {
                  echo "<button type='button' class='btn mb-3 btn-secondary' disabled>รอดำเนินการก่อนหน้า</button>";
                }
              }

              if ($key == 6 && !$isCanceled) {
                echo "<button type='button' class='btn mb-3 btn-danger cancel-btn' data-status='6' data-order-id='{$order_id}'>ยกเลิกใบเบิก</button>";
              }

              echo "</td>";
              echo "</tr>";
            }
            ?>
          </tbody>
        </table>
        <h4>รายการเบิก</h4>
        <table id="pdf" style="width: 100%;" class="table">
          <thead class="text-center table-primary">
            <th scope="col">ลำดับ</th>
            <th scope="col">รายการ</th>
            <th scope="col">คุณสมบัติ</th>
            <th scope="col">จำนวน</th>
            <th scope="col">ราคา</th>
            <th scope="col">หน่วย</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php foreach ($items as $index => $item): ?>
              <tr>
                <td><?= $index + 1 ?></td>
                <td>
                  <input disabled value="<?= htmlspecialchars($item['list']) ?>" style="width: 8rem;" type="text" class="form-control">
                </td>
                <td>
                  <textarea disabled class="form-control"><?= htmlspecialchars($item['quality']) ?></textarea>
                </td>
                <td>
                  <input disabled value="<?= htmlspecialchars($item['amount']) ?>" style="width: 3rem;" type="text" class="form-control">
                </td>
                <td>
                  <input disabled value="<?= htmlspecialchars($item['price']) ?>" style="width: 4rem;" type="text" class="form-control">
                </td>
                <td>
                  <input disabled value="<?= htmlspecialchars($item['unit']) ?>" style="width: 5rem;" type="text" class="form-control">
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <form action="system/update.php" method="POST">
        </form>
    </div>
    </div>
    </div>
    </div>

<?php    }
    } ?>


<?php
if (isset($_GET['numberWork'])) {
  $numberWork = $_GET['numberWork'];

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
dm.models_name AS list, 
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
LEFT JOIN 
device_models AS dm ON oi.list = dm.models_id
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
        $devices[$row['numberDevice_id']] = $row['numberDevice'];
      }

      // Collect unique items
      if (!empty($row['item_id']) && !isset($items[$row['item_id']])) {
        $items[$row['item_id']] = [
          'list' => $row['list'],
          'quality' => $row['quality'],
          'amount' => $row['amount'],
          'price' => $row['price'],
          'unit' => $row['unit']
        ];
      }
    }

    // Reset keys for JSON encoding or numeric indexing
    $devices = array_values($devices);
    $items = array_values($items);
  } else {
    echo "No records found.";
    $order = [];
    $devices = [];
    $items = [];
  }

  $columns = [];

  for ($i = 1; $i <= 15; $i++) {
    $columns[] = "`list$i`, `quality$i`, `amount$i`, `price$i` , `unit$i`";
  }
  $columnString = implode(", ", $columns);

  $sql = "SELECT $columnString FROM `orderdata` WHERE id = :numberWork";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":numberWork", $numberWork);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);
  $sum = 0;

  for ($i = 1; $i <= 15; $i++) {
    $list = $result["list$i"];
    $quality = $result["quality$i"];
    $amount = $result["amount$i"];
    $price = $result["price$i"];
    $amount = intval($amount);
    $price = intval($price);
    // คำนวณ $sum
    $currentSum = $amount * $price;

    $sum += intval($currentSum);
    // ตรวจสอบว่า $currentSum เป็น 0 หรือไม่
    if ($currentSum == "-" || $currentSum == 0) {
      $currentSum = ""; // กำหนดให้ $currentSum เป็นค่าว่าง
    }
    if ($result["list$i"] == "-" || $result["quality$i"] == "-" || $result["amount$i"] == "-" || $result["price$i"] == "-" || $result["unit$i"] == "-") {
      $list = "";
      $quality = "";
      $amount = "";
      $price = "";
      $unit = "";
    }
  }

  $sql = "SELECT od.*, dp.depart_name, lw.work_name, dv.device_name, ad.fname, ad.lname";

  for ($i = 1; $i <= 15; $i++) {
    $sql .= ", dm{$i}.models_name AS model{$i}";
  }

  $sql .= " FROM orderdata AS od
    INNER JOIN depart AS dp ON od.refDepart = dp.depart_id
    INNER JOIN listwork AS lw ON od.refWork = lw.work_id
    INNER JOIN device AS dv ON od.refDevice = dv.device_id
    INNER JOIN admin AS ad ON od.refUsername = ad.username";

  for ($i = 1; $i <= 15; $i++) {
    $sql .= " LEFT JOIN device_models AS dm{$i} ON od.list{$i} = dm{$i}.models_id";
  }
  $sql .= " WHERE od.id = :numberWork";
  $stmt = $conn->prepare($sql);
  $stmt->bindParam(":numberWork", $numberWork);
  $stmt->execute();
  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  $Device2 = "";
  $Device3 = "";

  if (isset($data['numberDevice2']) == "-" || isset($data['numberDevice2']) == "") {
    $Device2 = "";
  } else {
    $Device2 = ', ' . $data["numberDevice2"];
  }
  if (isset($data['numberDevice3']) == "-" || isset($data['numberDevice3']) == "") {
    $Device3 = "";
  } else {
    $Device3 = ', ' . $data["numberDevice3"];
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

  $dateWithdrawFromDB = $data['dateWithdraw'];
  $receiptDateFromDB = $data['receiptDate'];
  $deliveryDateFromDB = $data['deliveryDate'];
  $closeDateFromDB = $data['closeDate'];

  // แปลงวันที่ในรูปแบบ Y-m-d เป็นรูปแบบไทย
  $dateWithdrawThai = formatDateThai($dateWithdrawFromDB);
  $receiptThai = formatDateThai($receiptDateFromDB);
  $deliveryThai = formatDateThai($deliveryDateFromDB);
  $closeThai = formatDateThai($closeDateFromDB);
?>


  <div class="container mt-5">
    <div class="container mt-5">
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
        <div class="col-sm-12 col-md-12 col-lg-6">
          <div class="row">
            <div class="col-sm-12">
              <form action="" method="GET">
                <div class="mb-3">
                  <label class="form-label" for="assetInput">ค้นหาหมายเลขครุภัณฑ์</label>
                  <input class="form-control" type="text" id="assetInput">
                  <input type="hidden" id="assetInputName" name="">

                  <label class="form-label" for="inputGroupSelect01">หมายเลขออกงาน</label>
                  <?php
                  $sql = "SELECT * FROM orderdata_new";
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
                if (isset($_GET['numberWork'])) {
                  $currentIndex = array_search($_GET['numberWork'], array_column($d, 'id'));

                  // Button for going to the previous record
                  $prevIndex = $currentIndex - 1;
                  $prevDisabled = $prevIndex < 0 ? 'disabled' : '';
                  echo '<button type="button" class="btn btn-secondary me-3 mb-3 p-3" ' . $prevDisabled . ' onclick="window.location.href=\'?numberWork=\' + ' . ($prevIndex >= 0 ? $d[$prevIndex]['id'] : 0) . '">ย้อนกลับ</button>';

                  // Button for going to the next record
                  $nextIndex = $currentIndex + 1;
                  $nextDisabled = $nextIndex >= count($d) ? 'disabled' : '';
                  echo '<button type="button" class="btn btn-primary me-3 mb-3 p-3" ' . $nextDisabled . ' onclick="window.location.href=\'?numberWork=\' + ' . ($nextIndex < count($d) ? $d[$nextIndex]['id'] : 0) . '">ถัดไป</button>';
                }
                ?>

              </form>
            </div>
            <?php
            $sql = "SELECT status, COUNT(*) as count FROM orderdata GROUP BY status";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $statusOptions = array(
              1 => array(
                'text' => "รอรับเอกสารจากหน่วยงาน",
                'color' => "#FF5733"
              ),
              2 => array(
                'text' => "รอส่งเอกสารไปพัสดุ",
                'color' => "#1CD23C"
              ),
              3 => array(
                'text' => "รอพัสดุสั่งของ",
                'color' => "#5733FF"
              ),
              4 => array(
                'text' => "รอหมายเลขครุภัณฑ์",
                'color' => "#FF33C7"
              ),
              5 => array(
                'text' => "ปิดงาน",
                'color' => "#33C7FF"
              ),
              6 => array(
                'text' => "ยกเลิก",
                'color' => "#C733FF"
              )
            );
            foreach ($statusCounts as $statusCount) {
              $status = $statusCount['status'];
              $count = $statusCount['count'];

              $textS = isset($statusOptions[$status]['text']) ? $statusOptions[$status]['text'] : "ไม่ระบุสถานะ";
              $color = isset($statusOptions[$status]['color']) ? $statusOptions[$status]['color'] : sprintf('#%06X', rand(0, 0xFFFFFF));

            ?>
              <div class="col-sm-6">
                <div class="card text-white me-3 mb-3" style="max-width: 18rem; background-color: <?= $color ?>">
                  <div class="card-header">
                    <ion-icon name="people-outline"></ion-icon>
                    <?= $textS ?>
                  </div>
                  <div class="card-body">
                    <h5 class="card-title"><?= $count ?></h5>
                    <p class="card-text">
                      <a href="checkStatus.php?status=<?= $status ?>" class="text-white" style="text-decoration: none;"> รายละเอียดเพิ่มเติม</a>
                    </p>
                  </div>
                </div>
              </div>
            <?php
            }
            ?>
          </div>
        </div>

        <div class="col-sm-12 col-md-12 col-lg-6">
          <form action="system/update.php" method="POST">

            <div class="d-flex justify-content-end mb-3">
              <input type="hidden" name="numberWork" value="<?= $numberWork ?>">
              <a href="create.php?id=<?= $numberWork ?>" class="btn btn-secondary p-2 me-3">คัดลอก</a>
              <button id="editData" class="btn btn-warning p-2 me-3">แก้ไข</button>

              <!-- <button type="submit" disabled name="updateData" class="btn btn-success p-2 me-3">บันทึก</button> -->
            </div>

            <div class="row">
              <div class="col-6">
                <label>วันที่ออกใบเบิก</label>
                <input type="date" class="form-control"
                  value="<?= $order['dateWithdraw'] ?? '' ?>" disabled>
              </div>

              <div class="col-6">
                <label>ผู้รับเรื่อง</label>
                <input type="text" class="form-control"
                  value="<?= $order['refUsername'] ?? '' ?>" disabled>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <label>ส่งซ่อมอุปกรณ์ คอมพิวเตอร์</label>
                <input type="text" class="form-control"
                  value="<?= $order['device_name'] ?? '' ?>" disabled>
              </div>
              <div class="col-6">
                <label>หมายเลขพัสดุ / ครุภัณฑ์</label>
                <div id="device-number-container">
                  <?php foreach ($devices as $index => $device): ?>
                    <div class="d-flex device-number-row">
                      <input type="text" name="device_numbers[]" class="form-control mb-2" value="<?= htmlspecialchars($device) ?>" disabled>
                      <button type="button" class="btn btn-warning p-2 ms-3 mb-2 remove-field" style="display: none; visibility: <?= $index === 0 ? 'hidden' : 'visible' ?>;">ลบ</button>
                    </div>
                  <?php endforeach; ?>
                </div>
                <div class="d-flex justify-content-end">
                  <button type="button" id="add-device-number" class="btn btn-success mt-2 align-self-end" style="display: none;">+ เพิ่มหมายเลขครุภัณฑ์</button>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <label>อาการที่รับแจ้ง</label>
                <input type="text" class="form-control" name="report"
                  value="<?= $order['report'] ?>" disabled>
              </div>
            </div>

            <div class="row">
              <div class="col-6">
                <label>รายละเอียด</label>
                <input type="text" class="form-control" name="reason"
                  value="<?= $order['reason'] ?>" disabled>
              </div>
              <div class="col-6">
                <label>หน่วยงานที่แจ้ง</label>
                <input type="text" class="form-control"
                  value="<?= $order['depart_name'] ?>" disabled>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <label>หมายเหตุ</label>
                <input type="text" class="form-control" name="note"
                  value="<?= $order['note'] ?>" disabled>
              </div>
            </div>
            <?php
            $order_id = $order['id'];
            $sql = "
    SELECT status, timestamp 
    FROM order_status 
    WHERE order_id = :order_id 
    ORDER BY status";
            $stmt = $conn->prepare(query: $sql);
            $stmt->execute(['order_id' => $order_id]);
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if the order is canceled (status = 6 exists)
            $isCanceled = in_array(6, array_column($statuses, 'status'));

            // Define status names
            $statusNames = [
              1 => "รอรับเอกสารจากหน่วยงาน",
              2 => "รอส่งเอกสารไปพัสดุ",
              3 => "รอพัสดุสั่งของ",
              4 => "รอหมายเลขครุภัณฑ์",
              5 => "ปิดงาน",
              6 => "ยกเลิก"
            ];

            // Current status from records
            $currentStatus = !empty($statuses) ? max(array_column($statuses, 'status')) : 0;
            ?>
            <h4 class="mt-3">สถานะ</h4>
            <table id="pdf" style="width: 100%;" class="table">
              <thead class="table-warning">
                <tr class="text-center">
                  <th scope="col">สถานะ</th>
                  <th scope="col">วันที่อัพเดตสถานะ</th>
                  <th scope="col">ปุ่มยืนยัน</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <?php
                foreach ($statusNames as $key => $name) {
                  $record = array_filter($statuses, fn($row) => $row['status'] == $key);
                  $timestamp = $record ? reset($record)['timestamp'] : null;

                  echo "<tr>";
                  echo "<td>{$name}</td>";
                  echo "<td>" . ($timestamp
                    ? date('d/m/Y', strtotime($timestamp))
                    : (($key == $currentStatus + 1 || $key == 6) && !$isCanceled ? date('d/m/Y') : '-')) . "</td>";
                  echo "<td>";

                  if ($isCanceled) {
                    if ($key == 6) {
                      echo "<p class='text-danger'>ยกเลิกใบเบิกแล้ว</p>";
                    } else {
                      if ($timestamp) {
                        echo "<p>ยืนยันแล้ว</p>";
                      } else {
                        echo "<p >-</p>";
                      }
                    }
                  } else {
                    if ($timestamp) {
                      echo "<p>ยืนยันแล้ว</p>";
                    } elseif ($key == $currentStatus + 1 && $key <= 5) {
                      echo "<button type='button' class='btn mb-3 btn-success confirm-btn' data-status='{$key}' data-order-id='{$order_id}'>รอการยืนยัน</button>";
                    } elseif ($key > $currentStatus + 1 && $key <= 5) {
                      echo "<button type='button' class='btn mb-3 btn-secondary' disabled>รอดำเนินการก่อนหน้า</button>";
                    }
                  }

                  if ($key == 6 && !$isCanceled) {
                    echo "<button type='button' class='btn mb-3 btn-danger cancel-btn' data-status='6' data-order-id='{$order_id}'>ยกเลิกใบเบิก</button>";
                  }

                  echo "</td>";
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>
            <h4>รายการเบิก</h4>

            <a href="แบบฟอร์มคำขอส่งซ่อมบำรุงอุปกรณ์คอมพิวเตอร์.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">ใบซ่อม</a>
            <a target="_blank" href="ใบเบิก.php?workid=<?= $numberWork ?>" class="btn btn-primary p-2">ใบเบิกครุภัณฑ์</a>
            <a href="พิมพ์ใบครุภัณฑ์.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">ใบกำหนดคุณสมบัติ</a>
            <a href="เอกสารคณะกรรมการ.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">เอกสารคณะกรรมการ</a>
            <a href="พิมพ์สติ๊กเกอร์.php?workid=<?= $numberWork ?>" target="_blank" class="btn btn-primary p-2">สติ๊กเกอร์งาน</a>

            <table id="pdf" style="width: 100%;" class="table">
              <thead class="text-center table-primary">
                <tr>
                  <th scope="col">ลำดับ</th>
                  <th scope="col">รายการ</th>
                  <th scope="col">คุณสมบัติ</th>
                  <th scope="col">จำนวน</th>
                  <th scope="col">ราคา</th>
                  <th scope="col">หน่วย</th>
                  <th scope="col">จัดการ</th>
                </tr>
              </thead>
              <tbody id="table-body" class="text-center">
                <?php foreach ($items as $index => $item): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td>
                      <input disabled value="<?= htmlspecialchars($item['list']) ?>" style="width: 8rem;" type="text" class="form-control">
                    </td>
                    <td>
                      <textarea disabled class="form-control"><?= htmlspecialchars($item['quality']) ?></textarea>
                    </td>
                    <td>
                      <input disabled value="<?= htmlspecialchars($item['amount']) ?>" style="width: 3rem;" type="text" class="form-control">
                    </td>
                    <td>
                      <input disabled value="<?= htmlspecialchars($item['price']) ?>" style="width: 4rem;" type="text" class="form-control">
                    </td>
                    <td>
                      <input disabled value="<?= htmlspecialchars($item['unit']) ?>" style="width: 5rem;" type="text" class="form-control">
                    </td>
                    <td>
                      <button type="button" class="btn btn-warning remove-row" style="display: none;">ลบ</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            <div class="d-flex justify-content-end">
              <button type="button" id="add-row" class="btn btn-success" style="display: none;">+ เพิ่มแถว</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>


  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const editButton = document.getElementById("editData");
      const addDeviceButton = document.getElementById("add-device-number");
      const removeFields = document.querySelectorAll(".remove-field");

      editButton.addEventListener("click", function(event) {
        event.preventDefault();
        toggleEditMode();
      });

      function toggleEditMode() {
        const isDisabled = document.querySelector("#device-number-container input").disabled;
        removeFields.forEach(function(button) {
          button.style.display = isDisabled ? "inline-block" : "none";
        });
        addDeviceButton.style.display = isDisabled ? "inline-block" : "none";
      }

      addDeviceButton.addEventListener("click", function() {
        const container = document.getElementById("device-number-container");
        const newRow = document.createElement("div");
        newRow.className = "d-flex device-number-row";
        newRow.innerHTML = `
        <input type="text" name="device_numbers[]" class="form-control mb-2" value="">
        <button type="button" class="btn btn-warning p-2 ms-3 remove-field mb-2">ลบ</button>
      `;
        container.appendChild(newRow);

        const newRemoveButton = newRow.querySelector(".remove-field");
        newRemoveButton.addEventListener("click", function() {
          newRow.remove();
        });
      });

      removeFields.forEach(function(button) {
        button.addEventListener("click", function() {
          const row = button.closest(".device-number-row");
          row.remove();
        });
      });
    });

    document.addEventListener("DOMContentLoaded", function() {
      var editButton = document.getElementById("editData");
      editButton.addEventListener("click", function(event) {
        event.preventDefault(); // ป้องกัน default behavior ของการ click ลิงก์หรือ submit form
        enableInputs();
      });
    });

    function enableInputs() {
      var inputNames = ["list", "quality", "amount", "price", "unit"];

      const deviceInputs = document.querySelectorAll("#device-number-container input");
      deviceInputs.forEach(function(input) {
        input.disabled = !input.disabled;
      });

      ["report", "reason", "note"].forEach(function(name) {
        var input = document.querySelector(`input[name='${name}']`);
        if (input) {
          input.disabled = !input.disabled;
        }
      });

      var tableInputs = document.querySelectorAll("#pdf input, #pdf textarea");
      tableInputs.forEach(function(input) {
        input.disabled = !input.disabled;
      });

      // for (var i = 1; i <= 15; i++) {
      //   inputNames.forEach(function(name) {
      //     var input = document.querySelector("input[name='" + name + i + "']");
      //     if (input) {
      //       input.disabled = false;
      //     }
      //     var textarea = document.querySelector("textarea[name='" + name + i + "']");
      //     if (textarea) {
      //       textarea.disabled = false;
      //     }
      //     var select = document.querySelector("select[name='" + name + i + "']");
      //     if (select) {
      //       select.disabled = false;
      //     }
      //   });
      //   var status = document.getElementById("statusD");
      //   if (status) {
      //     status.disabled = false;
      //   }
      //   var saveButton = document.querySelector("button[name='updateData']");
      //   if (saveButton) {
      //     saveButton.disabled = false;
      //   }
      //   var show_receipt_date = document.querySelector("input[name='show_receipt_date']");
      //   if (show_receipt_date) {
      //     show_receipt_date.disabled = false;
      //   }
      //   var show_delivery_date = document.querySelector("input[name='show_delivery_date']");
      //   if (show_delivery_date) {
      //     show_delivery_date.disabled = false;
      //   }
      //   var show_close_date = document.querySelector("input[name='show_close_date']");
      //   if (show_close_date) {
      //     show_close_date.disabled = false;
      //   }
      //   var receipt_date = document.querySelector("input[name='receipt_date']");
      //   if (receipt_date) {
      //     receipt_date.disabled = false;
      //   }
      //   var close_date = document.querySelector("input[name='close_date']");
      //   if (close_date) {
      //     close_date.disabled = false;
      //   }

      //   var delivery_date = document.querySelector("input[name='delivery_date']");
      //   if (delivery_date) {
      //     delivery_date.disabled = false;
      //   }

      //   var currentSumInput = document.querySelector("input[name='currentSum" + i + "']");
      //   if (currentSumInput) {
      //     currentSumInput.readOnly = false;
      //   }
      // }
    }
  </script>
<?php } ?>

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
    return englishYear + 543;
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
      minLength: 2,
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
          alert(data);
          location.reload();
        });
    });
  });
</script>

<?php SC5() ?>
</body>

</html>