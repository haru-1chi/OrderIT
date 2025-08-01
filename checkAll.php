<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
    exit;
}
$admin = $_SESSION['admin_log'];
$sql = "SELECT CONCAT(fname, ' ', lname) AS full_name FROM admin WHERE username = :admin LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(":admin", $admin);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$fullname = $result['full_name'] ?? '-';

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
    <style>
        body {
            background-color: #F9FDFF;
        }

        .container-custom {
            max-width: 1600px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>

<body>

    <?php navbar(); ?>

    <div class="container-custom mt-5">
        <div class="mt-5">
            <?php foreach (['error' => 'danger', 'warning' => 'warning', 'success' => 'success'] as $key => $class): ?>
                <?php if (isset($_SESSION[$key])): ?>
                    <div class="alert alert-<?= $class ?>" role="alert">
                        <?= htmlspecialchars($_SESSION[$key], ENT_QUOTES, 'UTF-8') ?>
                        <?php unset($_SESSION[$key]); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <h1 class="text-center my-5">สร้างใบเบิกประจำสัปดาห์</h1>
        <div class="row d-flex justify-content-between">
            <div class="card col-sm-12 col-md-12 col-lg-6 p-3" style="width: 870px">
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
               LEFT JOIN 
               device_models AS dm ON oi.list = dm.models_id
               LEFT JOIN (
                   SELECT os1.order_id, os1.status
                   FROM order_status AS os1
                   INNER JOIN (
                       SELECT order_id, MAX(CONCAT(timestamp, id)) AS latest
                       FROM order_status
                       GROUP BY order_id
                   ) AS latest_status
                   ON CONCAT(os1.timestamp, os1.id) = latest_status.latest AND os1.order_id = latest_status.order_id
                   WHERE os1.status = 2
               ) AS os ON od.id = os.order_id
               WHERE 
               os.status = 2 AND
               (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
               AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
               ORDER BY od.id
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
                                'id' => $row['id'],
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
                    $order = [];
                    $devices = [];
                    $items = [];
                }
                ?>
                <!-- <form action="system/update.php" method="POST"> -->
                <table id="example" class="table table-bordered">
                    <thead class="table-danger">
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">หมายเลขงาน</th>
                            <th class="text-center">รายการ</th>
                            <th class="text-center">คุณสมบัติ</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-center">ราคา</th>
                            <th class="text-center" style="display: none;">หน่วย</th>
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
                                <?php if (count($group) > 1): ?>
                                    <td> <button style="width: 40px; height: 40px;" class="btn toggle-expand rounded-circle" data-numberwork="<?= $numberWork ?>">></button> </td>
                                <?php else: ?>
                                    <td>
                                        <p style="display: none;">></p>
                                    </td>
                                <?php endif; ?>
                                <td><?= $numberWork ?></td>
                                <td style="text-align:left;" data-id="<?= $firstRow["list_id"] ?>"><?= $firstRow["list"] ?></td>
                                <td style="text-align:left;"><?= $firstRow["quality"] ?></td>
                                <td><?= $firstRow["amount"] ?></td>
                                <td><?= $firstRow["price"] ?></td>
                                <td style="display: none;"><?= $firstRow["unit"] ?></td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input confirm-status-switch" type="checkbox" data-order-id="<?= $firstRow['id'] ?>">
                                        <label class="form-check-label" for="confirm-switch-<?= $firstRow['id'] ?>"></label>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input copy-row-switch" type="checkbox" data-numberwork="<?= $numberWork ?>" id="switch-<?= $numberWork ?>">
                                        <label class="form-check-label" for="switch-<?= $numberWork ?>"></label>
                                    </div>
                                </td>
                            </tr>
                            <?php for ($i = 1; $i < count($group); $i++): $item = $group[$i]; ?>
                                <tr class="text-center expand-row d-none" data-numberwork="<?= $numberWork ?>">
                                    <td>
                                        <p style="display: none;">></p>
                                    </td>
                                    <td></td>
                                    <td style="text-align:left;" data-id="<?= $item["list_id"] ?>"><?= $item["list"] ?></td>
                                    <td style="text-align:left;"><?= $item["quality"] ?></td>
                                    <td><?= $item["amount"] ?></td>
                                    <td><?= $item["price"] ?></td>
                                    <td style="display: none;"><?= $item["unit"] ?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card col-sm-12 col-md-12 col-lg-6 p-3" style="width: 730px">
                <h1>ใบเบิกประจำสัปดาห์</h1>
                <form action="system/insert.php" method="POST">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <input type="text" name="reason" placeholder="เหตุผลและความจำเป็น" class="form-control" style="width: 300px">
                            <button type="submit" name="CheckAll" class="ms-2 btn btn-primary">บันทึกข้อมูล</button>
                        </div>
                        <p class="m-0 fs-5">รวมทั้งหมด <span id="total-amount" class="fs-4 fw-bold text-primary">0</span> บาท</p>
                    </div>
                    <input type="hidden" name="username" value="<?= $fullname ?>">
                    <table class="table">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">รายการ</th>
                                <th class="text-center">คุณสมบัติ</th>
                                <th class="text-center">จำนวน</th>
                                <th class="text-center">ราคา</th>
                                <th class="text-center">รวม</th>
                                <th class="text-center">หน่วย</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="table-body" class="text-center">
                            <tr class="text-center">
                                <td style="width: 150px">
                                    <select class="form-select device-select" name="list[]" data-row="1">
                                        <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
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
                                <td><textarea rows="2" maxlength="60" name="quality[]" class="form-control"></textarea></td>
                                <td><input style="width: 3rem; margin: 0 auto;" type="text" name="amount[]" class="form-control"></td>
                                <td><input style="width: 5rem; margin: 0 auto;" type="text" name="price[]" class="form-control"></td>
                                <td><input disabled style="width: 5rem;" type="text" class="form-control sum"></td>
                                <td><input name="unit[]" style="width: 4rem;" type="text" class="form-control"></td>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new DataTable('#example', {
                paging: false,
                searching: true,
                responsive: true,
                ordering: false
            });
        });
    </script>
    <script>
        //expand row
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

        function calculateTotal() {
            let total = 0;
            const sumInputs = document.querySelectorAll('input.sum');
            sumInputs.forEach(input => {
                total += parseFloat(input.value) || 0; // Make sure to handle NaN if value is empty
            });
            // Update the total display
            document.getElementById('total-amount').textContent = total.toLocaleString();
        }

        function updateRowIndexes() {
            const rows = document.querySelectorAll('#table-body tr');
            rows.forEach((row, index) => {
                const rowIndex = index + 1;
                const select = row.querySelector('select.device-select');
                if (select) {
                    select.setAttribute('data-row', rowIndex); // Update data-row attribute
                }
            });
        }

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
            <td><input disabled style="width: 5rem;" type="text" class="form-control sum"></td>
            <td><input name="unit[]" style="width: 4rem;" type="text" class="form-control"></td>
            <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>
        `;
            tableBody.appendChild(newRow);
            updateRowIndexes();
            calculateTotal();
        });

        //case ที่เหลือ แทนที่/ย้อนกลับแถวแรกที่ว่าง, เพิ่ม/ลบจำนวน
        const toggledStates = new Map(); //เก็บค่าสำหรับ
        document.querySelector('#example').addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('copy-row-switch')) {
                const switchElement = e.target;
                const isChecked = switchElement.checked;
                const numberWork = switchElement.getAttribute('data-numberwork');

                if (isChecked) {
                    // Toggle ON: Copy rows
                    const row = switchElement.closest('tr');
                    const relatedRows = Array.from(document.querySelectorAll(`tr[data-numberwork="${numberWork}"]`));
                    relatedRows.unshift(row);

                    // Store related rows in Map for later removal
                    toggledStates.set(numberWork, relatedRows);
                    relatedRows.forEach(row => {
                        const listId = row.querySelector('td:nth-child(3)').getAttribute('data-id');
                        const listName = row.querySelector('td:nth-child(3)').textContent.trim();
                        const quality = row.querySelector('td:nth-child(4)').textContent.trim();
                        const amount = parseInt(row.querySelector('td:nth-child(5)').textContent.trim(), 10) || 0;
                        const price = parseFloat(row.querySelector('td:nth-child(6)').textContent.trim()) || 0;
                        const unit = row.querySelector('td:nth-child(7)').textContent.trim();

                        const tableBody = document.querySelector('#table-body');
                        const existingRows = Array.from(tableBody.querySelectorAll('tr'));

                        let isUpdated = false;

                        existingRows.forEach(existingRow => {
                            const existingName = existingRow.querySelector('select option:checked').textContent.trim();
                            const existingQuality = existingRow.querySelector('textarea').value.trim();
                            const existingAmountInput = existingRow.querySelector('input[name="amount[]"]');
                            const existingPriceInput = existingRow.querySelector('input[name="price[]"]');

                            if (existingName === listName && existingQuality === quality) {
                                const existingAmount = parseInt(existingAmountInput.value, 10) || 0;
                                existingAmountInput.value = existingAmount + amount; // Update the amount
                                isUpdated = true;

                                const sumInput = existingRow.querySelector('input.sum');
                                sumInput.value = (parseFloat(existingAmountInput.value) || 0) * (parseFloat(existingPriceInput.value) || 0);

                                calculateTotal();
                            }
                        });

                        if (!isUpdated) {
                            const firstRow = tableBody.querySelector('tr');
                            const isFirstRowEmpty = firstRow && !(
                                firstRow.querySelector('select').value ||
                                firstRow.querySelector('textarea').value.trim() ||
                                firstRow.querySelector('input[name="amount[]"]').value.trim() ||
                                firstRow.querySelector('input[name="price[]"]').value.trim() ||
                                firstRow.querySelector('input[name="unit[]"]').value.trim()
                            );
                            if (isFirstRowEmpty) {
                                tableBody.removeChild(firstRow);
                                const newRow = createRow(listId, listName, quality, amount, price, unit, numberWork);
                                tableBody.appendChild(newRow);
                                // if (unit == 'ลบ') {
                                //     console.log('error at 464');
                                // }
                                updateRowIndexes();
                                calculateTotal();
                            } else {
                                // Create a new row if no match is found
                                const newRow = createRow(listId, listName, quality, amount, price, unit, numberWork);
                                tableBody.appendChild(newRow);
                                // if (unit == 'ลบ') {
                                //     console.log('error at 473');
                                // }
                                updateRowIndexes();
                                calculateTotal();
                            }
                        }
                    });
                } else {
                    // Toggle OFF: Undo copy rows
                    const rowsToRemove = toggledStates.get(numberWork);
                    if (rowsToRemove) {
                        // Remove from toggledStates
                        const tableBody = document.querySelector('#table-body');
                        const existingRows = Array.from(tableBody.querySelectorAll('tr'));
                        if (existingRows) {
                            existingRows.forEach(existingRow => {
                                const existingName = existingRow.querySelector('select option:checked').textContent.trim();
                                const existingQuality = existingRow.querySelector('textarea').value.trim();
                                const existingAmountInput = existingRow.querySelector('input[name="amount[]"]');
                                const existingPriceInput = existingRow.querySelector('input[name="price[]"]');

                                rowsToRemove.forEach(row => {
                                    const listName = row.querySelector('td:nth-child(3)').textContent.trim();
                                    const quality = row.querySelector('td:nth-child(4)').textContent.trim();
                                    const amount = parseInt(row.querySelector('td:nth-child(5)').textContent.trim(), 10) || 0;

                                    if (existingName === listName && existingQuality === quality) {
                                        const existingAmount = parseInt(existingAmountInput.value, 10) || 0;
                                        existingAmountInput.value = existingAmount - amount; // Subtract the amount
                                        const sumInput = existingRow.querySelector('input.sum');
                                        sumInput.value = (parseFloat(existingAmountInput.value) || 0) * (parseFloat(existingPriceInput.value) || 0);

                                        // Check if existingAmountInput is 0, remove the row
                                        if (parseInt(existingAmountInput.value, 10) === 0) {
                                            existingRow.remove();
                                        }
                                        calculateTotal();
                                    }
                                });
                            });
                        }
                    }

                    // Check if the table body has no rows
                    const tableBody = document.querySelector('#table-body');
                    if (tableBody.querySelectorAll('tr').length === 0) {
                        // Create an empty row
                        const newRow = createRow(null, null, '', '', '', '', null);
                        tableBody.appendChild(newRow);
                        calculateTotal();
                    }
                }
            }
        });

        function createRow(listId, listName, quality, amount, price, unit, numberWork) {
            const newRow = document.createElement('tr');
            newRow.className = 'text-center copied-row';
            newRow.setAttribute('copied-numberwork', numberWork);
            newRow.innerHTML = `
    <td>
        <select class="form-select device-select" name="list[]" data-row="0">
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
    <td><input disabled style="width: 5rem;" type="text" class="form-control sum" value="${amount * price}"></td>
    <td><input value="${unit}" name="unit[]" style="width: 4rem;" type="text" class="form-control"></td>
    <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>`;
            return newRow;
        }

        // Event listener for copying a row (copy-row button)
        document.querySelector('#example').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('copy-row')) {
                const row = e.target.closest('tr');
                const numberWork = row.querySelector('td:nth-child(2)').textContent.trim();

                const relatedRows = Array.from(document.querySelectorAll(`tr[data-numberwork="${numberWork}"]`));
                relatedRows.unshift(row);

                relatedRows.forEach(row => {
                    const listId = row.querySelector('td:nth-child(3)').getAttribute('data-id');
                    const listName = row.querySelector('td:nth-child(3)').textContent.trim();
                    const quality = row.querySelector('td:nth-child(4)').textContent.trim();
                    const amount = parseInt(row.querySelector('td:nth-child(5)').textContent.trim(), 10) || 0;
                    const price = parseFloat(row.querySelector('td:nth-child(6)').textContent.trim()) || 0;

                    const tableBody = document.querySelector('#table-body');
                    const existingRows = Array.from(tableBody.querySelectorAll('tr'));

                    let isUpdated = false;

                    existingRows.forEach(existingRow => {
                        const existingName = existingRow.querySelector('select option:checked').textContent.trim();
                        const existingQuality = existingRow.querySelector('textarea').value.trim();
                        const existingAmountInput = existingRow.querySelector('input[name="amount[]"]');
                        const existingPriceInput = existingRow.querySelector('input[name="price[]"]');

                        if (existingName === listName && existingQuality === quality) {
                            const existingAmount = parseInt(existingAmountInput.value, 10) || 0;
                            existingAmountInput.value = existingAmount + amount; // Update the amount
                            isUpdated = true;

                            const sumInput = existingRow.querySelector('input.sum');
                            sumInput.value = (parseFloat(existingAmountInput.value) || 0) * (parseFloat(existingPriceInput.value) || 0);

                            calculateTotal();
                        }
                    });

                    if (!isUpdated) {
                        const firstRow = tableBody.querySelector('tr');
                        const isFirstRowEmpty = firstRow && !(
                            firstRow.querySelector('select').value ||
                            firstRow.querySelector('textarea').value.trim() ||
                            firstRow.querySelector('input[name="amount[]"]').value.trim() ||
                            firstRow.querySelector('input[name="price[]"]').value.trim()
                        );

                        if (isFirstRowEmpty) {
                            const select = firstRow.querySelector('select');
                            const amountInput = firstRow.querySelector('input[name="amount[]"]');
                            const priceInput = firstRow.querySelector('input[name="price[]"]');

                            select.value = listId;
                            firstRow.querySelector('textarea').value = quality;
                            firstRow.querySelector('input[name="amount[]"]').value = amount;
                            firstRow.querySelector('input[name="price[]"]').value = price;
                            const sumInput = firstRow.querySelector('input.sum');

                            amountInput.value = amount;
                            priceInput.value = price;

                            sumInput.value = (parseFloat(amountInput.value) || 0) * (parseFloat(priceInput.value) || 0);
                            calculateTotal();
                        } else {
                            // Create a new row if no match is found
                            const newRow = document.createElement('tr');
                            newRow.className = 'text-center copied-row';
                            newRow.setAttribute('data-numberwork', numberWork);
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
                        <td><input disabled style="width: 5rem;" type="text" class="form-control sum" value="${amount*price}"></td>
                        <td><button type="button" class="btn btn-warning remove-row">ลบ</button></td>

                        `;
                            tableBody.appendChild(newRow);
                            updateRowIndexes();
                            calculateTotal();
                        }
                    }
                });
            }
        });

        // Remove row functionality
        document.getElementById('table-body').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.parentElement.parentElement.remove();
                rowIndex--; // Adjust rowIndex to ensure consistent indexing
                updateRowIndexes();
                calculateTotal();
            }
        });

        document.getElementById('table-body').addEventListener('input', function(e) {
            // Check if the event target is amount or price input4wwww3
            if (e.target.name === 'amount[]' || e.target.name === 'price[]') {
                const row = e.target.closest('tr'); // Find the parent row of the input
                const amountInput = row.querySelector('input[name="amount[]"]');
                const priceInput = row.querySelector('input[name="price[]"]');
                const sumInput = row.querySelector('input.sum');

                // Calculate and update the total
                const amount = parseFloat(amountInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                sumInput.value = (amount * price); // Keep two decimal places
                calculateTotal();
            }
        });

        $(document).ready(function() {
            $('#table-body').on('change', '.device-select', function() {
                var models_id = $(this).val();
                var row = $(this).data('row'); // Ensure `row` is taken from `data-row`, representing the correct row

                if (models_id) {
                    $.ajax({
                        url: 'system_1/autoList.php',
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

                                const tableRow = $(`#table-body tr:nth-child(${row})`);
                                tableRow.find('textarea[name="quality[]"]').val(data.quality);
                                tableRow.find('input[name="price[]"]').val(data.price);

                                // Trigger real-time calculation for the updated row
                                const amountInput = tableRow.find('input[name="amount[]"]');
                                const priceInput = tableRow.find('input[name="price[]"]');
                                const sumInput = tableRow.find('input.sum');

                                const amount = parseFloat(amountInput.val()) || 0;
                                const price = parseFloat(priceInput.val()) || 0;
                                sumInput.val((amount * price));
                                calculateTotal();
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
        updateRowIndexes();
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".confirm-status-switch").forEach((checkbox) => {
                checkbox.addEventListener("change", function() {
                    let numberWork = this.getAttribute("data-order-id");
                    let form = document.querySelector("form[action='system/insert.php']"); // Target the form

                    if (this.checked) {
                        // Create hidden input
                        let hiddenInput = document.createElement("input");
                        hiddenInput.type = "hidden";
                        hiddenInput.name = "update_status[]"; // Name for submission
                        hiddenInput.value = numberWork; // Store the numberWork ID
                        hiddenInput.id = "hidden-input-" + numberWork; // Unique ID
                        form.appendChild(hiddenInput);
                    } else {
                        // Remove hidden input if unchecked
                        let hiddenInput = document.getElementById("hidden-input-" + numberWork);
                        if (hiddenInput) {
                            form.removeChild(hiddenInput);
                        }
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



    <?php SC5() ?>
</body>

</html>