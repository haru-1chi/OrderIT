<?php
require_once '../config/db.php';

$term = $_GET['term']; // Search term entered by the user

// Query to fetch records matching the search term
$sql = "
    SELECT 
        od.*, 
        nd.numberDevice, 
        dv.device_name, 
        dp.depart_name,
        dm.models_name AS list
    FROM 
    orderdata_new AS od
    LEFT JOIN order_numberdevice AS nd ON od.id = nd.order_item 
    LEFT JOIN device AS dv ON od.refDevice = dv.device_id
    LEFT JOIN depart AS dp ON od.refDepart = dp.depart_id
    LEFT JOIN order_items AS oi ON od.id = oi.order_id
    LEFT JOIN device_models AS dm ON oi.list = dm.models_id
    WHERE
        (nd.is_deleted = 0 OR nd.is_deleted IS NULL)
AND (oi.is_deleted = 0 OR oi.is_deleted IS NULL)
        AND (
            nd.numberDevice LIKE :term OR
            od.numberWork LIKE :term OR
            od.refUsername LIKE :term OR
            od.reason LIKE :term OR
            od.report LIKE :term OR
            od.note LIKE :term OR
            dv.device_name LIKE :term OR
            dp.depart_name LIKE :term OR
            dm.models_name LIKE :term
        )
";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process results to filter duplicates
$filteredData = [];
foreach ($result as $row) {
    $uniqueKey = $row['numberWork'] . '|' . $row['device_name']; // Unique key for duplicate check

    if (!isset($filteredData[$uniqueKey])) {
        $label = "";

        // Check which field is similar to the search term and build the label
        if (stripos($row['numberWork'], $term) !== false) {
            $label = "{$row['numberWork']}";
        } elseif (stripos($row['numberDevice'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['numberDevice']}";
        } elseif (stripos($row['device_name'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['device_name']}";
        } elseif (stripos($row['depart_name'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['depart_name']}";
        } elseif (stripos($row['refUsername'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['refUsername']}";
        } elseif (stripos($row['reason'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['reason']}";
        } elseif (stripos($row['report'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['report']}";
        } elseif (stripos($row['note'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['note']}";
        } elseif (stripos($row['list'], $term) !== false) {
            $label = "{$row['numberWork']} - {$row['list']}";
        }

        // Add the processed result to the filtered data
        if ($label) {
            $filteredData[$uniqueKey] = [
                'label' => $label,
                'value' => $row['numberWork'],
            ];
        }
    }
}

// Return unique suggestions in JSON format
echo json_encode(array_values($filteredData));
