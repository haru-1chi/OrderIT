<form action="system/insert.php" method="post">
    <div class="row">
        <div class="card col-sm-12 col-lg-6 col-md-12">
            <div class="row">

                <div class="col-sm-4">
                    <div class="mb-3">
                        <label class="form-label" for="inputGroupSelect01">หมายเลขออกงาน</label> !!!!!!!!!!
                        <?php
                        $sql = "SELECT numberWork FROM orderdata";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        ?>
                        <select required class="form-select" name="numberWork">
                            <?php
                            if (!$result) { ?>
                                <option value="1/67">1/67</option>
                            <?php } else {
                                foreach ($result as $row) {
                                    $selectedValue = $row['numberWork'];
                                    list($numerator, $denominator) = explode('/', $selectedValue);

                                    $currentDate = new DateTime();

                                    // Set $october10 to be October 10 of the current year
                                    $october1 = new DateTime(($currentDate)->format('Y') + 1 . '-10-10');

                                    // Check if the current date is after October 10
                                    if ($currentDate > $october1) {
                                        // Add 1 to the numerator and set the denominator to 1
                                        $newNumerator = intval($numerator) + 1;
                                        $newDenominator = intval($denominator) + 1; // เริ่มต้นที่ 1 ในปีถัดไป
                                    } else {
                                        // Keep the numerator and increment the denominator
                                        $newNumerator = intval($numerator) + 1;
                                        $newDenominator = intval($denominator);
                                    }

                                    $newValueToCheck = $newNumerator . '/' . $newDenominator;
                                } ?>
                                <option value="<?= $newValueToCheck ?>"><?= $newValueToCheck ?></option>
                            <?php }
                            ?>
                        </select>
                    </div>
                </div>







                <tbody id="table-body">
                    <tr class="text-center">
                        <th scope="row">1</th>
                        <td>
                            <select style="width: 120px" class="form-select device-select" name="list[]" data-row="1">
                                <option selected value="" disabled>เลือกรายการอุปกรณ์</option>
                                <!-- Populate options dynamically -->
                                <?php
                                $sql = "SELECT * FROM device_models ORDER BY models_name ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $d) {
                                ?>
                                    <option value="<?= $d['models_id'] ?>"><?= $d['models_name'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </td>
                        <td><textarea rows="2" maxlength="60" name="quality[]" class="limitedTextarea" ></textarea></td>
                        <td><input style="width: 2rem;" type="text" name="amount[]"></td>
                        <td><input style="width: 4rem;" type="text" name="price[]"></td>
                        <td><input style="width: 4rem;" type="text" name="unit[]"></td>
                        <td><button type="button" class="btn btn-danger remove-row">ลบ</button></td>
                    </tr>
                    <button type="button" id="add-row" class="btn btn-primary">เพิ่มแถว</button>
                </tbody>
                

                <div class="mb-3">
                    <label class="form-label" for="inputGroupSelect01">หมายเลขออกงาน</label>
                     
                    <?php
                    $sql = "SELECT numberWork FROM orderdata";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <select required class="form-select" name="numberWork">

                      <?php
                      if (!$result) { ?>

                        <option value="1/67">1/67</option>

                      <?php } else {
                        foreach ($result as $row) {
                          $selectedValue = $row['numberWork'];
                          list($numerator, $denominator) = explode('/', $selectedValue);

                          $currentDate = new DateTime();

                          // Set $october10 to be October 10 of the current year
                          $october1 = new DateTime(($currentDate)->format('Y') . '-10-10');

                          // Check if the current date is after October 10
                          if ($currentDate = $october1) {
                            // Add 1 to the numerator and set the denominator to 1
                            $newNumerator = intval($numerator) + 1;
                            //$newDenominator = intval($denominator) + 1; // เริ่มต้นที่ 1 ในปีถัดไป
                            $newDenominator = intval($denominator);
                          } else {
                            // Keep the numerator and increment the denominator
                            $newNumerator = intval($numerator) + 1;
                            $newDenominator = intval($denominator);
                          }

                          $newValueToCheck = $newNumerator . '/' . $newDenominator;
                        } ?>

                        <option value="<?= $newValueToCheck ?>"><?= $newValueToCheck ?></option>

                      <?php } ?>
                    </select>

                  </div>
                
               

</form>