<?php
session_start();
require_once '../config/db.php';

function backToInsertPage($data = [])
{
    $mainPageId = $_GET["mainPage"];
    $pageId = $_GET["page"];
    $link = "location: ../insertData.php?mainPage=$mainPageId&page=$pageId";

    foreach ($data as $key => $value) {
        $link .= "&" . urldecode($key) . "=" . urldecode($value);
    }

    // echo $link;
    header($link);
    exit();
}

if (isset($_GET['device'])) {
    $id = $_GET['device'];
    $sql = "DELETE FROM device WHERE device_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}

if (isset($_GET['noteId'])) {
    $noteId = $_GET['noteId'];
    try {
        $stmt = $conn->prepare("UPDATE notelist SET is_deleted = 1 WHERE id = :id");
        $stmt->bindParam(":id", $noteId);
        if ($stmt->execute()) {
            $_SESSION["success"] = "ลบโน้ตเรียบร้อยแล้ว";
            header("Location: ../noteList.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION["error"] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: ../noteList.php");
        exit;
    }
}

if (isset($_GET['categoryId'])) {
    $categoryId = $_GET['categoryId'];
    try {
        $stmt = $conn->prepare("DELETE FROM notelist_category WHERE category_id = :id");
        $stmt->execute([':id' => $categoryId]);

        $stmt = $conn->prepare("DELETE FROM category_note WHERE id = :id");
        $stmt->execute([':id' => $categoryId]);

        $_SESSION["success"] = "ลบหมวดหมู่เรียบร้อยแล้ว";
        header("Location: ../noteList.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION["error"] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        header("Location: ../noteList.php");
        exit;
    }
}


if (isset($_GET['models'])) {
    $id = $_GET['models'];
    $sql = "DELETE FROM device_models WHERE models_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['work'])) {
    $id = $_GET['work'];
    $sql = "DELETE FROM listwork WHERE work_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['depart'])) {
    $id = $_GET['depart'];
    $sql = "DELETE FROM depart WHERE depart_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['withdraw'])) {
    $id = $_GET['withdraw'];
    $sql = "DELETE FROM withdraw WHERE withdraw_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['sla'])) {
    $id = $_GET['sla'];
    $sql = "DELETE FROM sla WHERE sla_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['kpi'])) {
    $id = $_GET['kpi'];
    $sql = "DELETE FROM kpi WHERE kpi_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['offer'])) {
    $id = $_GET['offer'];
    $sql = "DELETE FROM offer WHERE offer_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['deleteuser'])) {
    $id = $_GET['deleteuser'];
    $sql = "DELETE FROM admin WHERE username = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['working'])) {
    $id = $_GET['working'];
    $sql = "DELETE FROM workinglist WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
if (isset($_GET['problemL'])) {
    $id = $_GET['problemL'];
    $sql = "DELETE FROM problemlist WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "ลบข้อมูลสำเร็จ";
        backToInsertPage();
    } else {
        $_SESSION['error'] = "พบข้อผิดพลาด";
        header("location: ../insertData.php");
    }
}
