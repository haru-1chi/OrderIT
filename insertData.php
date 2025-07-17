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

        <div class="row">
            <div class="col-sm-6"> <!-- ประเภทการเบิก -->
                <h1>ประเภทการเบิก</h1>
                <hr>
                <form action="system/insert.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="withdraw_name" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">ประเภทการเบิก</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addWithdraw" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM withdraw';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="withdraw" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#withdraw<?= $row['withdraw_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="withdraw<?= $row['withdraw_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['withdraw_name'] ?>" name="withdraw_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['withdraw_id'] ?>" name="withdraw_id" placeholder="Password">
                                                        <label for="floatingPassword">รายการอุปกรณ์</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="withdraw" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>


                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#w<?= $row['withdraw_id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?withdraw=<?= $row['withdraw_id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- ประเภทงาน -->
                <h1>ประเภทงาน</h1>
                <hr>
                <form action="system/insert.php" method="post">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="work_name" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">ประเภทงาน</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addListWork" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM listwork';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="work" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#work<?= $row['work_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="work<?= $row['work_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['work_name'] ?>" name="work_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['work_id'] ?>" name="work_id" placeholder="Password">
                                                        <label for="floatingPassword">รายการอุปกรณ์</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="work" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>

                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#t<?= $row['work_id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?work=<?= $row['work_id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- รายการอุปกรณ์ -->
                <h1>รายการซ่อม</h1>
                <hr>
                <form action="system/insert.php" method="post">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="device_name" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">รายการซ่อม</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addDevice" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM device';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="device" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#device<?= $row['device_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="device<?= $row['device_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['device_name'] ?>" name="device_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['device_id'] ?>" name="device_id" placeholder="Password">
                                                        <label for="floatingPassword">รายการอุปกรณ์</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="device" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>

                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dv<?= $row['device_id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?device=<?= $row['device_id'] ?>" class="btn btn-danger">ลบ</a>
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
            <div class="col-sm-6"> <!-- รายการอุปกรณ์ -->
                <h1>รายการเบิก</h1>
                <hr>
                <form action="system/insert.php" method="post">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="models_name" placeholder="รายการเบิก">
                                <label for="floatingPassword">รายการเบิก</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="quality" placeholder="คุณสมบัติ">
                                <label for="floatingPassword">คุณสมบัติ</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="price" placeholder="ราคา">
                                <label for="floatingPassword">ราคา</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" name="unit" placeholder="หน่วย">
                                <label for="floatingPassword">หน่วย</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addmodels" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM device_models';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="models" class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อรายการ</th>
                            <th scope="col">แก้ไข</th>
                            <th scope="col">ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $row) { ?>
                            <tr>
                                <th scope="row"><?= $row['models_name'] ?></th>
                                <td>
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#model<?= $row['models_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="model<?= $row['models_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">รายการอุปกรณ์</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
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
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="models" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>

                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#md<?= $row['models_id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?models=<?= $row['models_id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- หน่วยงาน -->
                <h1>หน่วยงาน</h1>
                <hr>
                <form action="system/insert.php" method="post">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="depart_name" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">หน่วยงาน</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addDepart" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM depart';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="depart" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#Depart<?= $row['depart_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="Depart<?= $row['depart_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">หน่วยงาน</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['depart_name'] ?>" name="depart_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['depart_id'] ?>" name="depart_id" placeholder="Password">
                                                        <label for="floatingPassword">หน่วยงาน</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="depart" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>


                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dp<?= $row['depart_id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?depart=<?= $row['depart_id'] ?>" class="btn btn-danger">ลบ</a>
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


            <div class="col-sm-6"> <!-- ร้านที่เสนอราคา -->
                <h1>ร้านค้า</h1>
                <hr>
                <form action="system/insert.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="offer_name" placeholder="Password">
                        <label for="floatingPassword">ร้านค้า</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addOffer" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM offer';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="offer" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#Offer<?= $row['offer_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="Offer<?= $row['offer_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ร้านที่เสนอราคา</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['offer_name'] ?>" name="offer_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['offer_id'] ?>" name="offer_id" placeholder="Password">
                                                        <label for="floatingPassword">ร้านที่เสนอราคา</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="offer" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <td>




                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#of<?= $row['offer_id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?offer=<?= $row['offer_id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- ร้านที่เสนอราคา -->
                <h1>รูปแบบการทำงาน</h1>
                <hr>
                <form action="system/insert.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="workingName" placeholder="Password">
                        <label for="floatingPassword">รูปแบบการทำงาน</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addWorkingName" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM workinglist';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="working" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#Offer<?= $row['id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="Offer<?= $row['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ร้านที่เสนอราคา</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['workingName'] ?>" name="workingName" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['id'] ?>" name="id" placeholder="Password">
                                                        <label for="floatingPassword">ร้านที่เสนอราคา</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="working" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <td>




                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#workingList<?= $row['id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?working=<?= $row['id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- ร้านที่เสนอราคา -->
                <h1>ปัญหาที่พบ</h1>
                <hr>
                <form action="system/insert.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="problemName" placeholder=" ">
                        <label for="floatingPassword">ปัญหาที่พบ</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="problemDetail" placeholder=" ">
                        <label for="floatingPassword">รายละเอียด</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addproblemName" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM problemlist';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="problem" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#problemT<?= $row['id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="problemT<?= $row['id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ปัญหาที่พบ</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['problemName'] ?>" name="problemName" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['id'] ?>" name="id" placeholder="Password">
                                                        <label for="floatingPassword">ปัญหาที่พบ</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['problemDetail'] ?>" name="problemDetail" placeholder="Password">
                                                        <label for="floatingPassword">รายละเอียด</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="problemL" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <td>




                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#problemLT<?= $row['id'] ?>">ลบ</a>

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
                                                    <a id="confirmDelete" href="system/delete.php?problemL=<?= $row['id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- ปัญหาใน SLA -->
                <h1>ปัญหาใน SLA</h1>
                <hr>
                <form action="system/insert.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="sla_name" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">ปัญหาใน SLA</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addSLA" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM sla';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="sla" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#sla<?= $row['sla_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="sla<?= $row['sla_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ปัญหาใน SLA</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['sla_name'] ?>" name="sla_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['sla_id'] ?>" name="sla_id" placeholder="Password">
                                                        <label for="floatingPassword">ปัญหาใน SLA</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="sla" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>


                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#w<?= $row['sla_id'] ?>">ลบ</a>

                                    <div class="modal fade" id="w<?= $row['sla_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
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
                                                    <a id="confirmDelete" href="system/delete.php?sla=<?= $row['sla_id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- ตัวชี้วัด -->
                <h1>ตัวชี้วัด</h1>
                <hr>
                <form action="system/insert.php" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" name="kpi_name" id="floatingPassword" placeholder="Password">
                        <label for="floatingPassword">ตัวชี้วัด</label>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addKPI" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM kpi';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="kpi" class="table table-hover">
                    <thead>
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
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#kpi<?= $row['kpi_id'] ?>">แก้ไข</button>
                                </td>

                                <div class="modal fade" id="kpi<?= $row['kpi_id'] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5" id="staticBackdropLabel">ตัวชี้วัด</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="system/update.php" method="post">
                                                    <div class="form-floating mb-3">
                                                        <input type="text" class="form-control" value="<?= $row['kpi_name'] ?>" name="kpi_name" placeholder="Password">
                                                        <input type="hidden" class="form-control" value="<?= $row['kpi_id'] ?>" name="kpi_id" placeholder="Password">
                                                        <label for="floatingPassword">ตัวชี้วัด</label>
                                                    </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="kpi" class="btn btn-primary">บันทึก</button>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <td>


                                    <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#w<?= $row['kpi_id'] ?>">ลบ</a>

                                    <div class="modal fade" id="w<?= $row['kpi_id'] ?>" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
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
                                                    <a id="confirmDelete" href="system/delete.php?kpi=<?= $row['kpi_id'] ?>" class="btn btn-danger">ลบ</a>
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

            <div class="col-sm-6"> <!-- มอบหมายตัวชี้วัด -->
                <h1>มอบหมายตัวชี้วัด</h1>
                <hr>
                <form action="system/insert.php" method="post">
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
                        <button type="submit" name="assignKPI" class="btn btn-primary">มอบหมายงาน</button>
                    </div>

                    <script>
                        function loadKpiUsers(kpiId) {
                            fetch('fetch_kpi_users.php?kpi=' + kpiId)
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

            <div class="col-sm-12">
                <h1>เพิ่มผู้ใช้งาน</h1>
                <hr>
                <form action="system/insert.php" method="post">
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
                    <div class="d-flex justify-content-center mb-3">
                        <button type="submit" name="addUsers" class="btn btn-primary">เพิ่มข้อมูล</button>
                    </div>
                </form>
                <?php
                $sql = 'SELECT * FROM admin';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <table id="admin" class="table table-hover">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อ</th>
                            <th scope="col">ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result as $row) {
                            if ($row['username'] != $admin) {
                        ?>
                                <tr>
                                    <td><?= $row['username'] ?></td>

                                    <td>

                                        <a href="#" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#user<?= $row['username'] ?>">ลบ</a>

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
                                                        <a id="confirmDelete" href="system/delete.php?deleteuser=<?= $row['username'] ?>" class="btn btn-danger">ลบ</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                        <?php }
                        } ?>
                    </tbody>
                </table>
                <hr>

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
</body>

</html>