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
SELECT label, score
FROM (
    SELECT 'ความพึงพอใจโดยรวม' AS label, 
           ROUND((average_serviced_speed + average_service_satisfaction + average_problem_satisfaction) / 3, 2) AS score, 
           1 AS sort_order
    FROM AverageScores
    UNION ALL
    SELECT 'ความรวดเร็ว', average_serviced_speed, 2
    FROM AverageScores
    UNION ALL
    SELECT 'การแก้ปัญหา', average_service_satisfaction, 3
    FROM AverageScores
    UNION ALL
    SELECT 'การให้บริการ', average_problem_satisfaction, 4
    FROM AverageScores
) AS combined_results
ORDER BY sort_order;
";

$stmt = $conn->prepare($sql);
if ($stmt->execute()) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data
    $labels = [];
    $scores = [];

    foreach ($results as $row) {
        $labels[] = $row['label'];  // ✅ Fixed column name
        $scores[] = (float)$row['score'];
    }

    // Send JSON response
    echo json_encode([
        'labels' => $labels,
        'scores' => $scores,
    ]);
} else {
    print_r($stmt->errorInfo()); // Debugging SQL errors
}
?>
