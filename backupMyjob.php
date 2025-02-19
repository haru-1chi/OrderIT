<!-- เคสเลขครุภัณฑ์ซ้ำ---------------------------------------------------------------- -->
<div class="w-100 d-flex justify-content-center">
    <button class="w-100 btn btn-primary mt-3" onclick="toggleModal('#overlayModalTask<?= $row['id'] ?>')">overlay modal</button>
</div>

<div id="overlayModalTask<?= $row['id'] ?>" class="modal" style="display: none;">
    <div class="p-5 d-flex justify-content-center gap-4">
        <div class="modal-content job-modal">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">รายละเอียดงาน</h1>
                <button type="button" class="btn-close" onclick="toggleModal('#overlayModalTask<?= $row['id'] ?>')"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <label>หมายเลขงาน</label>
                        <input type="text" class="form-control"
                            value="<?= $row['id'] ?>" disabled>
                    </div>
                    <div class="col-6">
                        <label>วันที่</label>
                        <input type="text" class="form-control"
                            value="<?= $row['date_report'] ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <label>เวลาแจ้ง</label>
                        <input type="text" class="form-control"
                            value="<?= date('H:i', strtotime($row['time_report'])) ?>" disabled>
                    </div>
                    <div class="col-4">
                        <label>เวลารับงาน</label>
                        <input type="text" class="form-control"
                            value="<?= date('H:i', strtotime($row['take']))  ?>" disabled>
                    </div>
                    <div class="col-4">
                        <label>เวลาปิดงาน (ถ้ามี)</label>
                        <input type="time" class="form-control" id="time_report" name="close_date"
                            value="<?= ($row['status'] == 3 && ($row['close_date'] === '00:00:00.000000' || $row['close_date'] === null || trim($row['close_date']) === ''))
                                        ? '' : (($row['close_date'] && $row['close_date'] !== '00:00:00.000000') ? date('H:i', strtotime($row['close_date'])) : '') ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label>ผู้แจ้ง</label>
                        <input type="text" class="form-control"
                            value="<?= $row['reporter'] ?>" disabled>
                    </div>
                    <div class="col-6">
                        <label>หน่วยงาน</label>
                        <?php
                        $sql = "SELECT depart_name FROM depart WHERE depart_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$row['department']]);
                        $departRow = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>

                        <input type="text" class="form-control"
                            value="<?= $departRow['depart_name'] ?>" disabled>

                        <input type="hidden" name="department"
                            value="<?= $row['department'] ?>">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label>เบอร์ติดต่อกลับ</label>
                        <input type="text" class="form-control"
                            value="<?= $row['tel'] ?>" disabled>
                    </div>
                    <div class="col-6">
                        <label for="deviceInput">อุปกรณ์</label>
                        <input type="text" class="form-control" id="deviceInput<?= $row['id'] ?>" name="deviceName"
                            value="<?= $row['deviceName'] ?>">
                        <input type="hidden" id="deviceId<?= $row['id'] ?>">
                    </div>

                </div>

                <div class="row">
                    <div class="col-6">
                        <label>หมายเลขครุภัณฑ์ (ถ้ามี)</label>
                        <input value="<?= $row['number_device'] ?>" type="text"
                            class="form-control" name="number_devices">
                    </div>
                    <div class="col-6">
                        <label>หมายเลข IP addrees</label>
                        <input type="text" class="form-control"
                            value="<?= $row['ip_address'] ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label>อาการที่ได้รับแจ้ง</label>
                        <input type="text" class="form-control"
                            value="<?= $row['report'] ?>" disabled>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label>รูปแบบการทำงาน<span style="color: red;">*</span></label>
                        <select class="form-select" name="device"
                            aria-label="Default select example">
                            <option value="<?= $row['device'] ?: '' ?>"
                                selected>
                                <?= !empty($row['device']) ? $row['device'] : '-' ?>
                            </option>
                            <?php
                            $sql = "SELECT * FROM workinglist";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($checkD as $d) {
                                if ($d['workingName'] != $row['device']) {
                            ?>
                                    <option value="<?= $d['workingName'] ?>">
                                        <?= $d['workingName'] ?>
                                    </option>
                            <?php }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label>หมายเลขใบเบิก</label>
                        <?php if (empty($row['withdraw'])) { ?>
                            <input disabled type="text"
                                class="form-control withdrawInput" name="withdraw"
                                id="withdrawInput<?= $row['id'] ?>">
                        <?php } else { ?>
                            <input disabled value="<?= $row['withdraw'] ?>"
                                type="text" class="form-control withdrawInput"
                                name="withdraw" id="withdrawInput<?= $row['id'] ?>">
                            <input type="hidden" value="<?= $row['withdraw'] ?>"
                                class="form-control withdrawInput"
                                id="withdrawInputHidden<?= $row['id'] ?>" name="withdraw2">
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label>รายละเอียด<span style="color: red;">*</span></label>
                        <textarea class="form-control " name="description" rows="2"><?= $row['description'] ?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label>หมายเหตุ</label>
                        <input value="<?= $row['note'] ?>" type="text"
                            class="form-control" name="noteTask">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <label>ผู้คีย์งาน</label>
                        <input value="<?= $row['create_by'] ?>" type="text"
                            class="form-control" name="create_by" disabled>
                    </div>
                    <div class="col-6">
                        <label>ซ่อมครั้งที่</label>
                        <?php
                        if (!empty($row['number_device']) && $row['number_device'] !== '-') {
                            $sqlCount = "SELECT COUNT(*) AS repair_count 
                     FROM data_report 
                     WHERE number_device = :number_device 
                     AND (number_device IS NOT NULL AND number_device <> '' AND number_device <> '-')";
                            $stmtCount = $conn->prepare($sqlCount);
                            $stmtCount->bindParam(":number_device", $row['number_device']);
                            $stmtCount->execute();
                            $count = $stmtCount->fetch(PDO::FETCH_ASSOC);
                            $repairCount = $count['repair_count'];
                        } else {
                            $repairCount = '-';
                        }
                        ?>
                        <input value="<?= $repairCount ?>" type="text" class="form-control" name="repair_count">
                    </div>
                </div>

                <hr class="mb-2">
                <!-- !!!!! -->
                <h4 class="mt-0 mb-3" id="staticBackdropLabel">งานคุณภาพ</h4>
                <div class="row">
                    <div class="col-12">
                        <label>ปัญหาอยู่ใน SLA หรือไม่<span style="color: red;">*</span></label>
                        <select class="form-select" name="sla"
                            aria-label="Default select example">
                            <option value="<?= $row['sla'] ?: '' ?>" selected>
                                <?= !empty($row['sla']) ? $row['sla'] : '-' ?>
                            </option>
                            <?php
                            $sql = "SELECT * FROM sla";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($checkD as $d) {
                                if ($d['sla_name'] != $row['sla']) {
                            ?>
                                    <option value="<?= $d['sla_name'] ?>">
                                        <?= $d['sla_name'] ?>
                                    </option>
                            <?php }
                            }
                            ?>

                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label>เป็นตัวชี้วัดหรือไม่<span style="color: red;">*</span></label>
                        <select class="form-select" name="kpi"
                            aria-label="Default select example">
                            <option value="<?= $row['kpi'] ?: '' ?>" selected>
                                <?= !empty($row['kpi']) ? $row['kpi'] : '-' ?>
                            </option>
                            <?php
                            $sql = "SELECT * FROM kpi";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($checkD as $d) {
                                if ($d['kpi_name'] != $row['kpi']) {
                            ?>
                                    <option value="<?= $d['kpi_name'] ?>">
                                        <?= $d['kpi_name'] ?>
                                    </option>
                            <?php }
                            }
                            ?>

                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <label>Activity Report<span style="color: red;">*</span></label>
                        <select class="form-select" name="problem"
                            aria-label="Default select example">
                            <?php
                            $sql = "SELECT * FROM problemlist";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $data = $stmt->fetchAll(PDO::FETCH_ASSOC); ?>
                            <option value="<?= $row['problem'] ?: '' ?>"
                                selected>
                                <?= !empty($row['problem']) ? $row['problem'] : '-' ?>
                            </option>
                            <?php foreach ($data as $d) {
                                if ($row['problem'] != $d['problemName']) { ?>
                                    <option value="<?= $d['problemName'] ?>">
                                        <?= $d['problemName'] ?>
                                    </option>
                            <?php }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="justify-content: space-between; border: none;">
                <button type="submit" class="btn btn-danger"
                    name="disWork">คืนงาน</button>
                <button type="button" class="btn btn-primary" onclick="toggleModal('#requisitionModal<?= $row['id'] ?>')">เบิก/ส่งซ่อม</button>
                <button type="submit" class="btn me-3 btn-primary"
                    name="Bantext">บันทึก</button>
                <button type="submit" name="CloseSubmit"
                    class="btn btn-success">ปิดงาน</button>
            </div>
        </div>
    </div>
</div>