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
            /* padding-top: 100px; */
            margin-top: -25px;
        }

        tr {
            height: 1.5pt;
            line-height: 18pt;
            page-break-inside: avoid;
        }

        p {
            line-height: 10pt;
            font-size: 18pt;
        }

        .breakhere {
            page-break-after: always;
        }



        table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            font-size: 18pt
        }

        td {
            height: 18.8pt;

        }

        .empty-row td {
            height: 18.8pt;
            /* กำหนดความสูงตามที่คุณต้องการ */
        }

        @page {
            size: 50mm 25mm;
            margin: 0;
            page-break-after: always;
        }

        footer {
            display: block;
        }

        @media print {

            html,
            body {
                width: 50mm;
                height: 25mm;
                padding-left: 1.3mm;
                padding-right: 1.3mm;
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
os.timestamp
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

                if (isset($row['status']) && $row['status'] == 5) {
                    $closeDate = $row['timestamp'];
                    break;
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

        // $sql = "SELECT od.*, dp.depart_name ,lw.work_name,dv.device_name,ad.fname,ad.lname,dw.withdraw_name
        // FROM orderdata AS od
        // INNER JOIN depart AS dp ON od.refDepart = dp.depart_id
        // INNER JOIN listwork AS lw ON od.refWork = lw.work_id
        // INNER JOIN device AS dv ON od.refDevice = dv.device_id
        // INNER JOIN withdraw AS dw ON od.refWithdraw = dw.withdraw_id
        // INNER JOIN admin AS ad ON od.refUsername = ad.username
        //  WHERE od.id = :id";
        // $stmt = $conn->prepare($sql);
        // $stmt->bindParam(":id", $id);
        // $stmt->execute();
        // $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // $originalDate = $data['closeDate'];
        // $date = new DateTime($originalDate);
        // $formattedDate = $date->format('d-m-Y');

        ?>
        <div class="d-flex justify-content-center">
            <h6><?= $closeDate ?></h6>
        </div>
        <div style="margin-top: -5px;" class="d-flex">
            <h3 style="font-size: 13pt;flex-grow: 1; text-align:left;"> <?= $order['numberWork'] ?></h3>
            <h3 style="font-size: 13pt;flex-grow: 1; text-align:right;"><?= $order['refUsername'] ?></h3>
        </div>
        <div class="d-flex justify-content-center">
            <h3 style="font-size: 10pt"><?= $order['device_name'] ?></h3>
        </div>
        <div class="d-flex justify-content-center">
            <h3 style="font-size: 8pt"><?= $order['depart_name'] ?></h3>
        </div>
    </div>





</body>

</html>