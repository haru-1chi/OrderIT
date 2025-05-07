<?php
// session_start(); รวมกับใบอื่นแล้ว
require_once 'config/db.php';
require_once 'template/navbar.php';

// if (!isset($_SESSION["admin_log"])) {
//     $_SESSION["warning"] = "กรุณาเข้าสู่ระบบ";
//     header("location: login.php");
// }
$id = $_GET['workid'];

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <title>เอกสารคณะกรรมการ</title>

    <!-- Behavioral Meta Data -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <?php bs5() ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'TH SarabunIT๙';
            src: url('THSarabunIT๙/THSarabunIT๙.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'TH SarabunIT๙', sans-serif;
            padding-top: 100px;
            margin-top: -80px;
            line-height: 1.5;



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
            size: A4;
            margin-top: 2em;
            padding-top: 2em;
            padding-left: 2em;
            padding-right: 2em;
            page-break-after: always;
        }

        footer {
            display: block;
        }

        @media print {

            html,
            body {
                width: 210mm;
                height: 297mm;
                padding-top: 2em;
                padding-left: 2em;
                padding-right: 2em;
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

        <div style="font-weight: bold; font-size:24pt; text-align:center;">เอกสารแสดงรายชื่อคณะกรรมการที่เกี่ยวกับการจัดซื้อจัดจ้าง
        </div>
        <br>
        <br>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">1. กรณีการจัดซื้อจัดจ้าง วงเงินไม่เกิน 100,000 บาท</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;ให้ระบุกรรมการตรวจรับ จำนวน 1 คน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">2. กรณีการจัดซื้อจัดจ้าง วงเงินเกิน 100,000 บาท ขึ้นไป ให้ระบุคณะกรรมการ ดังนี้</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;2.1 คณะกรรมการตรวจรับ จำนวน 3 คน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;2.2 คณะกรรมกำหนดราคากลางและคุณลักษณะเฉพาะ จำนวน 3 คน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;<u>หมายเหตุ</u> คณะกรรมการห้ามซ้ำกัน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">3. กรณีจัดซื้อจัดจ้าง วงเงินตั้งแต่ 500,000 บาท ขึ้นไป ให้ระบุคณะกรรมการ ดังนี้</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;3.1 คณะกรรมการตรวจรับ จำนวน 3 คน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;3.2 คณะกรรมกำหนดราคากลางและคุณลักษณะเฉพาะ จำนวน 3 คน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;3.3 คณะกรรมการพิจารณาผล จำนวน 3 คน</p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">&nbsp;&nbsp;&nbsp;&nbsp;<u>หมายเหตุ</u> คณะกรรมการพิจารณาผล,คณะกรรมกำหนดราคากลางและคุณลักษณะเฉพาะ ห้ามซ้ำกับ</p>

        <br>
        <br>
        <p style="font-size:18pt; text-align:left;"><u><b>คณะกรรมการกำหนดราคากลางและคุณลักษณะเฉพาะ</b></u>
        </p>
        <br>
        <br>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">1. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">2. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">3. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <br>
        <hr>
        <p style="font-size:18pt; text-align:left;"><u><b>คณะกรรมการพิจารณาผล</b></u>
        </p>
        <br>
        <br>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">1. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">2. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">3. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <br>
        <hr>
        <p style="font-size:18pt; text-align:left;"><u><b>คณะกรรมการตรวจรับพัสดุในงานซื้อหรืองานจ้าง</b></u>
        </p>
        <br>
        <br>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">1. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">2. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <p style="font-weight:normal; text-align: left; line-height: 10pt;">3. ชื่อสกุล....................................................................................ตำแหน่ง...............................................................
        </p>
        <br>
        <hr>
    </div>





</body>

</html>