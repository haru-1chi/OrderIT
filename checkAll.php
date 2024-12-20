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
    <title>สร้างใบเบิกประจำสัปดาห์ | IT ORDER PRO</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
</head>

<body>

    <?php navbar();
    ?>

    <div class="mt-5">
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
            <?php }
            ?>
        </div>
        <h1 class="text-center my-5">สร้างใบเบิกประจำสัปดาห์</h1>
        <div class="row">
            <div class="card col-sm-6">
                <h1 class="mb-3">รอส่งเอกสารไปพัสดุ</h1>
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
 oi.list AS list_id, 
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
  LEFT JOIN 
  order_status AS os ON od.id = os.order_id
  WHERE 
  (os.status = 2) AND
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
                                'numberWork' => $row['numberWork'],
                                'item_id' => $row['item_id'],
                                'list_id' => $row['list_id'],
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
                    echo "No records found.";
                    $order = [];
                    $devices = [];
                    $items = [];
                }
                ?>
                <!-- <form action="system/update.php" method="POST"> -->
                <table id="example" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">หมายเลขงาน</th>
                            <th class="text-center">รายการ</th>
                            <th class="text-center">คุณสมบัติ</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-center">ราคา</th>
                            <th class="text-center">ส่งแล้ว</th>
                            <th class="text-center">เพิ่มไปยังใบเบิกประจำสัปดาห์</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $groupedItems = [];
                        foreach ($items as $item) {
                            $groupedItems[$item['numberWork']][] = $item;
                        }

                        foreach ($groupedItems as $numberWork => $group):
                            $firstRow = $group[0];
                        ?>
                            <!-- First row with toggle button -->
                            <tr class="text-center">
                                <td>
                                    <?php if (count($group) > 1): ?>
                                        <button style="width: 40px; height: 40px;" class="btn toggle-expand rounded-circle" data-numberwork="<?= $numberWork ?>">></button>
                                    <?php endif; ?>
                                </td>
                                <td><?= $numberWork ?></td>
                                <td style="text-align:left;" data-id="<?= $firstRow["list_id"] ?>"><?= $firstRow["list"] ?></td>
                                <td style="text-align:left;"><?= $firstRow["quality"] ?></td>
                                <td><?= $firstRow["amount"] ?></td>
                                <td><?= $firstRow["price"] ?></td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="selectedRows[]" value="<?= $firstRow["item_id"] ?>">
                                        <input class="thaiDateInput" name="deliveryDate[<?= $firstRow["item_id"] ?>]" type="hidden">
                                    </div>
                                </td>
                                <td><button type="button" class="btn btn-success copy-row">เพิ่ม</button></td>
                            </tr>
                            <tr>

                            </tr>
                            <!-- Hidden rows -->
                            <?php for ($i = 1; $i < count($group); $i++): $item = $group[$i]; ?>
                                <tr class="text-center expand-row d-none" data-numberwork="<?= $numberWork ?>">
                                    <td></td>
                                    <td></td>
                                    <td style="text-align:left;" data-id="<?= $item["list_id"] ?>"><?= $item["list"] ?></td>
                                    <td style="text-align:left;"><?= $item["quality"] ?></td>
                                    <td><?= $item["amount"] ?></td>
                                    <td><?= $item["price"] ?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="d-grid gap-3 mt-3">
                    <button class="btn btn-primary" type="submit" name="updateStatus">อัพเดท</button>
                </div>
                <!-- </form> -->
                <hr>
                <div class="row">
                    <h1 class="mb-3">รอพัสดุสั่งของ</h1>
                    <div class="col-sm-12">
                        <?php
                        $sql = "SELECT od.*, dm1.models_name AS model1, dm2.models_name AS model2, dm3.models_name AS model3, dm4.models_name AS model4, dm5.models_name AS model5, dm6.models_name AS model6, dm7.models_name AS model7, dm8.models_name AS model8, dm9.models_name AS model9, dm10.models_name AS model10, dm11.models_name AS model11, dm12.models_name AS model12, dm13.models_name AS model13, dm14.models_name AS model14, dm15.models_name AS model15,
