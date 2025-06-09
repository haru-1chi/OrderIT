
              <?php
              $order_id = $order['id'];
              $sql = "
    SELECT id,status, timestamp 
    FROM order_status 
    WHERE order_id = :order_id 
    ORDER BY status";
              $stmt = $conn->prepare(query: $sql);
              $stmt->execute(['order_id' => $order_id]);
              $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);

              // Check if the order is canceled (status = 6 exists)
              $isCanceled = in_array(6, array_column($statuses, 'status'));
              $isCloseJob = in_array(5, array_column($statuses, 'status'));
              // Define status names
              $statusNames = [
                1 => "รอรับเอกสารจากหน่วยงาน",
                2 => "รอส่งเอกสารไปพัสดุ",
                3 => "รอพัสดุสั่งของ",
                4 => "รอหมายเลขครุภัณฑ์",
                5 => "ปิดงาน",
                6 => "ยกเลิก"
              ];

              // Current status from records
              if (str_contains($numberWork, 'S')) {
                // It's special
                $currentStatus = !empty($statuses) ? max(array_column($statuses, 'status')) : 2;
              } else {
                // It's normal
                $currentStatus = !empty($statuses) ? max(array_column($statuses, 'status')) : 0;
              }


              ?>
              <h4 class="mt-3">สถานะ</h4>
              <table id="pdf" style="width: 100%;" class="table">
                <thead class="table-warning">
                  <tr class="text-center">
                    <th scope="col">สถานะ</th>
                    <th scope="col">วันที่อัพเดตสถานะ</th>
                    <th scope="col">ปุ่มยืนยัน</th>
                  </tr>
                </thead>
                <tbody class="text-center">
                  <?php
                  foreach ($statusNames as $key => $name) {
                    $record = array_filter($statuses, fn($row) => $row['status'] == $key);
                    $timestamp = $record ? reset($record)['timestamp'] : null;
                    $id = $record ? reset($record)['id'] : null;


                    echo "<tr>";
                    echo "<td>{$name}</td>";
                    echo "<td>" . ($timestamp
                      ? date('d/m/Y', strtotime($timestamp))
                      : (($key == $currentStatus + 1 || $key == 6 || $key == 5) && !$isCanceled ? date('d/m/Y') : '-')) . "</td>";
                    echo "<td>";

                    if ($isCanceled) {
                      if ($key == 6) {
                        echo "<div class='d-flex justify-content-center align-items-center gap-2'><p class='text-danger'>ยกเลิกใบเบิกแล้ว</p>";
                        echo "
                        <button type='button' class='btn mb-3 btn-warning undo-btn' data-id='{$id}''>
                            <svg fill='#000000' height='16px' width='16px' viewBox='0 0 423.642 423.642' xmlns='http://www.w3.org/2000/svg'>
                                <path d='M342.369,37.498h43.755v-30H272.128v113.995h30V46.501c78.268,44.711,113.054,141.985,77.673,227.401
                                c-28.973,69.949-96.829,112.248-168.216,112.242c-23.142-0.002-46.667-4.45-69.343-13.842c-44.869-18.586-79.815-53.532-98.4-98.401
                                c-18.585-44.869-18.585-94.29,0-139.159l-27.717-11.48c-44.696,107.907,6.729,232.059,114.636,276.756
                                c26.424,10.945,53.818,16.126,80.784,16.125c83.158-0.001,162.221-49.278,195.972-130.762
                                C444.692,195.637,415.368,94.662,342.369,37.498z'/>
                            </svg>
                        </button></div>
                        ";
                      } else {
                        if ($timestamp) {
                          echo "<div class='d-flex justify-content-center align-items-center gap-2'><p>ยืนยันแล้ว</p>";
                          echo "
                        <button type='button' class='btn mb-3 btn-warning undo-btn' data-id='{$id}''>
                            <svg fill='#000000' height='16px' width='16px' viewBox='0 0 423.642 423.642' xmlns='http://www.w3.org/2000/svg'>
                                <path d='M342.369,37.498h43.755v-30H272.128v113.995h30V46.501c78.268,44.711,113.054,141.985,77.673,227.401
                                c-28.973,69.949-96.829,112.248-168.216,112.242c-23.142-0.002-46.667-4.45-69.343-13.842c-44.869-18.586-79.815-53.532-98.4-98.401
                                c-18.585-44.869-18.585-94.29,0-139.159l-27.717-11.48c-44.696,107.907,6.729,232.059,114.636,276.756
                                c26.424,10.945,53.818,16.126,80.784,16.125c83.158-0.001,162.221-49.278,195.972-130.762
                                C444.692,195.637,415.368,94.662,342.369,37.498z'/>
                            </svg>
                        </button></div>
                        ";
                        } else {
                          echo "<p >-</p>";
                        }
                      }
                    } else {
                      if ($timestamp) {
                        echo "<div class='d-flex justify-content-center align-items-center gap-2'><p>ยืนยันแล้ว</p>";
                        echo "
                        <button type='button' class='btn mb-3 btn-warning undo-btn' data-id='{$id}''>
                            <svg fill='#000000' height='16px' width='16px' viewBox='0 0 423.642 423.642' xmlns='http://www.w3.org/2000/svg'>
                                <path d='M342.369,37.498h43.755v-30H272.128v113.995h30V46.501c78.268,44.711,113.054,141.985,77.673,227.401
                                c-28.973,69.949-96.829,112.248-168.216,112.242c-23.142-0.002-46.667-4.45-69.343-13.842c-44.869-18.586-79.815-53.532-98.4-98.401
                                c-18.585-44.869-18.585-94.29,0-139.159l-27.717-11.48c-44.696,107.907,6.729,232.059,114.636,276.756
                                c26.424,10.945,53.818,16.126,80.784,16.125c83.158-0.001,162.221-49.278,195.972-130.762
                                C444.692,195.637,415.368,94.662,342.369,37.498z'/>
                            </svg>
                        </button></div>
                        ";
                      } elseif ($key == $currentStatus + 1 && $key <= 4) {
                        echo "<button type='button' class='btn mb-3 btn-warning confirm-btn' data-status='{$key}' data-order-id='{$order_id}'>รอการยืนยัน</button>";
                      } elseif ($key > $currentStatus + 1 && $key <= 4) {
                        echo "<button type='button' class='btn mb-3 btn-secondary' disabled>รอดำเนินการก่อนหน้า</button>";
                      }
                    }
                    if ($key == 5 && !$isCloseJob && !$isCanceled) {
                      echo "<button type='button' class='btn mb-3 btn-success cancel-btn' data-status='5' data-order-id='{$order_id}'>ปิดงาน</button>";
                    }
                    if ($key == 6 && !$isCanceled) {
                      echo "<button type='button' class='btn mb-3 btn-danger cancel-btn' data-status='6' data-order-id='{$order_id}'>ยกเลิกใบเบิก</button>";
                    }

                    echo "</td>";
                    echo "</tr>";
                  }
                  ?>
                </tbody>
              </table>

              ---------------------------------------------------------------------------------------------------
               <div class="row me-1">
            <form action="export.php" method="post">
              <div class="d-flex justify-content-end">
                <button type="submit" name="DataAll" class="btn btn-primary my-3">Export to Excel</button>
              </div>
            </form>
            <?php

            $sql = "
