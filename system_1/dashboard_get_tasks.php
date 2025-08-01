<?php
require_once '../config/db.php'; // Ensure you include your database connection

header('Content-Type: application/json');

// Check if the "type" parameter is set in the request
$type = isset($_GET['type']) ? $_GET['type'] : '';
$dateNow = new DateTime();
$dateThai = $dateNow->format("Y-m-d");
// Define the SQL query based on the requested type
if ($type === 'today') {
    $sql = "SELECT dp.*, dt.depart_name 
            FROM data_report as dp
            LEFT JOIN depart as dt ON dp.department = dt.depart_id
            WHERE dp.status = 0 AND DATE(date_report) = '$dateThai'";
} elseif ($type === 'in_progress') {
    $sql = "SELECT dp.id, dp.device, dp.report, dp.time_report, dp.take, dt.depart_name, 
                   adm.fname, adm.lname, dp.deviceName
            FROM data_report as dp
            LEFT JOIN depart as dt ON dp.department = dt.depart_id 
            INNER JOIN admin as adm ON dp.username = adm.username
            WHERE dp.status = 2
            ";
} elseif ($type === 'over_due') {
    $sql = "SELECT dp.*,dt.depart_name 
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    WHERE dp.status = 0 AND DATE(date_report) <> '$dateThai'
                    
                    ";
} elseif ($type === 'calm') {
    $sql = "SELECT dp.*,dt.depart_name, adm.fname, adm.lname
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    INNER JOIN admin as adm ON dp.username = adm.username
                    WHERE dp.status = 3
                    
                    ";
} elseif ($type === 'finish') {
    $sql = "SELECT dp.*,dt.depart_name, adm.fname, adm.lname
                    FROM data_report as dp
                    LEFT JOIN depart as dt ON dp.department = dt.depart_id
                    INNER JOIN admin as adm ON dp.username = adm.username
                    WHERE dp.status = 4
                    ";

} elseif ($type === 'cards') {
    $sql = "SELECT status, COUNT(*) as count FROM data_report GROUP BY status";
} else {
    echo json_encode(["error" => "Invalid request type"]);
    exit;
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as &$row) {
    if (isset($row['date_report'])) {
        $row['date_report'] = date('d/m/Y', strtotime($row['date_report']));
    }
    if (isset($row['time_report'])) {
        $row['time_report'] = date('H:i', strtotime($row['time_report'])) . 'น.';
    }
    if (isset($row['take'])) {
        $row['take'] = date('H:i', strtotime($row['take'])) . 'น.';
    }
    if (isset($row['close_date'])) {
        $row['close_date'] = date('H:i', strtotime($row['close_date'])) . 'น.';
    }
}

// Return the result as JSON
echo json_encode($result);
