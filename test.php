<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline Gantt Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
</head>
<body>
<canvas id="gantt-chart" width="800" height="400"></canvas>

<script>
document.addEventListener("DOMContentLoaded", function () {
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
                                displayFormats: { hour: 'HH:mm' }
                            },
                            min: '2023-01-01 08:00', // Start time
                            max: '2023-01-01 18:00', // End time
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
                                    const { problem, take, close_date } = ctx.raw;
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

</body>
</html>
