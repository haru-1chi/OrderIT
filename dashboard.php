<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
$dateNow = new DateTime();
$dateThai = $dateNow->format("Y-m-d");

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
<!doctype html>
<html lang="en">

<head>
    <title>ระบบบริหารจัดการ ศูนย์บริการซ่อมคอมพิวเตอร์</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">

    <!-- Bootstrap CSS v5.2.1 -->
    <?php bs5() ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <script>
        // Function to reload the page
        // function refreshPage() {
        //     location.reload();
        // }

        // // Set timeout to refresh the page every 1 m inute (60000 milliseconds)
        // setTimeout(refreshPage, 30000);
    </script>
    <style>
        body {
            background-color: #F9FDFF;
        }

        #dataAll tbody tr td {
            background-color: #fff4f5;
            color: #000;
        }

        #inTime tbody tr td {
            background-color: #fffbf0;
            color: #000;
        }

        #dataAllNOTTAKE tbody tr td {
            background-color: #fff4f5;
            color: #000;
        }

        #clam tbody tr td {
            background-color: #fff4f5;
            color: #000;
        }

        #wait tbody tr td {
            background-color: #f2f7ff;
            color: #000;
        }

        #success tbody tr td {
            background-color: #f3fffa;
            color: #000;
        }

        #rating tbody tr td {
            background-color: #fffbf0;
            color: #000;
        }
    </style>
</head>

