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

        .btn.dropdown-toggle {
            border-radius: 0 !important;
            /* Remove rounded corners */
            border-color: transparent !important;
            /* Ensure no border by default */
        }

        .btn.dropdown-toggle:focus,
        .btn.dropdown-toggle:active,
        .show>.btn.dropdown-toggle {
            border: none;
            border-bottom: 2px solid #ffc107 !important;
            /* Change border color */
            color: #ffc107 !important;
            /* Change text color */
        }

        .btn.dropdown-toggle:not(:focus):not(:active) {
            border-color: transparent !important;
            /* Remove border when losing focus */
            box-shadow: none !important;
            /* Remove Bootstrap's default focus glow */
        }
    </style>

    <nav class="navbar p-3 navbar-expand-lg bg-green text-center">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">ระบบบริหารงานซ่อม
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse align-items-center" id="navbarSupportedContent">



                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fff; margin-top: 1px">
                            Dashboard
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                            <li><a class="dropdown-item" href="dashboard.php">Dashboard</a></li>
                            <li><a class="dropdown-item" href="summary.php">สรุปผล</a></li>
                            <li><a class="dropdown-item" href="insertData.php">หลังบ้าน</a></li>
                        </ul>
                    </div>
                    <li class="nav-item">
                        <a class="nav-link" href="myjob.php">งานของฉัน</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="myjob2.php">สร้างงาน</a>
                    </li>
                    <!-- 
                    <li class="nav-item">
                        <a class="nav-link" href="create.php">สร้างใบเบิก</a>
                    </li> -->
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fff; margin-top: 1px">
                            ใบเบิก
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                            <li><a class="dropdown-item" href="check.php">ตรวจสอบใบเบิก</a></li>
                            <li><a class="dropdown-item" href="checkAll.php">สร้างใบเบิกประจำสัปดาห์ </a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fff; margin-top: 1px">
                            ครุภัณฑ์
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                            <li><a class="dropdown-item" href="../runNumber">รายการครุภัณฑ์</a></li>
                            <li><a class="dropdown-item" href="../runNumber/create.php">เพิ่มข้อมูลคอมพิวเตอร์ </a></li>
                            <li><a class="dropdown-item" href="../runNumber/createDevice.php">เพิ่มข้อมูลอุปกรณ์ทั่วไป</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownMenu2" data-bs-toggle="dropdown" aria-expanded="false" style="color: #fff; margin-top: 1px">
                            ยืมคืนอุปกรณ์
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu2">
                            <li><a class="dropdown-item" href="../repair/borrow.php">ยืม</a></li>
                            <li><a class="dropdown-item" href="../repair/admin.php">สำหรับเจ้าหน้าที่</a></li>
                        </ul>
                    </div>
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