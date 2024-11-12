<?php
function navbar()
{ ?>
    <style>
        /* เพิ่มสไตล์ CSS เพื่อปรับแต่ง Navbar */
        .navbar {
            background-color: #365486;
            /* สีเขียว */
        }

        .navbar-brand {
            font-weight: 900;
            color: #fff !important;
            /* สีข้อความของ Navbar Brand */
        }

        .navbar-toggler-icon {
            background-color: #fff;
            /* สีไอคอน Toggle */
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            transition: border 0.3s;
            /* เพิ่ม transition เพื่อทำให้การเปลี่ยนสีเป็นจุดประสงค์ */
        }

        .navbar-nav .nav-link:hover {
            border-bottom: 2px solid #ffc107;
            /* สีกรอบเมื่อ Hover */
            color: #ffc107 !important;
        }
    </style>

    <nav class="navbar p-3 navbar-expand-lg bg-green text-center">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">ระบบบริหารงานซ่อม
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="myjob.php">งานของฉัน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="myjob2.php">สร้างงาน</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="create.php">สร้างใบเบิก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="checkAll.php">สร้างใบเบิกประจำสัปดาห์</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="check.php">ตรวจสอบใบเบิก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="insertData.php">หลังบ้าน</a>
                    </li>
                    <li class="nav-item ms-5">
                        <a class="nav-link" href="system/logout.php">ออกจากระบบ</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<?php }
function bs5()
{ ?>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
<?php }
function SC5()
{ ?>
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
<?php }
?>