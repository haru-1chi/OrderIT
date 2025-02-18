<?php
require_once 'config/db.php';

header('Content-Type: application/json'); // Ensure response is JSON format

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'problem';
$username = isset($_GET['username']) ? $_GET['username'] : 'all';
$period = isset($_GET['period']) ? $_GET['period'] : 'day';
$validFilters = ['device', 'problem', 'report', 'sla'];

if (!in_array($filter, $validFilters)) {
    echo json_encode(['error' => 'Invalid filter']);
    exit;
}

$usernameCondition = ($username !== 'all') ? "AND username = :username" : "";

$dateCondition = "";
if ($period === 'day') {
    $dateCondition = "AND DATE(DATE_SUB(date_report, INTERVAL 543 YEAR)) = CURDATE()";
} elseif ($period === 'week') {
    $dateCondition = "AND WEEK(DATE_SUB(date_report, INTERVAL 543 YEAR)) = WEEK(CURDATE())";
} elseif ($period === 'month') {
    $dateCondition = "AND MONTH(DATE_SUB(date_report, INTERVAL 543 YEAR)) = MONTH(CURDATE())";
} elseif ($period === 'year') {
    $dateCondition = "AND YEAR(DATE_SUB(date_report, INTERVAL 543 YEAR)) = YEAR(CURDATE())";
}

try {
    $sql = "
        SELECT IF($filter = '' OR $filter IS NULL, 'ไม่ระบุ', $filter) AS label, COUNT(*) AS count_tasks
        FROM data_report
        WHERE status = 4 $dateCondition $usernameCondition
        GROUP BY label
    ";

    $stmt = $conn->prepare($sql);
    if ($username !== 'all') {
        $stmt->bindParam(':username', $username);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$results) {
        echo json_encode(['labels' => [], 'counts' => []]);
        exit;
    }

    $labels = [];
    $counts = [];

    foreach ($results as $row) {
        $labels[] = $row['label'];
        $counts[] = (int) $row['count_tasks'];
    }

    echo json_encode([
        'labels' => $labels,
        'counts' => $counts
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
