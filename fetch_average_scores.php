<?php
require_once 'config/db.php';

// Query to calculate averages
$sql = "
    WITH AverageScores AS (
        SELECT ROUND(AVG(service_speed), 2) AS average_serviced_speed,
               ROUND(AVG(service_satisfaction), 2) AS average_service_satisfaction,
               ROUND(AVG(problem_satisfaction), 2) AS average_problem_satisfaction
        FROM rating
    )
    SELECT 'ความรวดเร็ว' AS avg, average_serviced_speed AS score
    FROM AverageScores
    UNION ALL
    SELECT 'การแก้ปัญหา', average_service_satisfaction
    FROM AverageScores
    UNION ALL
    SELECT 'การให้บริการ', average_problem_satisfaction
    FROM AverageScores;
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data
$labels = [];
$scores = [];

foreach ($results as $row) {
    $labels[] = $row['avg'];
    $scores[] = (float)$row['score'];
}

// Send JSON response
echo json_encode([
    'labels' => $labels,
    'scores' => $scores,
]);
?>
