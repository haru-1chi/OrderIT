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
}

print_r($_GET);

if (isset($_POST['update_note'])) {
    // UPDATE logic
    $noteId = $_POST['note_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $pined = isset($_POST['pined']) ? 1 : 0;

    try {
        $sql = "UPDATE notelist SET title = :title, description = :description, pined = :pined WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":title", $title);
        $stmt->bindParam(":description", $description);
        $stmt->bindParam(":pined", $pined);
        $stmt->bindParam(":id", $noteId);

        if ($stmt->execute()) {
            $_SESSION["success"] = "แก้ไขข้อมูลสำเร็จ";
            header("location: ../noteList.php");
            exit();
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

if (isset($_POST['withdraw'])) {
    $withdraw_name = $_POST['withdraw_name'];
    $withdraw_id = $_POST['withdraw_id'];
    try {
        $sql = "SELECT * FROM withdraw WHERE withdraw_name = :withdraw_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":withdraw_name", $withdraw_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['withdraw_name'] == $withdraw_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE withdraw SET withdraw_name = :withdraw_name WHERE withdraw_id = :withdraw_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":withdraw_id", $withdraw_id);
            $stmt->bindParam(":withdraw_name", $withdraw_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['work'])) {
    $work_name = $_POST['work_name'];
    $work_id = $_POST['work_id'];
    try {
        $sql = "SELECT * FROM listwork WHERE work_name = :work_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":work_name", $work_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['work_name'] == $work_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE listwork SET work_name = :work_name WHERE work_id = :work_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":work_id", $work_id);
            $stmt->bindParam(":work_name", $work_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['device'])) {
    $device_name = $_POST['device_name'];
    $device_id = $_POST['device_id'];
    try {
        $sql = "SELECT * FROM device WHERE device_name = :device_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":device_name", $device_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['device_name'] == $device_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE device SET device_name = :device_name WHERE device_id = :device_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":device_id", $device_id);
            $stmt->bindParam(":device_name", $device_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['models'])) {
    $models_name = $_POST['models_name'];
    $models_id = $_POST['models_id'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    try {

        if (!isset($_SESSION['error'])) {
            $sql = "UPDATE device_models SET models_name = :models_name , quality = :quality , price = :price , unit = :unit WHERE models_id = :models_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":models_id", $models_id);
            $stmt->bindParam(":models_name", $models_name);
            $stmt->bindParam(":quality", $quality);
            $stmt->bindParam(":price", $price);
            $stmt->bindParam(":unit", $unit);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['depart'])) {
    $depart_name = $_POST['depart_name'];
    $depart_id = $_POST['depart_id'];
    try {
        $sql = "SELECT * FROM depart WHERE depart_name = :depart_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":depart_name", $depart_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['depart_name'] == $depart_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE depart SET depart_name = :depart_name WHERE depart_id = :depart_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":depart_id", $depart_id);
            $stmt->bindParam(":depart_name", $depart_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['offer'])) {
    $offer_name = $_POST['offer_name'];
    $offer_id = $_POST['offer_id'];
    try {
        $sql = "SELECT * FROM offer WHERE offer_name = :offer_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":offer_name", $offer_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['offer_name'] == $offer_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE offer SET offer_name = :offer_name WHERE offer_id = :offer_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":offer_id", $offer_id);
            $stmt->bindParam(":offer_name", $offer_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['working'])) {
    $workingName = $_POST['workingName'];
    $id = $_POST['id'];
    try {
        $sql = "SELECT * FROM workinglist WHERE workingName = :workingName";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":workingName", $workingName);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['workingName'] == $workingName) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE workinglist SET workingName = :workingName WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":workingName", $workingName);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['problemL'])) {
    $problemName = $_POST['problemName'];
    $problemDetail = $_POST['problemDetail'];
    $id = $_POST['id'];

    function isProblemNameChanged()
    {
        global $conn, $problemName, $id;

        try {
   

            $statement = $conn->prepare("SELECT * FROM  problemlist WHERE id = :id");
            $statement->bindParam(":id", $id);
            $statement->execute();
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if ($statement->rowCount() === 0) {
                
                return false;
            }
        
            if ($data["problemName"] !== $problemName) {
       
                return true;
            }

       

            return false;
        } catch (PDOException $error) {
            echo $error->getMessage();
            return false;
        }
    }

    try {
        $isChanged = isProblemNameChanged();
     
        echo $isChanged;
    
        if ($isChanged) {
            $sql = "SELECT * FROM problemlist WHERE problemName = :problemName";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":problemName", $problemName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                if ($result['problemName'] == $problemName) {
                    $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                    backToInsertPage();
                }
            }
        }

        if (!isset($_SESSION['error'])) {
            $sql = "UPDATE problemlist SET problemName = :problemName, problemsDetail = :problemsDetail  WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":problemName", $problemName);
            $stmt->bindParam(":problemsDetail", $problemDetail);
            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['updateData'])) {
    $id = $_POST['id'];
    $numberWork = $_POST['numberWork'];
    $report = $_POST['report'];
    $reason = $_POST['reason'];
    $note = $_POST['note'];
    $refWork = $_POST["refWork"];
    $refWithdraw = $_POST["refWithdraw"];
    $refOffer = $_POST["refOffer"];
    $refDevice = $_POST["refDevice"];
    $quotation = $_POST["quotation"];
    $id_ref = $_POST["id_ref"];

    $numberDevices = $_POST["device_numbers"];
    $update_number_device = $_POST["update_number_device"];
    $deleted_devices = $_POST["deleted_devices"];

    $lists = $_POST['list'];
    $qualities = $_POST['quality'];
    $amounts = $_POST['amount'];
    $prices = $_POST['price'];
    $units = $_POST['unit'];

    $update_lists = $_POST['update_list'];
    $update_qualities = $_POST['update_quality'];
    $update_amounts = $_POST['update_amount'];
    $update_prices = $_POST['update_price'];
    $update_units = $_POST['update_unit'];

    $deleted_items = $_POST['deleted_items'];

    $editedBy = $_SESSION['admin_log'] ?? 'unknown';
    // echo '<pre>';
    // var_dump([
    //     'numberWork' => $numberWork,
    //     'report' => $report,
    //     'reason' => $reason,
    //     'note' => $note,

    //     'numberDevices' => $numberDevices,
    //     'update_number_device' => $update_number_device,
    //     'deleted_devices' => $deleted_devices,

    //     'lists' => $lists,
    //     'qualities' => $qualities,
    //     'amounts' => $amounts,
    //     'prices' => $prices,
    //     'units' => $units,

    //     'update_lists' => $update_lists,
    //     'update_qualities' => $update_qualities,
    //     'update_amounts' => $update_amounts,
    //     'update_prices' => $update_prices,
    //     'update_units' => $update_units,

    //     'deleted_items' => $deleted_items,
    // ]);
    // echo '</pre>';
    // exit();
    // สร้าง SQL UPDATE statement

    $sqlOld = "SELECT * FROM orderdata_new WHERE id = :id";
    $stmtOld = $conn->prepare($sqlOld);
    $stmtOld->bindParam(":id", $id);
    $stmtOld->execute();
    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

    // Step 2: Loop over all fields in oldData
    foreach ($oldData as $field => $oldValue) {
        // Skip the primary key field (e.g., "id")
        if ($field === "id") continue;

        // Check if a new value was submitted for this field
        if (isset($_POST[$field])) {
            $newValue = $_POST[$field];

            // Only insert into history if the value actually changed
            if ($oldValue != $newValue) {
                $stmtHistory = $conn->prepare("
                    INSERT INTO order_history (
                        table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by
                    ) VALUES (
                        :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by
                    )
                ");
                $stmtHistory->execute([
                    ':table_name' => 'orderdata_new',
                    ':table_id' => $id,
                    ':order_id'   => $id,
                    ':field_name' => $field,
                    ':old_value'  => $oldValue,
                    ':new_value'  => $newValue,
                    ':action' => 'update',
                    ':edited_by'  => $editedBy // Define $editedBy earlier (e.g., from session)
                ]);
            }
        }
    }

    $sql = "UPDATE orderdata_new SET
            report = :report,
            reason = :reason,
            note = :note,
            refWork = :refWork,
            refDevice = :refDevice,
            refWithdraw = :refWithdraw,
            refOffer = :refOffer,
            quotation = :quotation,
            id_ref = :id_ref
            WHERE id = :id";

    // เตรียมและ execute statement

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":report", $report);
    $stmt->bindParam(":reason", $reason);
    $stmt->bindParam(":note", $note);
    $stmt->bindParam(":refWork", $refWork);
    $stmt->bindParam(":refDevice", $refDevice);
    $stmt->bindParam(":refWithdraw", $refWithdraw);
    $stmt->bindParam(":refOffer", $refOffer);
    $stmt->bindParam(":quotation", $quotation);
    $stmt->bindParam(":id_ref", $id_ref);
    $stmt->bindParam(":id", $id);
    if ($stmt->execute()) {
        if (!empty($_POST['list'])) {
            foreach ($_POST['list'] as $index => $list) {
                $sql = "INSERT INTO order_items (order_id, list, quality, amount, price, unit)  
                        VALUES (:order_id, :list, :quality, :amount, :price, :unit)";
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':order_id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':list', $list, PDO::PARAM_INT);
                $stmt->bindParam(':quality', $_POST['quality'][$index], PDO::PARAM_STR);
                $stmt->bindParam(':amount', $_POST['amount'][$index], PDO::PARAM_INT);
                $stmt->bindParam(':price', $_POST['price'][$index], PDO::PARAM_STR);
                $stmt->bindParam(':unit', $_POST['unit'][$index], PDO::PARAM_STR);

                $stmt->execute();

                $itemId = $conn->lastInsertId();

                // Insert history log
                $stmtHistory = $conn->prepare("
            INSERT INTO order_history (
                table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by, edited_at
            ) VALUES (
                :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by, NOW()
            )
        ");
                $stmtHistory->execute([
                    ':table_name' => 'order_items',
                    ':table_id'   => $itemId,
                    ':order_id'   => $id,
                    ':field_name' => '',
                    ':old_value'  => '',
                    ':new_value'  => '',
                    ':action'     => 'insert',
                    ':edited_by'  => $_SESSION['admin_log'] ?? 'unknown'
                ]);
            }
        }

        if (!empty($_POST['update_list'])) {
            foreach ($_POST['update_list'] as $modalId => $updateLists) {
                foreach ($updateLists as $recordId => $list) {

                    // Step 1: Get old data
                    $sqlOld = "SELECT * FROM order_items WHERE id = :id";
                    $stmtOld = $conn->prepare($sqlOld);
                    $stmtOld->bindParam(":id", $recordId, PDO::PARAM_INT);
                    $stmtOld->execute();
                    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

                    // Step 2: Prepare new values
                    $newData = [
                        'list'    => $list,
                        'quality' => $_POST['update_quality'][$modalId][$recordId],
                        'amount'  => $_POST['update_amount'][$modalId][$recordId],
                        'price'   => $_POST['update_price'][$modalId][$recordId],
                        'unit'    => $_POST['update_unit'][$modalId][$recordId],
                    ];

                    // Step 3: Compare and insert history
                    foreach ($newData as $field => $newValue) {
                        $oldValue = $oldData[$field];

                        if ($oldValue != $newValue) {
                            $stmtHistory = $conn->prepare("
                                INSERT INTO order_history (
                                    table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by
                                ) VALUES (
                                    :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by
                                )
                            ");
                            $stmtHistory->execute([
                                ':table_name' => 'order_items',
                                ':table_id'   => $recordId,
                                ':order_id'   => $modalId,
                                ':field_name' => $field,
                                ':old_value'  => $oldValue,
                                ':new_value'  => $newValue,
                                ':action'     => 'update',
                                ':edited_by'  => $editedBy
                            ]);
                        }
                    }

                    // Step 4: Update the record
                    $sql = "UPDATE order_items 
                            SET list = :list, quality = :quality, amount = :amount, price = :price, unit = :unit 
                            WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':list', $newData['list'], PDO::PARAM_STR);
                    $stmt->bindParam(':quality', $newData['quality'], PDO::PARAM_STR);
                    $stmt->bindParam(':amount', $newData['amount'], PDO::PARAM_INT);
                    $stmt->bindParam(':price', $newData['price'], PDO::PARAM_STR);
                    $stmt->bindParam(':unit', $newData['unit'], PDO::PARAM_STR);
                    $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }


        if (!empty($_POST['deleted_items'])) {
            foreach ($_POST['deleted_items'] as $modalId => $items) {
                foreach ($items as $recordId => $value) {
                    $sql = "UPDATE order_items SET is_deleted = 1 WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $recordId, PDO::PARAM_INT);
                    $stmt->execute();

                    $stmtOld = $conn->prepare("SELECT * FROM order_items WHERE id = :id");
                    $stmtOld->bindParam(':id', $recordId, PDO::PARAM_INT);
                    $stmtOld->execute();
                    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

                    $stmtHistory = $conn->prepare("
            INSERT INTO order_history (
                table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by, edited_at
            ) VALUES (
                :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by, NOW()
            )
        ");
                    $stmtHistory->execute([
                        ':table_name' => 'order_items',
                        ':table_id'   => $recordId,
                        ':order_id'   => $oldData['order_id'], // get from old data
                        ':field_name' => 'is_deleted',
                        ':old_value'  => $oldData['is_deleted'] ?? '0',
                        ':new_value'  => '1',
                        ':action'     => 'delete',
                        ':edited_by'  => $_SESSION['admin_log'] ?? 'unknown'
                    ]);
                }
            }
        }

        if (!empty($_POST['update_number_device'])) {
            foreach ($_POST['update_number_device'] as $orderItem => $devices) {
                foreach ($devices as $deviceId => $numberDevice) {
                    $sqlOld = "SELECT numberDevice FROM order_numberdevice WHERE id = :id";
                    $stmtOld = $conn->prepare($sqlOld);
                    $stmtOld->bindParam(':id', $deviceId, PDO::PARAM_INT);
                    $stmtOld->execute();
                    $oldRow = $stmtOld->fetch(PDO::FETCH_ASSOC);
                    $oldNumberDevice = $oldRow['numberDevice'];

                    // Step 2: Compare and insert into history if changed
                    if ($oldNumberDevice != $numberDevice) {
                        $stmtHistory = $conn->prepare("
                    INSERT INTO order_history (
                        table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by
                    ) VALUES (
                        :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by
                    )
                ");
                        $stmtHistory->execute([
                            ':table_name' => 'order_numberdevice',
                            ':table_id'   => $deviceId,
                            ':order_id'   => $orderItem,
                            ':field_name' => 'numberDevice',
                            ':old_value'  => $oldNumberDevice,
                            ':new_value'  => $numberDevice,
                            ':action'     => 'update',
                            ':edited_by'  => $editedBy // Make sure $editedBy is set
                        ]);
                    }
                    $sql = "UPDATE order_numberdevice SET numberDevice = :numberDevice WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':numberDevice', $numberDevice);
                    $stmt->bindParam(':id', $deviceId, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }

        if (!empty($_POST['deleted_devices'])) {
            foreach ($_POST['deleted_devices'] as $orderItem => $devices) {
                foreach ($devices as $deviceId => $numberDevice) {
                    $sql = "UPDATE order_numberdevice SET is_deleted = 1 WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id', $deviceId, PDO::PARAM_INT);
                    $stmt->execute();

                    $stmtOld = $conn->prepare("SELECT * FROM order_numberdevice WHERE id = :id");
                    $stmtOld->bindParam(':id', $deviceId, PDO::PARAM_INT);
                    $stmtOld->execute();
                    $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

                    $stmtHistory = $conn->prepare("
            INSERT INTO order_history (
                table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by, edited_at
            ) VALUES (
                :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by, NOW()
            )
        ");
                    $stmtHistory->execute([
                        ':table_name' => 'order_numberdevice',
                        ':table_id'   => $deviceId,
                        ':order_id'   => $oldData['order_item'], // get from old data
                        ':field_name' => 'is_deleted',
                        ':old_value'  => $oldData['is_deleted'] ?? '0',
                        ':new_value'  => '1',
                        ':action'     => 'delete',
                        ':edited_by'  => $_SESSION['admin_log'] ?? 'unknown'
                    ]);
                }
            }
        }

        if (!empty($_POST['device_numbers'])) {
            foreach ($_POST['device_numbers'] as $numberDevice) {
                if (trim($numberDevice) === "") {
                    continue;
                }

                $sql = "INSERT INTO order_numberdevice (order_item, numberDevice) VALUES (:order_item, :numberDevice)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':order_item', $id, PDO::PARAM_INT); // Use $id as the order item ID
                $stmt->bindParam(':numberDevice', $numberDevice, PDO::PARAM_STR);
                $stmt->execute();

                $itemId = $conn->lastInsertId();

                // Insert history log
                $stmtHistory = $conn->prepare("
            INSERT INTO order_history (
                table_name, table_id, order_id, field_name, old_value, new_value, action, edited_by, edited_at
            ) VALUES (
                :table_name, :table_id, :order_id, :field_name, :old_value, :new_value, :action, :edited_by, NOW()
            )
        ");
                $stmtHistory->execute([
                    ':table_name' => 'order_numberdevice',
                    ':table_id'   => $itemId,
                    ':order_id'   => $id,
                    ':field_name' => '',
                    ':old_value'  => '',
                    ':new_value'  => '',
                    ':action'     => 'insert',
                    ':edited_by'  => $_SESSION['admin_log'] ?? 'unknown'
                ]);
            }
        }

        if (!empty($_POST['id_ref'])) {
            $status = 3;
            $sql = "UPDATE data_report SET status = :status, withdraw = :withdraw WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":withdraw", $numberWork);
            $stmt->bindParam(":id", $id_ref);
            $stmt->execute();
        }
    }
    $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
    $_SESSION["warning"] = "กรุณาตรวจสอบสถานะของใบงานอีกครั้ง";
    header("location: ../check.php?numberWork=$numberWork");
}

if (isset($_POST['updateStatus'])) {
    // Check if any checkboxes are checked
    if (empty($_POST['selectedRows'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // Convert selected rows to integers
    $selectedRows = array_map('intval', $_POST['selectedRows']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET deliveryDate = :deliveryDate, status = 3 WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป
    $stmt->bindParam(':deliveryDate', $deliveryDate);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the SQL statement for each selectedRow
    foreach ($selectedRows as $selectedRow) {
        $deliveryDate = $_POST["deliveryDate"][$selectedRow];
        $id = $selectedRow;

        // Execute the SQL statement for each selectedRow
        if ($stmt->execute()) {
            $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $stmt->errorInfo()[2];
        }
    }

    // Redirect after all updates are done
    header("location: ../checkAll.php");
    exit();
}
if (isset($_POST['updateStatus3'])) {
    // ย้ายนี้ออกจากลูป


    // ตรวจสอบว่ามี checkbox ไหนถูกเลือกหรือไม่
    if (empty($_POST['selectedRows3'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // แปลง ID ที่เลือกเป็นตัวเลข
    $selectedRows = array_map('intval', $_POST['selectedRows3']);

    echo '<pre>';
    var_dump([
        'selectedRows' => $selectedRows,
        'selectedRows3' => $_POST['selectedRows3'],
    ]);
    echo '</pre>';
    exit();

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET status = 4 WHERE id IN (" . implode(',', $selectedRows) . ")";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป


    // ทำการ execute คำสั่ง SQL
    if ($stmt->execute()) {
        $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
    } else {
        $_SESSION["error"] = "พบข้อผิดพลาด";
    }

    // Redirect หลังจากที่ทำการอัพเดทเสร็จสิ้น
    header("location: ../checkAll.php");
    exit();
}
if (isset($_POST['updateStatus4'])) {
    // Check if any checkboxes are checked
    if (empty($_POST['selectedRows4'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // Convert selected rows to integers
    $selectedRows = array_map('intval', $_POST['selectedRows4']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET closeDate = :closeDate, status = 5 WHERE id = :id";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป
    $stmt->bindParam(':closeDate', $closeDate);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute the SQL statement for each selectedRow
    foreach ($selectedRows as $selectedRow) {
        $closeDate = $_POST["close_date"][$selectedRow];
        $id = $selectedRow;

        // Execute the SQL statement for each selectedRow
        if ($stmt->execute()) {
            $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
        } else {
            $_SESSION["error"] = "พบข้อผิดพลาด: " . $stmt->errorInfo()[2];
        }
    }

    // Redirect after all updates are done
    header("location: ../checkAll.php");
    exit();
}


if (isset($_POST['updateStatus5'])) {
    // ย้ายนี้ออกจากลูป


    // ตรวจสอบว่ามี checkbox ไหนถูกเลือกหรือไม่
    if (empty($_POST['selectedRows5'])) {
        $_SESSION["error"] = "ไม่พบข้อมูลที่เลือก";
        header("location: ../checkAll.php");
        exit();
    }

    // แปลง ID ที่เลือกเป็นตัวเลข
    $selectedRows = array_map('intval', $_POST['selectedRows5']);

    // ทำการอัพเดทฐานข้อมูลสำหรับทุกรายการที่เลือก
    $sql = "UPDATE orderdata SET status = 5 WHERE id IN (" . implode(',', $selectedRows) . ")";
    $stmt = $conn->prepare($sql);

    // ย้ายการผูกพารามิเตอร์ไปนอกลูป


    // ทำการ execute คำสั่ง SQL
    if ($stmt->execute()) {
        $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
    } else {
        $_SESSION["error"] = "พบข้อผิดพลาด";
    }

    // Redirect หลังจากที่ทำการอัพเดทเสร็จสิ้น
    header("location: ../checkAll.php");
    exit();
}
if (isset($_POST['sla'])) {
    $sla_name = $_POST['sla_name'];
    $sla_id = $_POST['sla_id'];
    try {
        $sql = "SELECT * FROM sla WHERE sla_name = :sla_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":sla_name", $sla_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['sla_name'] == $sla_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE sla SET sla_name = :sla_name WHERE sla_id = :sla_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":sla_id", $sla_id);
            $stmt->bindParam(":sla_name", $sla_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
if (isset($_POST['kpi'])) {
    $kpi_name = $_POST['kpi_name'];
    $kpi_id = $_POST['kpi_id'];
    try {
        $sql = "SELECT * FROM kpi WHERE kpi_name = :kpi_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":kpi_name", $kpi_name);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {
            if ($result['kpi_name'] == $kpi_name) {
                $_SESSION['error'] = 'มีรายการนี้อยู่แล้ว';
                backToInsertPage();
            }
        } else if (!isset($_SESSION['error'])) {
            $sql = "UPDATE kpi SET kpi_name = :kpi_name WHERE kpi_id = :kpi_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":kpi_id", $kpi_id);
            $stmt->bindParam(":kpi_name", $kpi_name);

            if ($stmt->execute()) {
                $_SESSION["success"] = "อัพเดทข้อมูลเรียบร้อยแล้ว";
                backToInsertPage();
            }
        }
    } catch (PDOException $e) {
        echo '' . $e->getMessage() . '';
    }
}