SELECT os.status, COUNT(*) AS count
FROM orderdata_new AS od
LEFT JOIN (
    SELECT order_id, status
    FROM order_status AS os1
    WHERE (os1.timestamp, os1.status) IN (
                       SELECT MAX(os2.timestamp) AS latest_timestamp, MAX(os2.status) AS latest_status
                       FROM order_status AS os2
                       WHERE os2.order_id = os1.order_id
                     )
) AS os ON os.order_id = od.id
WHERE os.status IS NOT NULL  
GROUP BY os.status
ORDER BY os.status;

";

            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $statusOptions = array(
              1 => array(
                'text' => "รอรับเอกสารจากหน่วยงาน",
                'color' => "#FFAE2C"
              ),
              2 => array(
                'text' => "รอส่งเอกสารไปพัสดุ",
                'color' => "#6CB1FF"
              ),
              3 => array(
                'text' => "รอพัสดุสั่งของ",
                'color' => "#7ECC7A"
              ),
              4 => array(
                'text' => "รอหมายเลขครุภัณฑ์",
                'color' => "#FF9359"
              ),
              5 => array(
                'text' => "ปิดงาน",
                'color' => "#51A075"
              ),
              6 => array(
                'text' => "ยกเลิก",
                'color' => "#FF7575"
              )
            );

            foreach ($statusCounts as $statusCount) {
              $status = $statusCount['status'];
              $count = $statusCount['count'];

              // Use default values if status is not mapped
              $textS = isset($statusOptions[$status]['text']) ? $statusOptions[$status]['text'] : "ไม่ระบุสถานะ";
              $color = isset($statusOptions[$status]['color']) ? $statusOptions[$status]['color'] : sprintf('#%06X', rand(0, 0xFFFFFF));
            ?>

              <div class="col-sm-6">
                <div class="card text-white mb-3" style="background-color: <?= $color ?>">
                  <div class="card-body">
                    <h1 class="card-title" style="font-size: 50px;"><?= $count ?></h1>
                    <h5 class="m-0"><?= htmlspecialchars($textS) ?></h5>
                  </div>
                  <div class="card-footer">
                    <h5 class="card-text text-end">
                      <a href="checkStatus.php?status=<?= urlencode($status) ?>" class="text-white" style="text-decoration: none;"> ▽ รายละเอียดเพิ่มเติม</a>
                    </h5>
                  </div>
                </div>
              </div>

            <?php
            }
            ?>
          </div>