<body>
    <?php
    $report_count = $_SESSION['report_count'] ?? 0;
    navbar($report_count)
    ?>

    <div class="container">
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
            <div class="col-sm-12 col-lg-12 col-md-12">
                <h1 class="text-center my-4">สรุปยอดจำนวนงาน</h1>
                <div class="row d-flex justify-content-center"></div>
                <div class="d-flex">
                    <div class="card p-3 mt-4 rounded-4" style="width: 1850px; height: 400px;">
                        <input type="hidden" id="filter-date" class="form-control mb-3" />
                        <select id="timelineFilter" class="form-control">
                            <option value="problem" selected>Activity Report</option>
                            <option value="device">รูปแบบการทำงาน</option>
                            <option value="report">อาการรับแจ้ง</option>
                            <option value="sla">SLA</option>
                        </select>
                        <canvas id="gantt-summary" width="800" height="200"></canvas>
                    </div>
                </div>

                <!-- ------------------- notification -------------------- -->
                <!-- <button type="button" class="btn btn-primary" id="liveToastBtn">Show live toast</button> -->

                <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3"></div>
                <audio id="notificationSound" src="audio/0313.MP3"></audio>

                <script>
                    let lastReportId = 0; // Track the highest ID we've seen

                    function checkNewReports() {
                        fetch('check_new_reports.php')
                            .then(response => response.json())
                            .then(data => {
                                if (data.reports && data.reports.length > 0) {
                                    data.reports.forEach(report => {
                                        if (report.id > lastReportId) {
                                            lastReportId = report.id; // Update last seen ID
                                            showNotification(report);
                                        }
                                    });
                                }

                                if (data.reports_set_up && data.reports_set_up.length > 0) {
                                    data.reports_set_up.forEach(report => {
                                        showNotification(report);
                                    });
                                }

                                console.log("reports:", data.reports);
                            })
                            .catch(error => console.error('Error:', error));
                    }

                    function showNotification(report) {
                        const toastContainer = document.getElementById('toastContainer');

                        const toastElement = document.createElement('div');
                        toastElement.className = 'toast bg-primary text-white mb-2';
                        toastElement.setAttribute('role', 'alert');
                        toastElement.setAttribute('aria-live', 'assertive');
                        toastElement.setAttribute('aria-atomic', 'true');

                        toastElement.innerHTML = `
        <div class="toast-header">
            <strong class="me-auto">หน่วยงาน ${report.depart_name}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            ${report.report}
        </div>
    `;

                        toastContainer.appendChild(toastElement);

                        const toast = new bootstrap.Toast(toastElement);
                        toast.show();

                        document.getElementById('notificationSound').play();

                        // Optional: Auto-remove toast from DOM after hidden
                        toastElement.addEventListener('hidden.bs.toast', () => {
                            toastElement.remove();
                        });
                    }

                    // First check immediately
                    checkNewReports();

                    // Check every 30 seconds
                    setInterval(checkNewReports, 30000);
                </script>

                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <h1>งานวันนี้</h1>
                    <div class="table-responsive">
                        <table id="dataAll" class="table table-danger">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">หมายเลข</th>
                                    <th scope="col">วันที่</th>
                                    <th scope="col">เวลาแจ้ง</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">ผู้แจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col">เบอร์ติดต่อกลับ</th>
                                    <th scope="col">สร้างโดย</th>
                                    <th scope="col">ปุ่มรับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>กำลังดำเนินการ</h1>
                        <table id="inTime" class="table table-warning">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 80px;">หมายเลข</th>
                                    <th scope="col">ผู้ซ่อม</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col" style="width: 80px;">เวลาแจ้ง</th>
                                    <th scope="col" style="width: 80px;">เวลารับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <h1>งานที่ถูกบันทึกไว้</h1>
                    <hr>
                    <div class="table-responsive">
                        <table id="dataAllNOTTAKE" class="table table-danger">
                            <thead>
                                <tr class="text-center">
                                    <th scope="col">หมายเลข</th>
                                    <th scope="col">วันที่</th>
                                    <th scope="col">เวลาแจ้ง</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">ผู้แจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col">เบอร์ติดต่อกลับ</th>
                                    <th scope="col">สร้างโดย</th>
                                    <th scope="col">ปุ่มรับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>รออะไหล่</h1>
                        <hr>
                        <table id="wait" class="table table-primary">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 80px;">หมายเลข</th>
                                    <th scope="col">ผู้ซ่อม</th>
                                    <th scope="col">อุปกรณ์</th>
                                    <th scope="col">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col" style="width: 80px;">เวลาแจ้ง</th>
                                    <th scope="col" style="width: 80px;">เวลารับงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                </div>
                <div class="card rounded-4 shadow-sm p-3 mt-4 col-sm-12 col-lg-12 col-md-12">
                    <div class="table-responsive">
                        <h1>งานที่เสร็จ</h1>
                        <form method="post" action="export.php">
                            <button name="actAll" class="btn btn-primary" type="submit">Export->Excel</button>
                        </form>
                        <hr>
                        <table id="success" class="table table-success">
                            <thead>
                                <tr>
                                    <th scope="col" style="text-align: left; width: 80px;">หมายเลข</th>
                                    <th scope="col" style="text-align: left; width: 170px;">ผู้ซ่อม</th>
                                    <th scope="col" style="text-align: left;">อาการที่ได้รับแจ้ง</th>
                                    <th scope="col" style="text-align: left; width: 170px;">หน่วยงาน</th>
                                    <th scope="col" style="text-align: left; width: 170px;">SLA</th>
                                    <th scope="col" style="text-align: left; width: 120px;">ตัวชี้วัด</th>
                                    <th scope="col" style="text-align: left; width: 80px;">เวลาแจ้ง</th>
                                    <th scope="col" style="text-align: left; width: 80px;">เวลารับงาน</th>
                                    <th scope="col" style="text-align: left; width: 80px;">เวลาปิดงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>

                <script>
                    function fetchCards(type) {
                        fetch(`dashboard_get_tasks.php?type=${type}`)
                            .then(response => response.json())
                            .then(data => {
                                const statusOptions = {
                                    0: {
                                        text: "งานที่ยังไม่ได้รับ",
                                        color: "#FF7575"
                                    },
                                    2: {
                                        text: "กำลังดำเนินงาน",
                                        color: "#F8BF24"
                                    },
                                    3: {
                                        text: "รออะไหล่",
                                        color: "#659BFF"
                                    },
                                    4: {
                                        text: "เสร็จงาน",
                                        color: "#6CC668"
                                    },
                                    5: {
                                        text: "ส่งซ่อม",
                                        color: "#D673D3"
                                    },
                                    6: {
                                        text: "รอกรอกรายละเอียด",
                                        color: "#6CC668"
                                    },
                                };

                                const container = document.querySelector('.row.d-flex.justify-content-center');
                                container.innerHTML = ''; // Clear existing content

                                data.forEach(({
                                    status,
                                    count
                                }) => {
                                    if (status == 1) return;

                                    const textS = statusOptions[status]?.text || "ไม่ระบุสถานะ";
                                    const color = statusOptions[status]?.color || `#${Math.floor(Math.random()*16777215).toString(16)}`;

                                    const card = `
                        <div class="col-sm-2">
                            <div class="rounded-3 text-white ps-3 pb-2" style="max-width: 18rem; background-color: ${color}">
                                <div class="card-header">
                                    <ion-icon name="people-outline"></ion-icon>
                                    <div class="d-flex align-items-end">
                                        <p style="font-size: 45px; margin: 0px;">${count}</p>
                                        <p class="ms-2" style="font-size: 32px; margin: 0px; margin-bottom:.4rem;">งาน</p>
                                    </div>
                                    <p style="font-size: 20px; margin: 0px;">${textS}</p>
                                </div>
                            </div>
                        </div>
                    `;

                                    container.innerHTML += card;
                                });
                            })
                            .catch(error => console.error('Error fetching data:', error));
                    }

                    function fetchTasks(type, tableId) {
                        fetch(`dashboard_get_tasks.php?type=${type}`)
                            .then(response => response.json())
                            .then(data => {
                                let table = $.fn.dataTable.isDataTable(`#${tableId}`) ?
                                    $(`#${tableId}`).DataTable() :
                                    $(`#${tableId}`).DataTable({
                                        order: [
                                            [0, 'desc']
                                        ],
                                        destroy: true,
                                    });

                                table.clear();

                                let tableBody = document.querySelector(`#${tableId} tbody`);
                                tableBody.innerHTML = "";

                                data.forEach(row => {
                                    let tr = document.createElement("tr");
                                    if (type === "today" || type === "over_due") {
                                        tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.date_report}</td>
                        <td>${row.time_report}</td>
                        <td>${row.deviceName}</td>
                        <td>${row.report}</td>
                        <td>${row.reporter}</td>
                        <td>${row.depart_name}</td>
                        <td>${row.tel}</td>
                        <td>${row.create_by}</td>
                        <td>
                            <form action="system/insert.php" method="post">
                                <input type="hidden" name="username" value="<?= $admin ?>">
                                <input type="hidden" name="id" value="${row.id}">
                                <button type="submit" name="takeaway" class="btn btn-primary">รับงาน</button>
                            </form>
                        </td>
                    `;
                                    } else if (type === "in_progress") {
                                        let takeFormatted = row.take ? row.take : "-";
                                        tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.fname} ${row.lname}</td>
                        <td>${row.deviceName}</td>
                        <td>${row.report}</td>
                        <td>${row.depart_name}</td>
                        <td>${row.time_report}</td>
                        <td>${takeFormatted}</td>
                    `;
                                    } else if (type === "calm") {
                                        let reportFormatted = row.time_report ? row.time_report : "-";
                                        let takeFormatted = row.take ? row.take : "-";
                                        tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.fname} ${row.lname}</td>
                        <td>${row.deviceName}</td>
                        <td>${row.report}</td>
                        <td>${row.depart_name}</td>
                        <td>${reportFormatted}</td>
                        <td>${takeFormatted}</td>
                    `;
                                    } else if (type === "finish") {
                                        let reportFormatted = row.time_report ? row.time_report : "-";
                                        let takeFormatted = row.take ? row.take : "-";
                                        let closeTimeFormatted = row.close_date ? row.close_date : "-";
                                        tr.innerHTML = `
                        <td>${row.id}</td>
                        <td>${row.fname} ${row.lname}</td>
                        <td>${row.report}</td>
                        <td>${row.depart_name}</td>
                        <td>${row.sla}</td>
                        <td>${row.kpi}</td>
                        <td>${reportFormatted}</td>
                        <td>${takeFormatted}</td>
                        <td>${closeTimeFormatted}</td>
                    `;
                                    }

                                    tableBody.appendChild(tr);
                                });
                                table.rows.add($(tableBody).find("tr")).draw();
                            })
                            .catch(error => console.error("Error fetching tasks:", error));
                    }

                    // Call the functions on page load
                    fetchTasks("today", "dataAll");
                    fetchTasks("in_progress", "inTime");
                    fetchTasks("over_due", "dataAllNOTTAKE");
                    fetchTasks("calm", "wait");
                    fetchTasks("finish", "success");

                    fetchCards('cards');
                    // fetchTasks("success", "success");

                    // Optionally, refresh data every 30 seconds without reloading the page
                    setInterval(() => {
                        fetchTasks("today", "dataAll");
                        fetchTasks("in_progress", "inTime");
                        fetchCards('cards');
                    }, 30000);
                </script>

                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const ctx = document.getElementById('myChart');
                        <?php
                        $sql_statuses = "SELECT status, COUNT(*) as count FROM data_report GROUP BY status";
                        $stmt_statuses = $conn->prepare($sql_statuses);
                        $stmt_statuses->execute();
                        $statuses = $stmt_statuses->fetchAll(PDO::FETCH_ASSOC);

                        // แปลง labels และ counts จาก status
                        $status_labels = ['งานที่ยังไม่ได้รับ', 'กำลังดำเนินการ', 'รออะไหล่', 'งานที่เสร็จ'];
                        $status_counts = ['0', '0', '0', '0'];
                        foreach ($statuses as $status) {
                            $status_code = $status['status'];
                            $count = $status['count'];

                            $status_counts[$status_code - 1] = $count;
                        }

                        // แปลงอาร์เรย์ counts เป็นสตริงที่คั่นด้วยเครื่องหมายจุลภาค
                        $status_counts_str = implode(', ', $status_counts);

                        ?>
                        // Data from PHP
                        const data = {
                            labels: <?= json_encode($status_labels) ?>,
                            datasets: [{
                                label: 'จำนวนงาน',
                                data: [<?= $status_counts_str ?>],

                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)',
                                    'rgba(255, 206, 86, 0.2)',
                                    'rgba(54, 162, 235, 0.2)',
                                    'rgba(75, 192, 192, 0.2)',
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(255, 206, 86, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                ],
                                borderWidth: 1
                            }]
                        };

                        const options = {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        };

                        new Chart(ctx, {
                            type: 'bar',
                            data: data,
                            options: options
                        });
                        console.log('Labels:', data.labels);
                        console.log('Data:', data.datasets[0].data);
                    });
                </script>
                <hr>
            </div>
            <br>
            <script>
                // Get the current date and time
                const now = new Date();

                // Format the time as HH:mm
                const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

                // Set the current time as the default value for the input fields
                const timeReportInputs = document.querySelectorAll('.time_report');
                timeReportInputs.forEach(input => input.value = currentTime);
            </script>
            <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
            <script>
                $(document).ready(function() {
                    setTimeout(function() {
                        $('#dataAll, #inTime, #dataAllNOTTAKE, #wait, #success').DataTable();
                    }, 1000);
                });
            </script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const filterDateInput = document.getElementById("filter-date");
                    const timelineFilterSelect = document.getElementById("timelineFilter");
                    const ganttChartCanvas = document.getElementById("gantt-summary");
                    let chart;
                    const colorMap = {}; // Stores unique colors for each task type

                    function generateColor(index) {
                        const hue = (index * 137.5) % 360; // Ensures distinct hues
                        return `hsla(${hue}, 90%, 50%, 0.5)`;
                    }

                    // Stores assigned colors
                    function getColor(taskType, index) {
                        if (!colorMap[taskType]) {
                            colorMap[taskType] = generateColor(index);
                        }
                        return colorMap[taskType];
                    }

                    function summarizeTasks(tasks) {
                        const summary = {};

                        tasks.forEach((task) => {
                            const taskStart = new Date(`2023-01-01 ${task.start}`);
                            const taskEnd = new Date(`2023-01-01 ${task.end}`);

                            for (let hour = 7; hour < 18; hour++) {
                                const rangeStart = new Date(`2023-01-01 ${String(hour).padStart(2, "0")}:30`);
                                const rangeEnd = new Date(`2023-01-01 ${String(hour + 1).padStart(2, "0")}:30`);
                                const rangeKey = `${task.name}-${String(hour).padStart(2, "0")}:30 - ${String(hour + 1).padStart(2, "0")}:30`;

                                if (taskEnd > rangeStart && taskStart < rangeEnd) {
                                    const overlap = Math.min(taskEnd, rangeEnd) - Math.max(taskStart, rangeStart);

                                    if (!summary[rangeKey] || overlap > summary[rangeKey].duration) {
                                        summary[rangeKey] = {
                                            name: task.name,
                                            problem: task.problem,
                                            range: `${String(hour).padStart(2, "0")}:30 - ${String(hour + 1).padStart(2, "0")}:30`,
                                            duration: overlap,
                                        };
                                    }
                                }
                            }
                        });

                        return Object.values(summary);
                    }

                    function fetchDataAndRenderChart(date = null, filter = null) {
                        // Clear previous color mapping while keeping the same object reference
                        Object.keys(colorMap).forEach(key => delete colorMap[key]);

                        const url = new URL("fetch_data.php", window.location.href);
                        if (date) url.searchParams.append("date", date);
                        if (filter) url.searchParams.append("filter", filter);

                        fetch(url)
                            .then((response) => response.json())
                            .then((data) => {
                                const summarizedData = summarizeTasks(data);
                                const taskTypes = [...new Set(summarizedData.map(task => task.problem))];
                                const datasets = summarizedData.map((task, index) => ({
                                    x: [
                                        new Date(`2023-01-01 ${task.range.split(" - ")[0]}`),
                                        new Date(`2023-01-01 ${task.range.split(" - ")[1]}`),
                                    ],
                                    y: task.name,
                                    backgroundColor: getColor(task.problem, taskTypes.indexOf(task.problem)), // Assign color based on index
                                    borderColor: getColor(task.problem, taskTypes.indexOf(task.problem)).replace('0.5', '1'),
                                    problem: task.problem,
                                    range: task.range,
                                }));

                                if (chart) {
                                    chart.destroy(); // Destroy previous chart instance before creating a new one
                                }

                                chart = new Chart(ganttChartCanvas, {
                                    type: "bar",
                                    data: {
                                        datasets: [{
                                            label: "Summary Schedule",
                                            data: datasets,
                                            borderWidth: 1,
                                            backgroundColor: datasets.map(d => d.backgroundColor),
                                        }],
                                    },
                                    options: {
                                        // Ensures it fills the container
                                        indexAxis: "y",
                                        scales: {
                                            x: {
                                                type: "time",
                                                position: "top",
                                                time: {
                                                    unit: "minute",
                                                    displayFormats: {
                                                        minute: "HH:mm",
                                                    },
                                                },
                                                min: "2023-01-01 07:30",
                                                max: "2023-01-01 17:30",
                                                ticks: {
                                                    stepSize: 30,
                                                },
                                            },
                                            y: {
                                                type: "category",
                                                reverse: true,
                                            },
                                        },
                                        plugins: {
                                            tooltip: {
                                                callbacks: {
                                                    label: (ctx) => `${ctx.raw.problem} (${ctx.raw.range})`,
                                                },
                                            },
                                            legend: {
                                                display: true,
                                                labels: {
                                                    generateLabels: (chart) => {
                                                        return Object.keys(colorMap).map((taskType) => ({
                                                            text: taskType,
                                                            fillStyle: colorMap[taskType],
                                                            hidden: false,
                                                        }));
                                                    },
                                                },
                                            },
                                        },
                                    },
                                });
                            })
                            .catch((err) => console.error(err));
                    }


                    fetchDataAndRenderChart();

                    filterDateInput.addEventListener("change", function() {
                        fetchDataAndRenderChart(this.value, timelineFilterSelect.value);
                    });

                    timelineFilterSelect.addEventListener("change", function() {
                        fetchDataAndRenderChart(filterDateInput.value || null, this.value);
                    });
                });
            </script>
            <footer class="mt-5 footer mt-auto py-3" style="background: #fff;">
                <marquee class="font-thai" style="font-weight: bold; font-size: 1rem"><span class="text-muted text-center">Design website by นายอภิชน ประสาทศรี , พุฒิพงศ์ ใหญ่แก้ว &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Coding โดย นายอานุภาพ ศรเทียน &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ควบคุมโดย นนท์ บรรณวัฒน์ นักวิชาการคอมพิวเตอร์ ปฏิบัติการ</span>
                </marquee>
            </footer>
            <?php SC5() ?>


</body>

</html>