od.quality1, od.quality2, od.quality3, od.quality4, od.quality5, od.quality6, od.quality7, od.quality8, od.quality9, od.quality10, od.quality11, od.quality12, od.quality13, od.quality14, od.quality15,
od.amount1, od.amount2, od.amount3, od.amount4, od.amount5, od.amount6, od.amount7, od.amount8, od.amount9, od.amount10, od.amount11, od.amount12, od.amount13, od.amount14, od.amount15,
od.price1, od.price2, od.price3, od.price4, od.price5, od.price6, od.price7, od.price8, od.price9, od.price10, od.price11, od.price12, od.price13, od.price14, od.price15
FROM orderdata AS od
LEFT JOIN device_models AS dm1 ON od.list1 = dm1.models_id
LEFT JOIN device_models AS dm2 ON od.list2 = dm2.models_id
LEFT JOIN device_models AS dm3 ON od.list3 = dm3.models_id
LEFT JOIN device_models AS dm4 ON od.list4 = dm4.models_id
LEFT JOIN device_models AS dm5 ON od.list5 = dm5.models_id
LEFT JOIN device_models AS dm6 ON od.list6 = dm6.models_id
LEFT JOIN device_models AS dm7 ON od.list7 = dm7.models_id
LEFT JOIN device_models AS dm8 ON od.list8 = dm8.models_id
LEFT JOIN device_models AS dm9 ON od.list9 = dm9.models_id
LEFT JOIN device_models AS dm10 ON od.list10 = dm10.models_id
LEFT JOIN device_models AS dm11 ON od.list11 = dm11.models_id
LEFT JOIN device_models AS dm12 ON od.list12 = dm12.models_id
LEFT JOIN device_models AS dm13 ON od.list13 = dm13.models_id
LEFT JOIN device_models AS dm14 ON od.list14 = dm14.models_id
LEFT JOIN device_models AS dm15 ON od.list15 = dm15.models_id
WHERE od.status = 3
";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <form action="system/update.php" method="POST">
                            <table id="example3" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">หมายเลขงาน</th>
                                        <th class="text-center">รายการ</th>
                                        <th class="text-center">คุณสมบัติ</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center">ราคา</th>
                                        <th class="text-center">ของมาแล้ว</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result) : ?>
                                        <?php for ($i = 1; $i <= 15; $i++) : ?>
                                            <?php
                                            // ตรวจสอบว่า model, quality, amount, price ไม่ใช่ค่าว่าง
                                            if ($result["model$i"] !== null && $result["quality$i"] !== null && $result["amount$i"] !== null && $result["price$i"] !== null) :
                                            ?>
                                                <tr class="text-center">
                                                    <td><?= $result["numberWork"] ?></td>
                                                    <td><?= $result["model$i"] ?></td>
                                                    <td><?= $result["quality$i"] ?></td>
                                                    <td><?= $result["amount$i"] ?></td>
                                                    <td><?= $result["price$i"] ?></td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" role="switch" name="selectedRows3[]" value="<?= $result["id"] ?>">
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="d-grid gap-3 mt-3">
                                <button class="btn btn-primary" type="submit" name="updateStatus3">อัพเดท</button>
                            </div>
                        </form>
                    </div>
                    <hr class="mt-3">
                    <h1 class="mb-3">รอหมายเลขครุภัณฑ์</h1>
                    <div class="col-sm-12">
                        <?php
                        $sql = "SELECT od.*, dm1.models_name AS model1, dm2.models_name AS model2, dm3.models_name AS model3, dm4.models_name AS model4, dm5.models_name AS model5, dm6.models_name AS model6, dm7.models_name AS model7, dm8.models_name AS model8, dm9.models_name AS model9, dm10.models_name AS model10, dm11.models_name AS model11, dm12.models_name AS model12, dm13.models_name AS model13, dm14.models_name AS model14, dm15.models_name AS model15,
