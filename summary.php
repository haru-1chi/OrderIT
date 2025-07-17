<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
$dateNow = new DateTime();
$dateThai = $dateNow->format("Y/m/d");

//ถ้าไม่พบรายการสำหรับ export
$exportError = $_SESSION['export_error'] ?? null;
unset($_SESSION['export_error']);

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

        // // Set timeout to refresh the page every 1 minute (60000 milliseconds)
        // setTimeout(refreshPage, 30000);
    </script>
    <style>
        body {
            background-color: #F9FDFF;
        }

        #dataAll tbody tr td {
            background-color: #f2f7ff;
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
    <?php navbar() ?>
    <div class="">
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

        <div class="d-flex">
            <div class="card p-3 m-4" style="width: 500px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>สรุปยอดการรับงาน</p>
                    <select id="filterDropdown" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <canvas id="pairedChart" style="width: 100%; height: 100%;"></canvas>
            </div>
            <div class="card p-3 mt-4" style="width: 700px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>แนวโน้มงานรายวัน</p>
                    <select id="filterDropdown1" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <canvas id="lineChart" style="width: 100%; height: 100%;"></canvas>
            </div>
            <div class="card p-3 ms-4 me-4 mt-4" style="width: 700px; height: 400px;">
                <p>สรุปความพึงพอใจ</p>
                <div style="display: flex; flex-direction: column; align-items: center; width: 600px; height: 80px;">
                    <canvas id="serviceBarChart" style="width: 100%; height: 100%;"></canvas>
                    <canvas id="solvingBarChart" style="width: 100%; height: 100%;"></canvas>
                    <!-- <div style="width: 600px; height: 60px;">
                        <canvas id="averageScoresChart" style="width: 100%; height: 100%;"></canvas>
                    </div> -->
                </div>
                <div style="width: 600px; height: 150px; margin-top: 100px;">
                    <canvas id="horizontalBarChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>

        <div class="d-flex">
            <div class="card p-3 mt-2 me-4 ms-4" style="width: 500px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>SLA%</p>
                    <select id="pieChartFilter" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <div class="d-flex justify-content-center align-items-center" style="margin-top: 20px;">
                    <canvas id="pieChart" style="width: 275px; height: 275px;"></canvas>
                </div>
            </div>

            <div class="card p-3 mt-2" style="width: 700px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>งานที่ใช้เวลานานเกิน SLA โดยเฉลี่ย</p>
                    <select id="avgSLAFilter" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <canvas id="avgSLAChart" style="width: 100%; height: 100%;"></canvas>
            </div>

            <div class="card p-3 mt-2 me-4 ms-4" style="width: 600px; height: 400px;">
                <div class="d-flex justify-content-between">
                    <p>ประเภทปัญหาที่พบ</p>
                    <div>
                        <select id="taskChartFilter" style="margin-bottom: 15px;">
                            <option value="problem" selected>Activity Report</option>
                            <option value="device">รูปแบบการทำงาน</option>
                            <option value="report">อาการรับแจ้ง</option>
                            <option value="sla">SLA</option>
                        </select>
                        <select id="filterTaskDropdown" style="margin-bottom: 15px;">
                            <option value="day" selected>วันนี้</option>
                            <option value="week">สัปดาห์นี้</option>
                            <option value="month">เดือนนี้</option>
                            <option value="year">ปีนี้</option>
                        </select>
                        <select id="personChartFilter" style="margin-bottom: 15px;">
                            <option value="all" selected>ทุกคน</option>

                            <?php
                            // Fetch all admin usernames
                            $sql = "SELECT * FROM admin";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // First, show the logged-in admin's username
                            foreach ($checkD as $d) {
                                if ($d['username'] === $_SESSION['admin_log']) {
                                    echo "<option value='{$d['username']}'>{$d['username']}</option>";
                                }
                            }

                            // Then, show other usernames
                            foreach ($checkD as $d) {
                                if ($d['username'] !== $_SESSION['admin_log']) {
                                    echo "<option value='{$d['username']}'>{$d['username']}</option>";
                                }
                            }
                            ?>
                        </select>

                    </div>
                </div>
                <div class="d-flex justify-content-center align-items-center">
                    <canvas id="taskChart" style="width: 500px; height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="card p-3 m-4" style="width: 1850px; height: 1325px;">
            <!-- 1850px 1110px -->
            <input type="date" id="filter-date" class="form-control mb-3" />
            <select id="timelineFilter" class="form-control">
                <option value="problem" selected>Activity Report</option>
                <option value="device">รูปแบบการทำงาน</option>
                <option value="report">อาการรับแจ้ง</option>
                <option value="sla">SLA</option>
            </select>
            <div class="d-flex justify-content-end mt-2">
                <button id="prev-date" class="btn btn-outline-primary me-2">Previous</button>
                <button id="next-date" class="btn btn-outline-primary">Next</button>
            </div>

            <canvas id="gantt-chart" width="800" height="150"></canvas>
            <canvas id="gantt-summary" width="800" height="150"></canvas>

            <hr />
            <div class="row">
                <div class="col ">
                    <h5 class="mb-3">Export รายงานการปฏิบัติงานรายวัน</h5>
                    <form method="POST" action="export.php">
                        <div class="d-flex mb-2">
                            <input class="form-control me-2" type="date" name="date" value="<?= date('Y-m-d') ?>" />
                            <select name="filter" class="form-control">
                                <option value="problem" selected>Activity Report</option>
                                <option value="device">รูปแบบการทำงาน</option>
                                <option value="report">อาการรับแจ้ง</option>
                                <option value="sla">SLA</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="activity" class="btn btn-primary">Export to Excel</button>
                        </div>
                    </form>
                </div>
                <div class="col pb-3">
                    <h5 class="mb-3">Export รายงานการปฏิบัติงานรายบุคคลตามช่วงเวลา</h5>
                    <form method="POST" action="export.php">
                        <div class="row mb-2">
                            <div class="col">
                                <label><input class="form-check-input me-2" type="radio" name="date_filter_type" value="period" checked onclick="toggleDateInputs()"> ตามช่วงเวลา</label>
                            </div>
                            <div class="col">
                                <select id="period_select" name="period" class="form-control">
                                    <option value="week">สัปดาห์นี้</option>
                                    <option value="month">เดือนนี้</option>
                                    <option value="year">ปีนี้</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <label><input class="form-check-input me-2" type="radio" name="date_filter_type" value="custom" onclick="toggleDateInputs()"> กำหนดเอง</label>
                            </div>
                            <div class="col d-flex">
                                <input class="form-control me-2" type="date" name="start_date" id="start_date" disabled />
                                <input class="form-control" type="date" name="end_date" id="end_date" disabled />
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col"></div>
                            <div class="col">
                                <select name="filter" class="form-control">
                                    <option value="problem" selected>Activity Report</option>
                                    <option value="device">รูปแบบการทำงาน</option>
                                    <option value="report">อาการรับแจ้ง</option>
                                    <option value="sla">SLA</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col"></div>
                            <div class="col">
                                <select name="username" style="margin-bottom: 15px;" class="form-control">
                                    <option value="all" selected>ทุกคน</option>
                                    <?php
                                    $sql = "SELECT * FROM admin";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute();
                                    $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($checkD as $d) {
                                        echo "<option value='{$d['username']}'>{$d['username']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="activity_username" class="btn btn-primary">Export to Excel</button>
                        </div>
                    </form>
                </div>

            </div>
            <div class="row">
                <div class="col"></div>
                <div class="col">
                    <h5 class="mb-3">Export รายงานสรุปการปฏิบัติงาน</h5>
                    <form method="POST" action="export.php">
                        <div class="row mb-2">
                            <!-- <div class="col">
                                <label><input class="form-check-input me-2" type="radio" name="date_filter_type" value="period" checked onclick="toggleDateInputs()">รายเดือน</label>
                            </div> -->

                            <div class="col">
                                <input class="form-control" type="month" name="month" id="month" value="<?= date('Y-m') ?>" />
                            </div>
                            <div class="col">
                                <select name="filter" class="form-control">
                                    <option value="problem" selected>Activity Report</option>
                                    <option value="device">รูปแบบการทำงาน</option>
                                    <option value="report">อาการรับแจ้ง</option>
                                    <option value="sla">SLA</option>
                                </select>
                            </div>
                            <div class="col">
                                <select name="username" style="margin-bottom: 15px;" class="form-control">
                                    <option value="all" selected>ทุกคน</option>
                                    <?php
                                    $sql = "SELECT * FROM admin";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->execute();
                                    $checkD = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($checkD as $d) {
                                        echo "<option value='{$d['username']}'>{$d['username']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="row mb-2">
                            <div class="col">
                                <label><input class="form-check-input me-2" type="radio" name="date_filter_type" value="period" checked onclick="toggleDateInputs()">รายปี</label>
                            </div>
                            <div class="col d-flex">
                                <input class="form-control me-2" type="year" name="year" id="year" value="<?= date('Y') ?>" />
                            </div>
                        </div> -->

                        <div class="d-flex justify-content-end">
                            <button type="submit" name="activity_usernames" class="btn btn-primary">Export to Excel</button>
                        </div>
                    </form>
                </div>

            </div>

            <?php if ($exportError): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    Swal.fire({
                        icon: 'warning',
                        title: 'ไม่พบรายการ',
                        text: '<?= $exportError ?>',
                    });
                </script>
            <?php endif; ?>

            <script>
                function toggleDateInputs() {
                    const isCustom = document.querySelector('input[name="date_filter_type"][value="custom"]').checked;

                    // Toggle period select
                    document.getElementById('period_select').disabled = isCustom;

                    // Toggle start and end date
                    document.getElementById('start_date').disabled = !isCustom;
                    document.getElementById('end_date').disabled = !isCustom;
                }
            </script>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
        <script>
            let chart; // To hold the Chart.js instance

            // Fetch data from backend
            async function fetchChartData(date, filter) {
                const response = await fetch(`fetch_data.php?date=${date}&filter=${filter}`);
                const data = await response.json();
                return data;
            }

            // Map fetched data to chart format
            function mapDataToChartFormat(fetchedData) {
                const filteredData = fetchedData.filter(row => row.name.toLowerCase() !== 'achirayaj');
                return filteredData.map(row => ({
                    name: row.name,
                    tasks: [{
                        type: row.problem, // "problem" column maps to task type
                        start: row.start,
                        end: row.end,
                    }],
                }));
            }

            // Create or update the chart
            function renderChart(data) {
                const summarizedTasks = summarizeTasks(data);

                const labels = summarizedTasks.map(item => item.timeRange);

                // Unique task types
                const taskTypes = [...new Set(summarizedTasks.map(item => item.taskType).filter(type => type !== 'Nothing'))];

                // Base colors (RGB only)
                const baseColors = [
                    [255, 99, 132], // Similar to 'rgba(255, 99, 132, 0.5)'
                    [255, 206, 86], // Similar to 'rgba(255, 206, 86, 0.5)'
                    [54, 162, 235], // Similar to 'rgba(54, 162, 235, 0.5)'
                ];

                // Generate unique colors for each task type

                function generateColor(index) {
                    const hue = (index * 60) % 360; // Unique hue
                    return `hsla(${hue}, 90%, 60%, 0.5)`;
                }

                const taskColors = {};
                taskTypes.forEach((type, index) => {
                    const baseColor = baseColors[index % baseColors.length];
                    const [r, g, b] = baseColor;
                    taskColors[type] = {
                        backgroundColor: generateColor(index),
                        borderColor: generateColor(index).replace('0.5', '1'),
                    };
                });

                // Generate datasets for each task type
                const datasets = taskTypes.map(taskType => {
                    const data = summarizedTasks.map(item => (item.taskType === taskType ? 1 : 0));
                    return {
                        label: taskType, // Legend will now show task type
                        data: data,
                        backgroundColor: taskColors[taskType].backgroundColor,
                        borderColor: taskColors[taskType].borderColor,
                        borderWidth: 1,
                    };
                });

                // Chart Data
                const chartData = {
                    labels: labels,
                    datasets: datasets, // Multiple datasets
                };

                const config = {
                    type: 'bar',
                    data: chartData,
                    options: {
                        indexAxis: 'x',
                        scales: {
                            x: {
                                stacked: true,
                                position: 'top',
                                title: {
                                    display: true,
                                    text: 'Time Range',
                                },
                            },
                            y: {
                                stacked: true,
                                display: false,
                            },
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: context => {
                                        return `${context.dataset.label}`;
                                    },
                                },
                            },
                        },
                    },
                };

                // If chart exists, destroy it before creating a new one
                if (chart) {
                    chart.destroy();
                }
                const ctx = document.getElementById('summary-chart').getContext('2d');
                chart = new Chart(ctx, config);
            }

            async function initChart() {
                const dateInput = document.getElementById('filter-date');
                const filterSelect = document.getElementById('timelineFilter');

                async function updateChart() {
                    const selectedDate = dateInput.value || new Date().toISOString().split('T')[0];
                    const selectedFilter = filterSelect.value;
                    const fetchedData = await fetchChartData(selectedDate, selectedFilter);
                    const ganttData = mapDataToChartFormat(fetchedData);
                    renderChart(ganttData);
                }

                dateInput.addEventListener('change', updateChart);
                filterSelect.addEventListener('change', updateChart);

                // Initial chart load
                await updateChart();
            }

            // Utility functions
            function timeToMinutes(time) {
                const [hours, minutes] = time.split(':').map(Number);
                return hours * 60 + minutes;
            }

            function minutesToTime(minutes) {
                const h = Math.floor(minutes / 60);
                const m = minutes % 60;
                return `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}`;
            }

            function summarizeTasks(data) {
                const timeBuckets = []; // Initialize hourly time buckets between 8:30 and 16:30

                for (let start = 8 * 60 + 30; start <= 16 * 60 + 30; start += 60) {
                    timeBuckets.push({
                        start,
                        end: start + 60,
                        tasks: {}
                    });
                }

                // Analyze each person's tasks
                data.forEach(person => {
                    person.tasks.forEach(task => {
                        const taskStart = timeToMinutes(task.start);
                        const taskEnd = timeToMinutes(task.end);

                        timeBuckets.forEach(bucket => {
                            if (taskStart < bucket.end && taskEnd > bucket.start) {
                                const overlapStart = Math.max(taskStart, bucket.start);
                                const overlapEnd = Math.min(taskEnd, bucket.end);
                                const overlapDuration = overlapEnd - overlapStart;

                                if (overlapDuration > 0) {
                                    bucket.tasks[task.type] = (bucket.tasks[task.type] || 0) + overlapDuration;
                                }
                            }
                        });
                    });
                });

                return timeBuckets.map(bucket => {
                    // Sort tasks by total time (primary) and number of participants (secondary)
                    const sortedTasks = Object.entries(bucket.tasks)
                        .sort((a, b) => b[1] - a[1]); // Sort by total duration descending

                    const taskType = sortedTasks.length ?
                        sortedTasks[0][0] // Select the task type with the longest duration
                        :
                        'Nothing'; // Default when no tasks are present

                    return {
                        timeRange: `${minutesToTime(bucket.start)}-${minutesToTime(bucket.end)}`,
                        taskType
                    };
                });
            }

            function adjustDate(days) {
                const dateInput = document.getElementById('filter-date');
                const currentDate = dateInput.value ? new Date(dateInput.value) : new Date();
                currentDate.setDate(currentDate.getDate() + days);

                // Format date as YYYY-MM-DD
                const newDate = currentDate.toISOString().split('T')[0];
                dateInput.value = newDate;

                // Trigger change event to refresh chart
                const event = new Event('change');
                dateInput.dispatchEvent(event);
            }

            document.getElementById('prev-date').addEventListener('click', () => adjustDate(-1));
            document.getElementById('next-date').addEventListener('click', () => adjustDate(1));

            // Start
            initChart();
        </script>

        <div class="">
            <div class="card rounded-4 shadow-sm p-3 ms-4 me-4">
                <h1>รายการความพึงพอใจ</h1>
                <div class="table-responsive">
                    <table id="dataAll" class="table table-primary">
                        <thead>
                            <tr class="text-center">
                                <th scope="col">ช่องทางที่ใช้บริการ</th>
                                <th scope="col">ปัญหาได้รับการแก้ไข</th>
                                <th scope="col">ความรวดเร็ว</th>
                                <th scope="col">การแก้ปัญหา</th>
                                <th scope="col">การให้บริการ</th>
                                <th scope="col">ข้อเสนอแนะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT *
                    FROM rating
                    ";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute();
                            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                            ?>
                                <tr>
                                    <td><?= $row['service_channel'] ?></td>
                                    <td><?= $row['issue_resolved'] ?></td>
                                    <td><?= $row['service_speed'] ?></td>
                                    <td><?= $row['problem_satisfaction'] ?></td>
                                    <td><?= $row['service_satisfaction'] ?></td>
                                    <td><?= $row['suggestion'] ?></td>
                                </tr>
                            <?php }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const horizontalChartCanvas = document.getElementById('averageScoresChart');
                    let horizontalChartInstance;

                    function fetchAverageScores() {
                        return fetch('fetch_total_score.php', {
                                method: 'GET',
                            })
                            .then((response) => {
                                if (!response.ok) throw new Error('Network error');
                                return response.json();
                            })
                            .catch((error) => console.error('Error fetching data:', error));
                    }

                    function createHorizontalChart(canvas, data) {
                        return new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: [data.label],
                                datasets: [{
                                    label: 'คะแนนเฉลี่ย',
                                    data: data.score,
                                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1,
                                }, ],
                            },
                            options: {
                                indexAxis: 'y', // Horizontal bar
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false,
                                        position: 'top',
                                    },
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,

                                        },
                                        beginAtZero: true,
                                        max: 5,
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                        },
                                    },
                                },
                            },
                        });
                    }

                    fetchAverageScores().then((data) => {
                        if (data.error) {
                            console.error('Error:', data.error);
                            return;
                        }
                        if (!horizontalChartInstance) {
                            horizontalChartInstance = createHorizontalChart(horizontalChartCanvas, data);
                        } else {
                            horizontalChartInstance.data.labels = [data.label];
                            horizontalChartInstance.data.datasets[0].data = [data.score];
                            horizontalChartInstance.update();
                        }
                    });
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const endpoints = {
                        service: 'fetch_service_channel.php',
                        solving: 'fetch_solving.php',
                    };

                    const chartConfigs = {
                        serviceBarChart: {
                            title: 'ช่องทางการให้บริการ',
                        },
                        solvingBarChart: {
                            title: 'ปัญหาได้รับการแก้ไข',
                        },
                    };

                    // Initialize charts
                    Object.entries(chartConfigs).forEach(([chartId, config]) => {
                        initializeChart(chartId, endpoints[chartId.split('Bar')[0]], config);
                    });
                });

                // Function to fetch data from API
                async function fetchData(url) {
                    try {
                        const response = await fetch(url);
                        if (!response.ok) throw new Error('Failed to fetch data');
                        return await response.json();
                    } catch (error) {
                        console.error(error);
                        alert('Error fetching chart data. Please try again.');
                        return null;
                    }
                }

                // Function to initialize a chart
                async function initializeChart(chartId, endpoint, config) {
                    const data = await fetchData(endpoint);
                    if (!data) return;

                    const {
                        labels,
                        data: chartData
                    } = data;
                    const datasets = labels.map((label, index) => ({
                        label,
                        data: [chartData[index]],
                        backgroundColor: getColor(index, 0.5),
                        borderColor: getColor(index, 1),
                        borderWidth: 1,
                    }));

                    const ctx = document.getElementById(chartId).getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [config.title],
                            datasets,
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    align: 'end',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (tooltipItem) =>
                                            `${tooltipItem.dataset.label}: ${tooltipItem.raw}%`,
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    ticks: {
                                        beginAtZero: true,
                                        stepSize: 10,
                                    },
                                },
                                y: {
                                    stacked: true,
                                },
                            },
                        },
                    });
                }

                // Function to generate colors
                function getColor(index, alpha) {
                    const colors = [
                        `rgba(255, 99, 132, ${alpha})`,
                        `rgba(75, 192, 192, ${alpha})`,
                        `rgba(54, 162, 235, ${alpha})`,
                        `rgba(255, 206, 86, ${alpha})`,
                        `rgba(153, 102, 255, ${alpha})`,
                    ];
                    return colors[index % colors.length];
                }
            </script>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const endpoint = "fetch_avg_sla.php"; // Backend endpoint
                    const canvas = document.getElementById("avgSLAChart");
                    const filterDropdown = document.getElementById("avgSLAFilter");
                    let chartInstance;

                    // Fetch data from the backend
                    function fetchData(filter) {
                        return fetch(endpoint, {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify({
                                    filter
                                }),
                            })
                            .then((response) => {
                                if (!response.ok) throw new Error("Network error");
                                return response.json();
                            })
                            .catch((error) => console.error("Error fetching data:", error));
                    }

                    // Create or update the chart
                    function updateChart(data) {
                        const chartData = {
                            labels: data.labels,
                            datasets: [{
                                    label: "เวลาที่กำหนด",
                                    data: data.in_time_values,
                                    borderColor: "rgba(54, 162, 235, 1)",
                                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                                    fill: false,
                                    tension: 0.3,
                                },
                                {
                                    label: "เวลาที่ใช้",
                                    data: data.avg_time_values,
                                    borderColor: "rgba(255, 99, 132, 1)",
                                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                                    fill: false,
                                    tension: 0.3,
                                },
                            ],
                        };

                        const chartOptions = {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: "top",
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let value = context.raw || 0;
                                            return `${value} นาที`;
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                },
                            },
                        };

                        if (chartInstance) {
                            chartInstance.destroy(); // Destroy existing chart instance
                        }

                        chartInstance = new Chart(canvas, {
                            type: "line",
                            data: chartData,
                            options: chartOptions,
                        });
                    }

                    // Event listener for dropdown change
                    filterDropdown.addEventListener("change", function() {
                        const selectedFilter = this.value;
                        fetchData(selectedFilter).then(updateChart);
                    });

                    // Initial load with default filter
                    fetchData(filterDropdown.value).then(updateChart);
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const charts = [{
                            canvasId: 'pairedChart',
                            dropdownId: 'filterDropdown',
                            endpoint: 'fetch_staff.php',
                            type: 'bar',
                            datasets: (data) => [{
                                    label: 'จำนวนรับงาน',
                                    data: data.taker_counts,
                                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    borderWidth: 1,
                                },
                                {
                                    label: 'จำนวนคีย์งาน',
                                    data: data.creator_counts,
                                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1,
                                },
                            ],
                        },
                        {
                            canvasId: 'lineChart',
                            dropdownId: 'filterDropdown1',
                            endpoint: 'fetch_count_task.php',
                            type: 'line',
                            datasets: (data) => [{
                                label: 'จำนวนงาน',
                                data: data.task_counts,
                                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                fill: false,
                                tension: 0.2,
                            }, ],
                        },
                    ];

                    function fetchData(endpoint, filter) {
                        return fetch(endpoint, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    filter
                                }),
                            })
                            .then((response) => {
                                if (!response.ok) throw new Error('Network error');
                                return response.json();
                            })
                            .catch((error) => console.error('Error fetching data:', error));
                    }

                    function createChart(canvas, type, data, datasetsFn) {
                        return new Chart(canvas, {
                            type: type,
                            data: {
                                labels: data.labels,
                                datasets: datasetsFn(data),
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top'
                                    },
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true
                                        }
                                    },
                                    y: {
                                        beginAtZero: true
                                    },
                                },
                            },
                        });
                    }

                    charts.forEach(({
                        canvasId,
                        dropdownId,
                        endpoint,
                        type,
                        datasets
                    }) => {
                        const canvas = document.getElementById(canvasId);
                        const dropdown = document.getElementById(dropdownId);
                        let chartInstance;

                        function updateChart(filter) {
                            fetchData(endpoint, filter).then((data) => {
                                if (!chartInstance) {
                                    chartInstance = createChart(canvas, type, data, datasets);
                                } else {
                                    chartInstance.data.labels = data.labels;
                                    chartInstance.data.datasets = datasets(data);
                                    chartInstance.update();
                                }
                            });
                        }

                        dropdown.addEventListener('change', () => updateChart(dropdown.value));
                        updateChart('day');
                    });
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const pieChartCanvas = document.getElementById('taskChart');
                    const taskChartFilter = document.getElementById('taskChartFilter');
                    const filterDropdown = document.getElementById('filterTaskDropdown');
                    const personChartFilter = document.getElementById('personChartFilter');
                    if (!pieChartCanvas) {
                        console.error("Canvas element #taskChart not found.");
                        return;
                    }

                    let pieChartInstance;

                    function fetchPieData(filter, username, period) {
                        return fetch(`fetch_problem_counts.php?filter=${filter}&username=${username}&period=${period}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log("API Response:", data);
                                return data;
                            })
                            .catch(error => console.error('Error fetching pie chart data:', error));
                    }

                    function updatePieChart() {
                        const filter = taskChartFilter.value;
                        const username = personChartFilter.value;
                        const period = filterDropdown.value;

                        fetchPieData(filter, username, period).then(data => {
                            if (!data.labels || !data.counts) {
                                console.error("Invalid data received:", data);
                                return;
                            }

                            const chartData = {
                                labels: data.labels,
                                datasets: [{
                                    data: data.counts,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)',
                                        'rgba(255, 206, 86, 0.5)', 'rgba(75, 192, 192, 0.5)',
                                        'rgba(153, 102, 255, 0.5)', 'rgba(255, 159, 64, 0.5)',
                                        'rgba(0, 204, 102, 0.5)', 'rgba(204, 0, 102, 0.5)' // Added two new colors
                                    ],
                                    borderColor: [
                                        'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)',
                                        'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)',
                                        'rgba(0, 204, 102, 1)', 'rgba(204, 0, 102, 1)' // Added two new colors
                                    ],
                                    borderWidth: 1
                                }]
                            };

                            if (!pieChartInstance) {
                                pieChartInstance = new Chart(pieChartCanvas.getContext('2d'), {
                                    type: 'pie',
                                    data: chartData,
                                    options: {
                                        responsive: false,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'right'
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                                        const value = context.raw;
                                                        const percentage = ((value / total) * 100).toFixed(2);
                                                        return `${value} งาน, ${percentage}%`;
                                                    }
                                                }
                                            }

                                        }
                                    }
                                });
                            } else {
                                pieChartInstance.data = chartData;
                                pieChartInstance.update();
                            }
                        });
                    }

                    // Initial load
                    updatePieChart();

                    // Event listener for dropdown change
                    taskChartFilter.addEventListener('change', updatePieChart);
                    filterDropdown.addEventListener('change', updatePieChart);
                    personChartFilter.addEventListener('change', updatePieChart);
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const pieChartCanvas = document.getElementById('pieChart');
                    const pieChartDropdown = document.getElementById('pieChartFilter');
                    let pieChartInstance;

                    function fetchPieData(filter) {
                        return fetch('fetch_percentage_sla.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    filter
                                })
                            })
                            .then(response => response.json())
                            .catch(error => console.error('Error fetching pie chart data:', error));
                    }

                    function updatePieChart(filter) {
                        fetchPieData(filter).then(data => {
                            const chartData = {
                                labels: ['เสร็จภายในเวลา', 'เกินเวลาที่กำหนด'],
                                datasets: [{
                                    data: [data.in_time, data.over_time],
                                    backgroundColor: ['rgba(75, 192, 192, 0.5)', 'rgba(255, 99, 132, 0.5)'],
                                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                                    borderWidth: 1
                                }]
                            };

                            if (!pieChartInstance) {
                                pieChartInstance = new Chart(pieChartCanvas, {
                                    type: 'pie',
                                    data: chartData,
                                    options: {
                                        responsive: false,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                position: 'bottom'
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        // Format the value with a '%' symbol
                                                        let value = context.raw || 0;
                                                        return `${context.label}: ${value}%`;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            } else {
                                pieChartInstance.data = chartData;
                                pieChartInstance.update();
                            }
                        });
                    }

                    pieChartDropdown.addEventListener('change', () => updatePieChart(pieChartDropdown.value));
                    updatePieChart('day');
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const chartData = {
                        canvasId: 'horizontalBarChart',
                        endpoint: 'fetch_average_scores.php', // API to retrieve the averages
                        type: 'bar',
                        datasets: (data) => [{
                            label: 'คะแนนเฉลี่ย',
                            data: data.scores,
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.5)',
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(255, 206, 86, 0.5)',
                                'rgba(54, 162, 235, 0.5)',
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(54, 162, 235, 1)',
                            ],
                            borderWidth: 1,
                        }, ],
                    };

                    const canvas = document.getElementById(chartData.canvasId);

                    function fetchChartData(endpoint) {
                        return fetch(endpoint, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                            })
                            .then((response) => response.json())
                            .catch((error) => console.error('Error fetching data:', error));
                    }

                    function createHorizontalBarChart(canvas, data, datasetsFn) {
                        return new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: datasetsFn(data),
                            },
                            options: {
                                indexAxis: 'y', // Horizontal bar chart
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        max: 5,
                                    },
                                },
                            },
                        });
                    }

                    fetchChartData(chartData.endpoint).then((data) => {
                        createHorizontalBarChart(canvas, data, chartData.datasets);
                    });
                });
            </script>
            <script>
                // Get the current date and time
                const now = new Date();

                // Format the time as HH:mm
                const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;

                // Set the current time as the default value for the input fields
                const timeReportInputs = document.querySelectorAll('.time_report');
                timeReportInputs.forEach(input => input.value = currentTime);
            </script>
            <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('#dataAll').DataTable({
                        order: [
                            [0, 'asc']
                        ] // assuming you want to sort the first column in ascending order
                    });
                    $('#dataAllNOTTAKE').DataTable({
                        order: [
                            [0, 'asc']
                        ] // assuming you want to sort the first column in ascending order
                    });

                    $('#dataAllTAKE').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });

                    $('#inTime').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });

                    $('#wait').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });

                    $('#success').DataTable({
                        order: [
                            [0, 'desc']
                        ],
                        columnDefs: [{
                                targets: 0,
                                width: "auto"
                            }, // หมายเลขงาน
                            {
                                targets: 1,
                                width: "170px"
                            }, // ผู้ซ่อม
                            {
                                targets: 2,
                                width: "auto"
                            }, // อาการที่ได้รับแจ้ง
                            {
                                targets: 3,
                                width: "170px"
                            }, // หน่วยงาน
                            {
                                targets: 4,
                                width: "170px"
                            }, // SLA
                            {
                                targets: 5,
                                width: "120px"
                            }, // ตัวชี้วัด
                            {
                                targets: 6,
                                width: "80px"
                            }, // เวลาแจ้ง
                            {
                                targets: 7,
                                width: "80px"
                            }, // เวลารับงาน
                            {
                                targets: 8,
                                width: "80px"
                            }, // เวลาปิดงาน
                        ],
                        scrollX: true, // Allow horizontal scrolling if necessary
                        paging: true, // Enable pagination
                        searching: true,
                        autoWidth: false
                    });
                    $('#clam').DataTable({
                        order: [
                            [0, 'desc']
                        ] // adjust the column index as needed
                    });
                });
            </script>

            <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const filterDateInput = document.getElementById("filter-date");
                    const timelineFilterSelect = document.getElementById("timelineFilter");
                    const ganttChartCanvas = document.getElementById("gantt-chart");
                    let chart; // Placeholder for the chart instance

                    // Function to fetch and update the chart data
                    function fetchDataAndRenderChart(date = null, filter = null) {
                        const url = new URL('fetch_data.php', window.location.href);
                        if (date) url.searchParams.append('date', date);
                        if (filter) url.searchParams.append('filter', filter);

                        fetch(url)
                            .then(response => response.json())
                            .then(data => {
                                const datasets = data.map(task => ({
                                    x: [new Date(`2023-01-01 ${task.start}`), new Date(`2023-01-01 ${task.end}`)],
                                    y: task.name,
                                    backgroundColor: `rgba(${Math.floor(Math.random() * 255)}, 
                                                ${Math.floor(Math.random() * 255)}, 
                                                ${Math.floor(Math.random() * 255)}, 0.5)`,
                                    problem: task.problem,
                                    take: task.start,
                                    close_date: task.end,
                                }));

                                // Render or update the chart
                                if (chart) {
                                    chart.data.datasets[0].data = datasets;
                                    chart.update();
                                } else {
                                    chart = new Chart(ganttChartCanvas, {
                                        type: 'bar',
                                        data: {
                                            datasets: [{
                                                label: 'Timeline',
                                                data: datasets,
                                                borderWidth: 1,
                                            }],
                                        },
                                        options: {
                                            indexAxis: 'y',
                                            scales: {
                                                x: {
                                                    type: 'time',
                                                    position: 'top',
                                                    time: {
                                                        unit: 'minute',
                                                        displayFormats: {
                                                            minute: 'HH:mm'
                                                        }
                                                    },
                                                    min: '2023-01-01 07:30',
                                                    max: '2023-01-01 17:30',
                                                    ticks: {
                                                        stepSize: 30,
                                                    }
                                                },
                                                y: {
                                                    type: 'category',
                                                    reverse: true,
                                                },
                                            },
                                            plugins: {
                                                tooltip: {
                                                    callbacks: {
                                                        label: (ctx) => {
                                                            const {
                                                                problem,
                                                                take,
                                                                close_date
                                                            } = ctx.raw;
                                                            const formatTime = (time) => {
                                                                const [hours, minutes] = time.split(":");
                                                                return `${hours}:${minutes}`;
                                                            };
                                                            return `${problem} (${formatTime(take)} น. - ${formatTime(close_date)} น.)`;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                }
                            })
                            .catch(err => console.error(err));
                    }

                    // Initial chart render
                    fetchDataAndRenderChart();

                    // Event listener for date input
                    filterDateInput.addEventListener("change", function() {
                        const selectedDate = this.value;
                        const selectedFilter = timelineFilterSelect.value;
                        fetchDataAndRenderChart(selectedDate, selectedFilter);
                    });

                    // Event listener for dropdown filter
                    timelineFilterSelect.addEventListener("change", function() {
                        const selectedFilter = this.value;
                        const selectedDate = filterDateInput.value || null;
                        fetchDataAndRenderChart(selectedDate, selectedFilter);
                    });
                });

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
            <script>
                // Automatically set the default date to today
                document.addEventListener("DOMContentLoaded", function() {
                    const filterDateInput = document.getElementById("filter-date");

                    // Get today's date in YYYY-MM-DD format
                    const today = new Date().toISOString().split("T")[0];

                    // Set the value of the date input to today's date
                    filterDateInput.value = today;
                });
            </script>
            <footer class="mt-5 footer mt-auto py-3" style="background: #fff;">

                <marquee class="font-thai" style="font-weight: bold; font-size: 1rem"><span class="text-muted text-center">Design website by นายอภิชน ประสาทศรี , พุฒิพงศ์ ใหญ่แก้ว &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Coding โดย นายอานุภาพ ศรเทียน &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ควบคุมโดย นนท์ บรรณวัฒน์ นักวิชาการคอมพิวเตอร์ ปฏิบัติการ</span>
                </marquee>

            </footer>
            <?php SC5() ?>


</body>

</html>