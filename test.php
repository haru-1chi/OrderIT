<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gantt Chart Summary</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>

<body>
    <div class="d-flex">
        <div class="card p-3 m-4" style="width: 1800px; height: 700px;">
            <input type="date" id="filter-date" class="form-control mb-3" />
            <select id="timelineFilter" class="form-control">
                <option value="problem" selected>Activity Report</option>
                <option value="device">รูปแบบการทำงาน</option>
                <option value="report">อาการรับแจ้ง</option>
                <option value="sla">SLA</option>
            </select>
            <canvas id="summary-chart" width="800" height="100"></canvas>
        </div>
    </div>


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
            const taskColors = {};
            taskTypes.forEach((type, index) => {
                const baseColor = baseColors[index % baseColors.length];
                const [r, g, b] = baseColor;
                taskColors[type] = {
                    backgroundColor: `rgba(${r}, ${g}, ${b}, 0.5)`,
                    borderColor: `rgba(${r}, ${g}, ${b}, 1)`,
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
                                    return `${context.dataset.label}: ${context.raw}`;
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

        // function renderChart(data) {
        //     const summarizedTasks = summarizeTasks(data);

        //     const labels = summarizedTasks.map(item => item.timeRange);
        //     // Function to generate a random color similar to a base color
        //     function getRandomColorFromBase(baseColor, opacity = 0.5) {
        //         const [r, g, b] = baseColor; // Extract the RGB values from the base color
        //         const variation = 30; // Maximum variation for randomness
        //         const randomize = (value) => Math.min(255, Math.max(0, value + Math.floor(Math.random() * (variation * 2) - variation)));
        //         const newR = randomize(r);
        //         const newG = randomize(g);
        //         const newB = randomize(b);
        //         return `rgba(${newR}, ${newG}, ${newB}, ${opacity})`;
        //     }

        //     // Base colors (RGB only)
        //     const baseColors = [
        //         [255, 99, 132], // Similar to 'rgba(255, 99, 132, 0.5)'
        //         [255, 206, 86], // Similar to 'rgba(255, 206, 86, 0.5)'
        //         [54, 162, 235] // Similar to 'rgba(54, 162, 235, 0.5)'
        //     ];

        //     // Function to map unique task types to similar colors
        //     const taskColors = {}; // Store generated colors for each task type
        //     function getTaskColor(taskType, opacity = 0.5) {
        //         if (!taskColors[taskType]) {
        //             const baseColor = baseColors[Math.floor(Math.random() * baseColors.length)];
        //             taskColors[taskType] = getRandomColorFromBase(baseColor, opacity);
        //         }
        //         return taskColors[taskType];
        //     }

        //     // Chart Data with Pantone-like Random Colors
        //     const chartData = {
        //         labels: labels,
        //         datasets: [{
        //             label: 'Task Summary',
        //             data: summarizedTasks.map(item => (item.taskType !== 'Nothing' ? 1 : 0)),
        //             backgroundColor: summarizedTasks.map(item => getTaskColor(item.taskType, 0.5)),
        //             borderColor: summarizedTasks.map(item => getTaskColor(item.taskType, 1).replace(/0\.5\)$/, '1)')),
        //             borderWidth: 1,
        //         }]
        //     };


        //     const config = {
        //         type: 'bar',
        //         data: chartData,
        //         options: {
        //             indexAxis: 'x',
        //             scales: {
        //                 x: {
        //                     position: 'top',
        //                     title: {
        //                         display: true,
        //                         text: 'Time Range',
        //                     },
        //                 },
        //                 y: {
        //                     display: false,
        //                 },
        //             },
        //             plugins: {
        //                 tooltip: {
        //                     callbacks: {
        //                         label: context => {
        //                             const taskType = summarizedTasks[context.dataIndex].taskType;
        //                             return `${taskType}`;
        //                         },
        //                     },
        //                 },
        //             },
        //         },
        //     };

        //     // If chart exists, destroy it before creating a new one
        //     if (chart) {
        //         chart.destroy();
        //     }
        //     const ctx = document.getElementById('summary-chart').getContext('2d');
        //     chart = new Chart(ctx, config);
        // }
        
        // Initialize the chart with event listeners
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


        // Start
        initChart();
    </script>
</body>

</html>