od.quality1, od.quality2, od.quality3, od.quality4, od.quality5, od.quality6, od.quality7, od.quality8, od.quality9, od.quality10, od.quality11, od.quality12, od.quality13, od.quality14, od.quality15,
od.amount1, od.amount2, od.amount3, od.amount4, od.amount5, od.amount6, od.amount7, od.amount8, od.amount9, od.amount10, od.amount11, od.amount12, od.amount13, od.amount14, od.amount15,
od.price1, od.price2, od.price3, od.price4, od.price5, od.price6, od.price7, od.price8, od.price9, od.price10, od.price11, od.price12, od.price13, od.price14, od.price15
FROM orderdata AS od
LEFT JOIN device_models AS dm1 ON od.list1 = dm1.models_id
LEFT JOIN device_models AS dm2 ON od.list2 = dm2.models_id
LEFT JOIN device_models AS dm3 ON od.list3 = dm3.models_id
LEFT JOIN device_models AS dm4 ON od.list4 = dm4.models_id
LEFT JOIN device_models AS dm5 ON od.list5 = dm5.models_id
LEFT JOIN device_models AS dm6 ON od.list6 = dm6.models_id
LEFT JOIN device_models AS dm7 ON od.list7 = dm7.models_id
LEFT JOIN device_models AS dm8 ON od.list8 = dm8.models_id
LEFT JOIN device_models AS dm9 ON od.list9 = dm9.models_id
LEFT JOIN device_models AS dm10 ON od.list10 = dm10.models_id
LEFT JOIN device_models AS dm11 ON od.list11 = dm11.models_id
LEFT JOIN device_models AS dm12 ON od.list12 = dm12.models_id
LEFT JOIN device_models AS dm13 ON od.list13 = dm13.models_id
LEFT JOIN device_models AS dm14 ON od.list14 = dm14.models_id
LEFT JOIN device_models AS dm15 ON od.list15 = dm15.models_id
WHERE od.status = 4
";

                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <form action="system/update.php" method="POST">
                            <table id="example4" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">หมายเลขงาน</th>
                                        <th class="text-center">รายการ</th>
                                        <th class="text-center">คุณสมบัติ</th>
                                        <th class="text-center">จำนวน</th>
                                        <th class="text-center">ราคา</th>
                                        <th class="text-center">ปิดงาน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result) : ?>
                                        <?php for ($i = 1; $i <= 15; $i++) : ?>
                                            <?php
                                            // ตรวจสอบว่า model, quality, amount, price ไม่ใช่ค่าว่าง
                                            if ($result["model$i"] !== null && $result["quality$i"] !== null && $result["amount$i"] !== null && $result["price$i"] !== null) :
                                            ?>
                                                <tr class="text-center">
                                                    <td><?= $result["numberWork"] ?></td>
                                                    <td><?= $result["model$i"] ?></td>
                                                    <td><?= $result["quality$i"] ?></td>
                                                    <td><?= $result["amount$i"] ?></td>
                                                    <td><?= $result["price$i"] ?></td>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" role="switch" name="selectedRows4[]" value="<?= $result["id"] ?>">
                                                            <input class="thaiDateInput" name="close_date[<?= $result["id"] ?>]" type="hidden">

                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="d-grid gap-3 mt-3">
                                <button class="btn btn-primary" type="submit" name="updateStatus4">อัพเดท</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
            <div class="card col-sm-6">
                <h1>ใบเบิกประจำสัปดาห์</h1>
                <form action="system/insert.php" method="POST">
                    <div class="d-flex mb-3">
                        <input type="text" name="reason" placeholder="เหตุผลและความจำเป็น" class="form-control" style="width: 300px">
                        <button type="submit" name="CheckAll" class="ms-2 btn btn-primary">บันทึกข้อมูล</button>
                    </div>
                    <input type="hidden" name="dateWithdraw" class="form-control thaiDateInput">
                    <input type="hidden" name="numberWork" value="<?= $newValueToCheck ?>">
                    <input type="hidden" name="username" value="<?= $fullname ?>">
                    <table id="example" class="table">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">รายการ</th>
                                <th class="text-center">คุณสมบัติ</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-center">ราคา</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-center">
                            <tr class="text-center">
                                <td style="width: 200px">
                                    <select class="form-select device-select" name="list[]" data-row="1">
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
                                <td style="width: 250px"><textarea rows="2" maxlength="60" name="quality[]" class="form-control"></textarea></td>
                                <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
                                <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[]" class="form-control"></td>
                                <td><button type="button" class="btn btn-danger" style="visibility: hidden;">ลบ</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end">
                        <button type="button" id="add-row" class="btn btn-success">+ เพิ่มแถว</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // new DataTable('#example');
        new DataTable('#example3');
        new DataTable('#example4');
        new DataTable('#example5');
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButtons = document.querySelectorAll('.toggle-expand');

            toggleButtons.forEach(button => {
                button.addEventListener('click', (event) => {
                    const numberWork = button.getAttribute('data-numberwork');
                    const rows = document.querySelectorAll(`.expand-row[data-numberwork="${numberWork}"]`);

                    rows.forEach(row => {
                        if (row.classList.contains('d-none')) {
                            row.classList.remove('d-none');
                            row.classList.add('table-row');
                        } else {
                            row.classList.remove('table-row');
                            row.classList.add('d-none');
                        }
                    });

                    // Optionally toggle button text
                    button.textContent = button.textContent.trim() === '>' ? 'v' : '>';
                });
            });
        });
    </script>

