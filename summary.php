<?php
session_start();
require_once 'config/db.php';
require_once 'template/navbar.php';
$dateNow = new DateTime();
$dateNow->modify("+543 years");

$dateThai = $dateNow->format("Y/m/d");

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

            <div class="card p-3 ms-4 me-4 mt-4">
                <p>สรุปความพึงพอใจ</p>
                <div style="width: 600px; height: 80px;">
                    <canvas id="serviceBarChart" style="width: 100%; height: 100%;"></canvas>
                    <canvas id="solvingBarChart" style="width: 100%; height: 100%;"></canvas>
                    <canvas id="averageScoresChart" style="width: 100%; height: 100%;"></canvas>
                </div>
                <div style="width: 600px; height: 150px; margin-top: 150px;">
                    <canvas id="horizontalBarChart" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>

        <div class="d-flex">
            <div class="card p-3 m-4">
                <div class="d-flex justify-content-between">
                    <p>SLA%</p>
                    <select id="pieChartFilter" style="margin-bottom: 15px;">
                        <option value="day" selected>วันนี้</option>
                        <option value="week">สัปดาห์นี้</option>
                        <option value="month">เดือนนี้</option>
                        <option value="year">ปีนี้</option>
                    </select>
                </div>
                <canvas id="pieChart" style="width: 250px; height: 250px;"></canvas>
            </div>

            <div class="card p-3 mt-4" style="width: 700px; height: 400px;">
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
        </div>

        <div class="d-flex">
            <div class="card p-3 m-4" style="width: 1800px; height: 400px;">
                <canvas id="gantt-chart" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-lg-12 col-md-12">
                <h1 class="text-center my-4">อยู่ในระหว่างการสร้างหน้าเว็บนี้...</h1>
                <div class="card rounded-4 shadow-sm p-3 mt-5 col-sm-12 col-lg-12 col-md-12">
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
                                        data: [data.score],
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

                <style>
                    .chart-container {
                        width: 600px;
                        height: 100px;
                    }

                    canvas {
                        width: 100%;
                        height: 100%;
                    }
                </style>
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
                                    'rgba(255, 99, 132, 0.5)',
                                    'rgba(255, 206, 86, 0.5)',
                                    'rgba(54, 162, 235, 0.5)',
                                ],
                                borderColor: [
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
                    fetch('fetch_data.php') // Replace with your actual PHP endpoint
                        .then(response => response.json())
                        .then(data => {
                            // Transform the data for the Gantt chart
                            const labels = [...new Set(data.map(item => item.name))]; // Get unique names

                            // Helper function to generate random colors
                            const randomColor = () => `rgba(${Math.floor(Math.random() * 255)}, 
                                            ${Math.floor(Math.random() * 255)}, 
                                            ${Math.floor(Math.random() * 255)}, 
                                            0.5)`;

                            // Prepare datasets for each task
                            const datasets = data.map(task => ({
                                x: [new Date(`2023-01-01 ${task.start}`), new Date(`2023-01-01 ${task.end}`)],
                                y: task.name,
                                backgroundColor: randomColor(), // Generate a random color for each task
                                problem: task.problem, // Add problem type for tooltips
                                take: task.start, // Start time in 24-hour format
                                close_date: task.end // End time in 24-hour format
                            }));

                            // Gantt chart configuration
                            new Chart(document.getElementById('gantt-chart'), {
                                type: 'bar',
                                data: {
                                    datasets: [{
                                        label: 'Timeline',
                                        data: datasets,
                                        borderWidth: 1, // Keep border consistent for visibility
                                    }],
                                },
                                options: {
                                    indexAxis: 'y',
                                    scales: {
                                        x: {
                                            type: 'time',
                                            time: {
                                                unit: 'hour',
                                                displayFormats: {
                                                    hour: 'HH:mm'
                                                }
                                            },
                                            min: '2023-01-01 08:00', // Start time
                                            max: '2023-01-01 17:00', // End time
                                        },
                                        y: {
                                            type: 'category',
                                            reverse: true // Reverse for better readability
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
                                                    return `${problem} (${take} - ${close_date})`;
                                                }
                                            }
                                        }
                                    }
                                },
                            });
                        })
                        .catch(err => console.error(err));
                });
            </script>
            <footer class="mt-5 footer mt-auto py-3" style="background: #fff;">

                <marquee class="font-thai" style="font-weight: bold; font-size: 1rem"><span class="text-muted text-center">Design website by นายอภิชน ประสาทศรี , พุฒิพงศ์ ใหญ่แก้ว &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Coding โดย นายอานุภาพ ศรเทียน &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ควบคุมโดย นนท์ บรรณวัฒน์ นักวิชาการคอมพิวเตอร์ ปฏิบัติการ</span>
                </marquee>

            </footer>
            <?php SC5() ?>


</body>

</html>