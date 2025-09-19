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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <title>เพิ่มข้อมูล | IT ORDER PRO</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .active-main-page-button {
            background-color: #3191FF;
            padding-bottom: 0.5rem;
            padding-top: 0.5rem;
            padding: 0.50rem 2.5rem;
            color: white;
            border: none;
            border-top-left-radius: 0.40rem;
            border-top-right-radius: 0.40rem;
        }

        .default-main-page-button {
            background-color: #BABABA;
            padding-bottom: 0.5rem;
            padding-top: 0.5rem;
            padding: 0.50rem 2.5rem;
            color: white;
            border: none;
            border-top-left-radius: 0.40rem;
            border-top-right-radius: 0.40rem;
        }
    </style>

</head>

<body>

    <?php
    navbar();
    ?>

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

        <div class="row justify-content-center">
            <div class="p-0">
                <button class="default-main-page-button" data-selectMainPage="1" id="select-main-page-button">ใบงาน</button>
                <button class="default-main-page-button" data-selectMainPage="2" id="select-main-page-button">ใบเบิก</button>
                <button class="default-main-page-button" data-selectMainPage="3" id="select-main-page-button">ตัวชี้วัด</button>
                <button class="default-main-page-button" data-selectMainPage="4" id="select-main-page-button">เจ้าหน้าที่</button>
            </div>

            <div class="row justify-content-center shadow rounded-4" data-mainPageId="1" id="main-page">
                <div class="" id="page-column" data-pageId="1"> <!-- หน่วยงาน -->
                    <h1 class="pt-3">หน่วยงาน</h1>
                    <div>
                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option selected value="1" data-selectPageId="1">หน่วยงาน</option>
                                    <option value="2" data-selectPageId="2">รูปแบบการทำงาน</option>
                                    <option value="3" data-selectPageId="3">ปัญหาที่พบ</option>
                                    <option value="4" data-selectPageId="4">ปัญหาใน SLA</option>
                                    <option value="5" data-selectPageId="5">ตัวชี้วัด</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#หน่วยงาน">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="หน่วยงาน" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=5" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">หน่วยงาน</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">

                                        <input type="text" class="form-control" name="depart_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">หน่วยงาน</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addDepart" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                    $sql = 'SELECT * FROM depart';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="depart" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['depart_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#Depart<?= $row['depart_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="Depart<?= $row['depart_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="system/update.php" id="form-Depart<?= $row['depart_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">หน่วยงาน</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['depart_name'] ?>" name="depart_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['depart_id'] ?>" name="depart_id" placeholder="Password">
                                                            <label for="floatingPassword">หน่วยงาน</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" data-target="form-Depart<?= $row['depart_id'] ?>" id="update-form-submit" name="depart" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>
                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none; border: none;" data-bs-toggle="modal" data-bs-target="#dp<?= $row['depart_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="dp<?= $row['depart_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?depart=<?= $row['depart_id'] ?>&mainPage=1&page=1" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>

                        </tbody>
                    </table>
                    <hr>

                </div>
                <div class="" id="page-column" data-pageId="2"> <!-- รูปแบบการทำงาน -->
                    <h1 class="pt-3">รูปแบบการทำงาน</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1" data-selectPageId="1">หน่วยงาน</option>
                                    <option selected value="2" data-selectPageId="2">รูปแบบการทำงาน</option>
                                    <option value="3" data-selectPageId="3">ปัญหาที่พบ</option>
                                    <option value="4" data-selectPageId="4">ปัญหาใน SLA</option>
                                    <option value="5" data-selectPageId="5">ตัวชี้วัด</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#รูปแบบการทำงาน">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="รูปแบบการทำงาน" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=7" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">รูปแบบการทำงาน</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="workingName" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">รูปแบบการทำงาน</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addWorkingName" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM workinglist';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="working" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['workingName'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#Offer<?= $row['id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="Offer<?= $row['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="system/update.php" id="form-Offer<?= $row['id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">ร้านที่เสนอราคา</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['workingName'] ?>" name="workingName" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['id'] ?>" name="id" placeholder="Password">
                                                            <label for="floatingPassword">ร้านที่เสนอราคา</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" data-target="form-Offer<?= $row['id'] ?>" id="update-form-submit" name="working" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>

                                    <td>
                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-target="#workingList<?= $row['id'] ?>">ลบ</a>

                                        <div class="modal fade" id="workingList<?= $row['id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?working=<?= $row['id'] ?>&mainPage=1&page=2" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>

                        </tbody>
                    </table>
                    <hr>

                </div>
                <div class="" id="page-column" data-pageId="3"> <!-- ปัญหาที่พบ -->
                    <h1 class="pt-3">ปัญหาที่พบ</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1" data-selectPageId="1">หน่วยงาน</option>
                                    <option value="2" data-selectPageId="2">รูปแบบการทำงาน</option>
                                    <option selected value="3" data-selectPageId="3">ปัญหาที่พบ</option>
                                    <option value="4" data-selectPageId="4">ปัญหาใน SLA</option>
                                    <option value="5" data-selectPageId="5">ตัวชี้วัด</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#ปัญหาที่พบ">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="ปัญหาที่พบ" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=8" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">ปัญหาที่พบ</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="problemName" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">ปัญหาที่พบ</label>

                                    </div>
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="problemDetail" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">รายละเอียด</label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addproblemName" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM problemlist';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="problem" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>

                                <tr>
                                    <th scope="row"><?= $row['problemName'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#problemT<?= $row['id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="problemT<?= $row['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="system/update.php" id="form-problemT<?= $row['id'] ?>" method="post" class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">ปัญหาที่พบ</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div>
                                                            <div class="form-floating mb-3">
                                                                <input type="text" class="form-control" value="<?= $row['problemName'] ?>" name="problemName" placeholder="Password">

                                                                <input type="hidden" class="form-control" value="<?= $row['id'] ?>" name="id" placeholder="Password">
                                                                <label for="floatingPassword">ปัญหาที่พบ</label>
                                                            </div>

                                                            <div class="form-floating mb-3">
                                                                <input type="text" class="form-control" value="<?= $row['problemsDetail'] ?>" name="problemDetail" placeholder="Password">

                                                                <label for="floatingPassword">รายละเอียด</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" data-target="form-problemT<?= $row['id'] ?>" id="update-form-submit" name="problemL" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>

                                    <td>




                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#problemLT<?= $row['id'] ?>">ลบ</a>

                                        <div class="modal fade" id="problemLT<?= $row['id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?problemL=<?= $row['id'] ?>&mainPage=1&page=3" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>

                        </tbody>
                    </table>
                    <hr>

                </div>
                <div class="" id="page-column" data-pageId="4"> <!-- ปัญหาใน SLA -->
                    <h1 class="pt-3">ปัญหาใน SLA</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1" data-selectPageId="1">หน่วยงาน</option>
                                    <option value="2" data-selectPageId="2">รูปแบบการทำงาน</option>
                                    <option value="3" data-selectPageId="3">ปัญหาที่พบ</option>
                                    <option selected value="4" data-selectPageId="4">ปัญหาใน SLA</option>
                                    <option value="5" data-selectPageId="5">ตัวชี้วัด</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#SLAModal">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="SLAModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=9" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">ปัญหาใน SLA</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="sla_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">ปัญหาใน SLA</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addSLA" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM sla';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="sla" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['sla_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#sla-m<?= $row['sla_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="sla-m<?= $row['sla_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form action="system/update.php" id="form-sla<?= $row['sla_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">ปัญหาใน SLA</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['sla_name'] ?>" name="sla_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['sla_id'] ?>" name="sla_id" placeholder="Password">
                                                            <label for="floatingPassword">ปัญหาใน SLA</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" id="update-form-submit" data-target="form-sla<?= $row['sla_id'] ?>" name="sla" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>
                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-target="#sla<?= $row['sla_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="sla<?= $row['sla_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?sla=<?= $row['sla_id'] ?>&mainPage=1&page=4" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
                <div class="" id="page-column" data-pageId="5"> <!-- ตัวชี้วัด -->
                    <h1 class="pt-3">ตัวชี้วัด</h1>
                    <div>
                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1" data-selectPageId="1">หน่วยงาน</option>
                                    <option value="2" data-selectPageId="2">รูปแบบการทำงาน</option>
                                    <option value="3" data-selectPageId="3">ปัญหาที่พบ</option>
                                    <option value="4" data-selectPageId="4">ปัญหาใน SLA</option>
                                    <option selected value="5" data-selectPageId="5">ตัวชี้วัด</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#addKPI">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="addKPI" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=10" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">ตัวชี้วัด</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="kpi_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">ตัวชี้วัด</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addKPI" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM kpi';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="kpi" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['kpi_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#kpi<?= $row['kpi_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="kpi<?= $row['kpi_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form class="modal-dialog" action="system/update.php" id="form-kpi<?= $row['kpi_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">ตัวชี้วัด</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['kpi_name'] ?>" name="kpi_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['kpi_id'] ?>" name="kpi_id" placeholder="Password">
                                                            <label for="floatingPassword">ตัวชี้วัด</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" id="update-form-submit" data-target="form-kpi<?= $row['kpi_id'] ?>" name="kpi" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>
                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-target="#kpi-d<?= $row['kpi_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="kpi-d<?= $row['kpi_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?kpi=<?= $row['kpi_id'] ?>&mainPage=1&page=5" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
            </div>

            <div class="row justify-content-center shadow rounded-4" data-mainPageId="2" id="main-page">
                <div class="col-sm-12" id="page-column" data-pageId="1"> <!-- ประเภทการเบิก -->
                    <h1 class="pt-3">ประเภทการเบิก</h1>
                    <div>
                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option selected value="1">ประเภทการเบิก</option>
                                    <option value="2">ประเภทงาน</option>
                                    <option value="3">รายการเบิก</option>
                                    <option value="4">ร้านค้า</option>
                                    <option value="5">รายการอุปกรณ์</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#ประเภทการเบิก">เพิ่มข้อมูล</button>
                        </div>
                    </div>


                    <!-- Modal -->
                    <div class="modal fade" id="ประเภทการเบิก" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=1" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">ประเภทการเบิก</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="withdraw_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">ประเภทการเบิก</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addWithdraw" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM withdraw';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="withdraw" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['withdraw_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#withdraw<?= $row['withdraw_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="withdraw<?= $row['withdraw_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form class="" action="system/update.php" id="form-withdraw<?= $row['withdraw_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['withdraw_name'] ?>" name="withdraw_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['withdraw_id'] ?>" name="withdraw_id" placeholder="Password">
                                                            <label for="floatingPassword">รายการอุปกรณ์</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" data-target="form-withdraw<?= $row['withdraw_id'] ?>" id="update-form-submit" name="withdraw" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>


                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#w<?= $row['withdraw_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="w<?= $row['withdraw_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?withdraw=<?= $row['withdraw_id'] ?>&mainPage=2&page=1" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>
                        </tbody>
                    </table>
                    <hr>
                </div>

                <div class="" id="page-column" data-pageId="2"> <!-- ประเภทงาน -->
                    <h1 class="pt-3">ประเภทงาน</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1">ประเภทการเบิก</option>
                                    <option selected value="2">ประเภทงาน</option>
                                    <option value="3">รายการเบิก</option>
                                    <option value="4">ร้านค้า</option>
                                    <option value="5">รายการอุปกรณ์</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#ประเภทงาน">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="ประเภทงาน" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=2" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">ประเภทงาน</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="work_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">ประเภทงาน</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addListWork" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM listwork';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="work" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['work_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#work<?= $row['work_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="work<?= $row['work_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form class="" action="system/update.php" id="form-work<?= $row['work_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['work_name'] ?>" name="work_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['work_id'] ?>" name="work_id" placeholder="Password">
                                                            <label for="floatingPassword">รายการอุปกรณ์</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" id="update-form-submit" data-target="form-work<?= $row['work_id'] ?>" name="work" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>

                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#t<?= $row['work_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="t<?= $row['work_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?work=<?= $row['work_id'] ?>&mainPage=2&page=2" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>



                            <?php  } ?>
                        </tbody>
                    </table>
                    <hr>
                </div>
                <div class="" id="page-column" data-pageId="3"> <!-- รายการเบิก -->
                    <h1 class="pt-3">รายการเบิก</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1">ประเภทการเบิก</option>
                                    <option value="2">ประเภทงาน</option>
                                    <option selected value="3">รายการเบิก</option>
                                    <option value="4">ร้านค้า</option>
                                    <option value="5">รายการอุปกรณ์</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#รายการเบิก">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="รายการเบิก" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=4" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">รายการเบิก</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" name="models_name" placeholder="รายการเบิก">
                                                <label for="floatingPassword">รายการเบิก</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" name="quality" placeholder="คุณสมบัติ">
                                                <label for="floatingPassword">คุณสมบัติ</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" name="price" placeholder="ราคา">
                                                <label for="floatingPassword">ราคา</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control" name="unit" placeholder="หน่วย">
                                                <label for="floatingPassword">หน่วย</label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-floating mb-3">
                                                <select name="auto_close" class="form-select form-select-md px-12">
                                                    <option selected value="0">ไม่ใช่</option>
                                                    <option value="1">ใช่</option>
                                                </select>
                                                <label for="floatingPassword">ปิดงานใบเบิกเมื่อมี</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addmodels" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                    $sql = 'SELECT * FROM device_models';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="models" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">คุณสมบัติ</th>
                                <th scope="col">ราคา</th>
                                <th scope="col">หน่วย</th>
                                <th scope="col">ปิดงานอัตโนมัติ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['models_name'] ?></th>
                                    <td scope="row"><?= $row['quality'] ?></td>
                                    <td scope="row"><?= $row['price'] ?></td>
                                    <td scope="row"><?= $row['unit'] ?></td>
                                    <td scope="row">
                                        <?php if ($row['auto_close'] == 1): ?>
                                            <div class="w-50 bg-warning rounded-2 text-white text-center">
                                                ใช่
                                            </div>
                                        <?php else: ?>
                                            <div class="w-50 bg-secondary rounded-2 text-white text-center">
                                                ไม่ใช่
                                            </div>
                                        <?php endif; ?>

                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#model<?= $row['models_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="model<?= $row['models_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form class="modal-dialog" action="system/update.php" id="form-model<?= $row['models_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">

                                                            <input type="text" class="form-control" value="<?= $row['models_name'] ?>" name="models_name" placeholder="Password">
                                                            <label for="floatingPassword">รายการอุปกรณ์</label>

                                                            <input type="hidden" class="form-control" value="<?= $row['models_id'] ?>" name="models_id" placeholder="Password">
                                                        </div>
                                                        <div class="form-floating mb-3">

                                                            <input type="text" class="form-control" value="<?= $row['quality'] ?>" name="quality" placeholder="Password">
                                                            <label for="floatingPassword">คุณสมบัติ</label>


                                                        </div>
                                                        <div class="form-floating mb-3">

                                                            <input type="text" class="form-control" value="<?= $row['price'] ?>" name="price" placeholder="Password">
                                                            <label for="floatingPassword">ราคา</label>


                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['unit'] ?>" name="unit" placeholder="Password">
                                                            <label for="floatingPassword">หน่วย</label>
                                                        </div>
                                                        <div class="form-floating mb-3">
                                                            <select id="auto_close" name="auto_close" class="form-select form-select-md">
                                                                <option value="0" <?= $row['auto_close'] == 0 ? 'selected' : '' ?>>ไม่ใช่</option>
                                                                <option value="1" <?= $row['auto_close'] == 1 ? 'selected' : '' ?>>ใช่</option>
                                                            </select>
                                                            <label for="auto_close">ปิดงานใบเบิกเมื่อมี</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" name="models" id="update-form-submit" data-target="form-model<?= $row['models_id'] ?>" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>

                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#md<?= $row['models_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="md<?= $row['models_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?models=<?= $row['models_id'] ?>&mainPage=2&page=3" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>
                        </tbody>
                    </table>
                    <hr>

                </div>
                <div class="" id="page-column" data-pageId="4"> <!-- ร้านค้า -->
                    <h1 class="pt-3">ร้านค้า</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1">ประเภทการเบิก</option>
                                    <option value="2">ประเภทงาน</option>
                                    <option value="3">รายการเบิก</option>
                                    <option selected value="4">ร้านค้า</option>
                                    <option value="5">รายการอุปกรณ์</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#ร้านค้า">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="ร้านค้า" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=6" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">ร้านค้า</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="offer_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">ร้านค้า</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addOffer" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                    $sql = 'SELECT * FROM offer';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="offer" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['offer_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#Offer<?= $row['offer_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="Offer<?= $row['offer_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form class="modal-dialog" action="system/update.php" id="form-Offer<?= $row['offer_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">ร้านที่เสนอราคา</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['offer_name'] ?>" name="offer_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['offer_id'] ?>" name="offer_id" placeholder="Password">
                                                            <label for="floatingPassword">ร้านที่เสนอราคา</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" id="update-form-submit" data-target="form-Offer<?= $row['offer_id'] ?>" name="offer" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>

                                    <td>




                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-target="#of<?= $row['offer_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="of<?= $row['offer_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?offer=<?= $row['offer_id'] ?>&mainPage=2&page=4" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>

                        </tbody>
                    </table>
                    <hr>

                </div>

                <div class="" id="page-column" data-pageId="5"> <!-- รายการอุปกรณ์ -->
                    <h1 class="pt-3">รายการอุปกรณ์</h1>
                    <div>

                        <div class="d-flex justify-content-between my-2">
                            <div class="">
                                <select name="" class="form-select form-select-md px-12" id="select-page-button">
                                    <option value="1">ประเภทการเบิก</option>
                                    <option value="2">ประเภทงาน</option>
                                    <option value="3">รายการเบิก</option>
                                    <option value="4">ร้านค้า</option>
                                    <option selected value="5">รายการอุปกรณ์</option>
                                </select>
                            </div>


                            <button type="button" class="btn btn-primary border-0 px-4 py-2" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#รายการอุปกรณ์">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="รายการอุปกรณ์" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=3" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">รายการอุปกรณ์</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" name="device_name" id="floatingPassword" placeholder="Password">
                                        <label for="floatingPassword">รายการอุปกรณ์</label>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addDevice" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>


                    <?php
                    $sql = 'SELECT * FROM device';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="device" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อรายการ</th>
                                <th scope="col">แก้ไข</th>
                                <th scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) { ?>
                                <tr>
                                    <th scope="row"><?= $row['device_name'] ?></th>
                                    <td>
                                        <button type="button" class="btn btn-warning text-white" style="background-color: #FFAE2C;" data-bs-toggle="modal" data-bs-target="#device<?= $row['device_id'] ?>">แก้ไข</button>
                                    </td>

                                    <div class="modal fade" id="device<?= $row['device_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form class="modal-dialog" action="system/update.php" id="form-device<?= $row['device_id'] ?>" method="post">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">

                                                        <div class="form-floating mb-3">
                                                            <input type="text" class="form-control" value="<?= $row['device_name'] ?>" name="device_name" placeholder="Password">
                                                            <input type="hidden" class="form-control" value="<?= $row['device_id'] ?>" name="device_id" placeholder="Password">
                                                            <label for="floatingPassword">รายการอุปกรณ์</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" id="update-form-submit" data-target="form-device<?= $row['device_id'] ?>" name="device" class="btn btn-primary">บันทึก</button>

                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>

                                    </div>
                                    <td>

                                        <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-target="#dv<?= $row['device_id'] ?>">ลบ</a>

                                        <div class="modal fade" id="dv<?= $row['device_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?device=<?= $row['device_id'] ?>&mainPage=2&page=5" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php  } ?>
                        </tbody>
                    </table>
                    <hr>

                </div>
            </div>

            <div class="row justify-content-center shadow rounded-4" data-mainPageId="3" id="main-page">
                <div class="col-sm-6" id="page-column" data-pageId="1"> <!-- มอบหมายตัวชี้วัด -->
                    <h1 class="pt-3">มอบหมายตัวชี้วัด</h1>

                    <form action="system/insert.php" method="post" id="page-form" class="modal-content">
                        <select class="form-select" name="kpi" aria-label="Default select example">
                            <?php
                            $sql = "SELECT * FROM kpi";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            $selectedKpiId = $_GET['kpi'] ?? $_POST['kpi'] ?? null;

                            foreach ($checkD as $d) {
                                if ($d['kpi_id'] == 1) continue;
                            ?>
                                <option value="<?= $d['kpi_id'] ?>" <?= ($d['kpi_id'] == $selectedKpiId) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['kpi_name']) ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>


                        <div class="list-group mt-3" id="checkbox-container">
                        </div>

                        <div class="d-flex justify-content-center my-3">
                            <button type="submit" id="form-submit" name="assignKPI" class="btn btn-primary w-100">มอบหมายงาน</button>
                        </div>

                        <script>
                            function loadKpiUsers(kpiId) {
                                fetch('system_1/fetch_kpi_users.php?kpi=' + kpiId)
                                    .then(response => response.json())
                                    .then(data => {
                                        document.getElementById('checkbox-container').innerHTML = data.html;
                                    })
                                    .catch(err => {
                                        console.error('Error fetching KPI data:', err);
                                    });
                            }

                            document.querySelector('select[name="kpi"]').addEventListener('change', function() {
                                loadKpiUsers(this.value);
                            });

                            window.addEventListener('DOMContentLoaded', function() {
                                const kpiSelect = document.querySelector('select[name="kpi"]');
                                if (kpiSelect && kpiSelect.value) {
                                    loadKpiUsers(kpiSelect.value); // Fetch checkboxes on initial load
                                }
                            });
                        </script>


                    </form>
                    <hr>
                </div>
            </div>

            <div class="row justify-content-center shadow rounded-4" data-mainPageId="4" id="main-page">
                <div class="col-sm-12" id="page-column" data-pageId="1"> <!-- เพิ่มผู้ใช้งาน -->


                    <div class="my-3 d-flex justify-content-between h-25">
                        <h1 class="d-flex">เพิ่มผู้ใช้งาน</h1>

                        <div class="d-flex text-center">
                            <button type="button" class="btn btn-primary border-0 px-4" style="background-color: #4ACA84;" data-bs-toggle="modal" data-bs-target="#addUsers">เพิ่มข้อมูล</button>
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="addUsers" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="post" action="system/insert.php?page=11" id="page-form" class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">เพิ่มผู้ใช้งาน</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-floating mb-3">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text">ชื่อผู้ใช้</span>
                                            <input type="text" class="form-control" placeholder="ชื่อผู้ใช้" name="username" aria-label="Username">
                                            <span class="input-group-text">รหัสผ่าน</span>
                                            <input type="password" class="form-control" placeholder="รหัสผ่าน" name="password" aria-label="Server">
                                        </div>
                                        <div class="input-group mb-3">

                                            <span class="input-group-text">ชื่อจริง</span>
                                            <input type="text" class="form-control" placeholder="ชื่อจริง" name="fname" aria-label="Username">
                                            <span class="input-group-text">นามสกุล</span>
                                            <input type="text" class="form-control" placeholder="นามสกุล" name="lname" aria-label="Server">
                                        </div>

                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" id="form-submit" name="addUsers" class="btn btn-primary">เพิ่มข้อมูล</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                    $sql = 'SELECT * FROM admin';
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <table id="admin" class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">ชื่อ</th>
                                <th class="d-flex justify-content-end " scope="col">ลบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row) {
                                if ($row['username'] != $admin) {
                            ?>
                                    <tr>
                                        <td><?= $row['username'] ?></td>

                                        <td class="justify-content-end d-flex">

                                            <a href="#" class="btn btn-danger" style="background-color: #FF5757; border: none;" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#user<?= $row['username'] ?>">ลบ</a>


                                        </td>
                                        <div class="modal fade" id="user<?= $row['username'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="confirmationModalLabel">ยืนยันการลบ</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        คุณแน่ใจหรือไม่ที่ต้องการลบรายการนี้?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                        <a id="confirmDelete" href="system/delete.php?deleteuser=<?= $row['username'] ?>&mainPage=4&page=1" class="btn btn-danger" style="background-color: #FF5757; border: none;">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </tr>
                            <?php }
                            } ?>
                        </tbody>
                    </table>
                    <hr>

                </div>
            </div>

        </div>


    </div>

    <script>
        function confirmDelete(message) {
            var confirmResult = confirm(message);

            if (confirmResult) {
                // ทำการลบ
                return true;
            } else {
                // ไม่ทำการลบ
                return false;
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#withdraw').DataTable();
            $('#work').DataTable();
            $('#models').DataTable();
            $('#device').DataTable();
            $('#depart').DataTable();
            $('#offer').DataTable();
            $('#admin').DataTable();
            $('#working').DataTable();
            $('#problem').DataTable();


        });
    </script>



    <?php SC5() ?>
    <script src="Client/InsertDataPage/Main.js" type="module"></script>

</body>

</html>