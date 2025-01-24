<?php
require_once 'config/db.php';

try {
    $sql = "
        WITH AverageScores AS (
            SELECT ROUND(AVG(service_speed), 2) AS average_serviced_speed,
                   ROUND(AVG(service_satisfaction), 2) AS average_service_satisfaction,
                   ROUND(AVG(problem_satisfaction), 2) AS average_problem_satisfaction
            FROM rating
        )
        SELECT 'ความพึงพอใจโดยรวม' AS label,
               ROUND((average_serviced_speed + average_service_satisfaction + average_problem_satisfaction) / 3, 2) AS score
        FROM AverageScores";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Prepare the response
    echo json_encode([
        'label' => $result['label'],
        'score' => $result['score'],
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
