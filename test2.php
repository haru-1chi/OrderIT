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
            <canvas id="ganttChart" width="800" height="100"></canvas>
        </div>
    </div>


    <script>
        // Sample dataset from PHP backend
        const taskData = [{
                id: 1,
                name: "Task A",
                start: "08:30",
                end: "09:45",
                type: "High"
            },
            {
                id: 2,
                name: "Task B",
                start: "09:15",
                end: "10:00",
                type: "Medium"
            },
            {
                id: 3,
                name: "Task C",
                start: "10:00",
                end: "11:30",
                type: "Low"
            }
        ];

        // Function to group tasks into hourly summary
        function groupTasksByHour(tasks) {
            const summary = {};
            const hourRanges = [
                "08:30-09:30", "09:30-10:30", "10:30-11:30"
            ];

            hourRanges.forEach(range => summary[range] = {
                High: 0,
                Medium: 0,
                Low: 0
            });

            tasks.forEach(task => {
                let startTime = task.start;
                let endTime = task.end;

                hourRanges.forEach(range => {
                    const [start, end] = range.split("-");
                    if (startTime <= end && endTime >= start) {
                        summary[range][task.type]++;
                    }
                });
            });
            return summary;
        }

        const summaryData = groupTasksByHour(taskData);

        // Prepare Chart.js dataset
        const chartData = {
            labels: Object.keys(summaryData),
            datasets: [{
                    label: "High",
                    backgroundColor: "red",
                    data: Object.values(summaryData).map(d => d.High)
                },
                {
                    label: "Medium",
                    backgroundColor: "orange",
                    data: Object.values(summaryData).map(d => d.Medium)
                },
                {
                    label: "Low",
                    backgroundColor: "green",
                    data: Object.values(summaryData).map(d => d.Low)
                }
            ]
        };

        const ctx = document.getElementById("ganttChart").getContext("2d");
        new Chart(ctx, {
            type: "bar",
            data: chartData,
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });
    </script>
</body>

</html>