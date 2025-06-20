<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';

if (!isset($_SESSION["admin_log"])) {
    $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
    header("location: login.php");
}
$id = $_GET['workid'];

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-10" />

    <title>พิมพ์สติ๊กเกอร์</title>

    <!-- Behavioral Meta Data -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?php bs5() ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            margin-top: -25px;
        }

        tr {
            height: 1.5pt;
            line-height: 8pt;
            page-break-inside: avoid;
        }

        p {
            line-height: 10pt;
            font-size: 8pt;
        }

        .breakhere {
            page-break-after: always;
        }

        .row {
            font-size: 8pt;
        }

        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 8pt;
        }

        td {
            height: 1pt;

        }

        .empty-row td {
            height: 1pt;
            /* กำหนดความสูงตามที่คุณต้องการ */
        }

        .col-4,
        .col-8 {
            overflow: visible;
            white-space: nowrap;
            text-overflow: clip;
        }

        @page {
            size: 100mm 75mm;
            margin: 0;
            page-break-after: always;
        }

        footer {
            display: block;
        }

        @media print {

            html,
            body {
                width: 100mm;
                height: 75mm;
                padding: 1.3mm;
                page-break-after: always;
            }

            /* ... the rest of the rules ... */
        }
    </style>

    <!-- กำหนดฟังก์ชันแปลงเลขไทย -->
    <script>
        function convertToThaiNumber(arabicNumber) {
            const thaiNumbers = ['๐', '๑', '๒', '๓', '๔', '๕', '๖', '๗', '๘', '๙'];
            const arabicNumbers = arabicNumber.toString().split('');

            const thaiResult = arabicNumbers.map(digit => {
                if (digit === '.') {
                    return '.';
                } else {
                    return thaiNumbers[parseInt(digit)];
                }
            });

            return thaiResult.join('');
        }
    </script>




<body onload="window.print()">
    <div class="breakhere">
        <br />
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
oi.unit,
os.status,
os.timestamp,
dr.reporter,
dr.tel
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
order_status AS os ON od.id = oi.order_id
LEFT JOIN 
device_models AS dm ON oi.list = dm.models_id
LEFT JOIN 
data_report AS dr ON od.id_ref = dr.id
WHERE 
(od.numberWork = :numberWork) AND
(nd.is_deleted = 0 OR nd.is_deleted IS NULL)
AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
ORDER BY nd.id, oi.id
";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":numberWork", $id);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            $order = $results[0];
            $devices = [];
            $items = [];

            $columnCount = 0;
            $sum = 0;
            $closeDate = null;

            foreach ($results as $row) {
                if (!empty($row['numberDevice_id']) && !isset($devices[$row['numberDevice_id']])) {
                    $devices[$row['numberDevice_id']] =
                        [
                            'numberDevice_id' => $row['numberDevice_id'],
                            'numberDevice' => $row['numberDevice']
                        ];
                }

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

                    $columnCount++;
                    $sum += $row['price'] * $row['amount'];
                }

                if (isset($row['status']) && $row['status'] == 5 && !$closeDate) {
                    $closeDate = $row['timestamp'];
                }
            }

            $devices = array_values($devices);
            $items = array_values($items);

            $numberDeviceList = array_column($devices, 'numberDevice');
            $numberDeviceString = implode(', ', $numberDeviceList);

            if ($closeDate) {
                $dateTime = new DateTime($closeDate);

                $day = $dateTime->format('d');
                $month = $dateTime->format('m');
                $year = (int)$dateTime->format('Y');

                $formattedCloseDate = "{$day}-{$month}-{$year}";

                $closeDate = $formattedCloseDate;
            } else {
                $closeDate = 'ไม่มีวันปิดงาน';
            }
        } else {
            echo "No records found.";
            $order = [];
            $devices = [];
            $items = [];
        }
        ?>

        <div class="row gx-0">
            <div class="col">
                <div class="row row-cols-2 gx-0">
                    <div class="col-4">เลขใบเบิก</div>
                    <div class="col-7" style="transform: translateX(-5px);"><?= $order['numberWork'] ?></div>
                    <div class="col-4">เลขงาน</div>
                    <div class="col-7" style="transform: translateX(-5px);"><?= $order['id_ref'] ?? '' ?></div>
                    <div class="col-4">ครุภัณฑ์</div>
                    <div class="col-7" style="transform: translateX(-5px);"><?= $devices[0]['numberDevice'] ?? '' ?></div>
                    <div class="col-4"></div>
                    <div class="col-7" style="transform: translateX(-5px);"><?= $devices[1]['numberDevice'] ?? '' ?></div>
                    <div class="col-4"></div>
                    <div class="col-7" style="transform: translateX(-5px);"><?= $devices[2]['numberDevice'] ?? '' ?></div>
                </div>
            </div>
            <div class="col">
                <div class="row row-cols-2 gx-0" style="transform: translateX(-15px);">
                    <div class="col-4">หน่วยงาน</div>
                    <div class="col-8" style="transform: translateX(-5px);"><?= $order['depart_name'] ?></div>
                    <div class="col-4">ผู้แจ้ง</div>
                    <div class="col-8" style="transform: translateX(-5px);"><?= $order['reporter'] ?? '' ?></div>
                    <div class="col-4">เบอร์โทร</div>
                    <div class="col-8" style="transform: translateX(-5px);"><?= $order['tel'] ?? '' ?></div>
                    <div class="col-4">ผูู้ซ่อม</div>
                    <div class="col-8" style="transform: translateX(-5px);"><?= $order['refUsername'] ?></div>
                    <div class="col-4">ทั้งหมด</div>
                    <div class="col-8" style="transform: translateX(-5px);"><?= count($items) ?> รายการ</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table style="width:100%;" cellspacing="0">
                <thead>
                    <tr>
                        <th style="text-align:center;">ลำดับ</th>
                        <th style="text-align:center;">รายการ</th>
                        <th style="text-align:center;">จำนวน</th>
                    </tr>
                </thead>
                <tbody class="text-center text-start">
                    <?php
                    $rowCount = count($items); // Get the current number of items
                    $emptyRows = 10 - $rowCount; // Calculate how many empty rows to add
                    $index = 0; // Initialize index to start from 1

                    // Loop through the existing items
                    foreach ($items as $item): ?>
                        <tr class="empty-row">
                            <td style="font-weight: normal; vertical-align: top;"><?= $index + 1 ?></td>
                            <td style="font-weight: normal; text-align:left; vertical-align: top;"><?= $item['list'] ?></td>
                            <td style="font-weight: normal; vertical-align: top;" class="arabicNumber"><?= $item['amount'] ?></td>
                        </tr>
                    <?php
                        $index++;
                    endforeach;
                    for ($i = 0; $i < $emptyRows; $i++): ?>
                        <tr class="empty-row">
                            <td style="font-weight: normal;" class="arabicNumber"><?= $index + 1 ?></td>
                            <td style="font-weight: normal;" class="arabicNumber"></td>
                            <td style="font-weight: normal;" class="arabicNumber"></td>
                        </tr>
                    <?php
                        $index++; // Increment index for each empty row
                    endfor;
                    ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($order['note']) && $order['note'] != '-'): ?>
            <div style="margin-top: 5px;">
                <p style="
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        word-break: break-word;
    ">
                    *หมายเหตุ <?= $order['note'] ?>
                </p>
            </div>
        <?php endif; ?>




</body>

</html>