<script>
    let rowIndex = 1;

    // Adding a row
    document.getElementById('add-row').addEventListener('click', function() {
        rowIndex++;
        const tableBody = document.getElementById('table-body');
        const newRow = document.createElement('tr');
        newRow.className = 'text-center';
        newRow.innerHTML = `
            <td>
                <select class="form-select device-select" name="list[]" data-row="${rowIndex}">
                    <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                    <?php
                        foreach ($result as $d) {
                    ?>
                    <option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
                    <?php
                        }
                    ?>
                </select>
            </td>
            <td><textarea rows="2" maxlength="60" name="quality[]" class="form-control"></textarea></td>
            <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
            <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[]" class="form-control"></td>
            <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
        `;
        tableBody.appendChild(newRow);
    });

    // Event listener for copying a row (copy-row button)
    document.querySelector('#example').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('copy-row')) {
            const row = e.target.closest('tr');
            const numberWork = row.querySelector('td:nth-child(2)').textContent.trim();

            const relatedRows = Array.from(document.querySelectorAll(`tr[data-numberwork="${numberWork}"]`));
            relatedRows.unshift(row);

            relatedRows.forEach(row => {
                const listId = row.querySelector('td:nth-child(3)').getAttribute('data-id');
                const quality = row.querySelector('td:nth-child(4)').textContent.trim();
                const amount = row.querySelector('td:nth-child(5)').textContent.trim();
                const price = row.querySelector('td:nth-child(6)').textContent.trim();

                const firstRow = document.querySelector('#table-body tr');
                const isFirstRowEmpty = !(
                    firstRow.querySelector('select').value ||
                    firstRow.querySelector('textarea').value.trim() ||
                    firstRow.querySelector('input[name="amount[]"]').value.trim() ||
                    firstRow.querySelector('input[name="price[]"]').value.trim()
                );

                if (isFirstRowEmpty) {
                    const select = firstRow.querySelector('select');
                    select.value = listId;
                    firstRow.querySelector('textarea').value = quality;
                    firstRow.querySelector('input[name="amount[]"]').value = amount;
                    firstRow.querySelector('input[name="price[]"]').value = price;
                } else {
                    const newRow = document.createElement('tr');
                    newRow.className = 'text-center';
                    newRow.innerHTML = `
                        <td>
                            <select class="form-select device-select" name="list[]" data-row="${rowIndex}">
                                <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                <?php foreach ($result as $d): ?>
                                    <option value="<?= $d['models_id'] ?>" ${listId === '<?= $d['models_id'] ?>' ? 'selected' : ''}>
                                        <?= $d['models_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><textarea rows="2" maxlength="60" name="quality[]" class="form-control">${quality}</textarea></td>
                        <td><input value="${amount}" style="width: 3rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
                        <td><input value="${price}" style="width: 5rem; margin: 0 auto;" type="text" name="price[]" class="form-control"></td>
                        <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
                    `;
                    document.querySelector('#table-body').appendChild(newRow);
                    rowIndex++;
                }
            });
        }
    });

    // Remove row functionality
    document.getElementById('table-body').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-row')) {
            e.target.parentElement.parentElement.remove();
            rowIndex--; // Adjust rowIndex to ensure consistent indexing
        }
    });

    $(document).ready(function() {
        $('#table-body').on('change', '.device-select', function() {
            var models_id = $(this).val();
            var row = $(this).data('row');  // Ensure `row` is taken from `data-row`, representing the correct row

            if (models_id) {
                $.ajax({
                    url: 'autoList.php',
                    type: 'POST',
                    data: {
                        models_id: models_id
                    },
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            // Update fields in the corresponding row
                            $(`#table-body tr:nth-child(${row}) textarea[name="quality[]"]`).val(data.quality);
                            $(`#table-body tr:nth-child(${row}) input[name="price[]"]`).val(data.price);
                            $(`#table-body tr:nth-child(${row}) input[name="unit[]"]`).val(data.unit);
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
    });
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



    <?php SC5() ?>
</body>

</html>