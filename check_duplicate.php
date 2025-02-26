<?php
header('Content-Type: application/json');

// Database connection
require_once 'config/db.php';

// Get device number from POST request
if (isset($_POST['number_device'])) {
    $deviceNumbers = json_decode($_POST['number_device'], true);

    if (!is_array($deviceNumbers) || count($deviceNumbers) === 0) {
        echo json_encode(['error' => 'Invalid device numbers']);
        exit;
    }

    // Prepare placeholders for PDO
    $placeholders = implode(',', array_fill(0, count($deviceNumbers), '?'));
    $sql = "
SELECT 
    od.id AS order_id, 
    od.numberWork,
    od.dateWithdraw,
    od.note,
    wd.withdraw_name, 
    lw.work_name, 
    dv.device_name, 
    dp.depart_name, 
    of.offer_name,
    nd.numberDevice, 
    nd.id AS numberDevice_id, 
    nd.is_deleted AS deleted_numberDevice, 
    oi.id AS item_id, 
    oi.list, 
    dm.models_name AS list_name, 
    oi.quality, 
    oi.amount, 
    oi.price, 
    oi.unit,
    oi.is_deleted AS deleted_item
FROM orderdata_new AS od
LEFT JOIN withdraw AS wd ON od.refWithdraw = wd.withdraw_id
LEFT JOIN offer AS of ON od.refOffer = of.offer_id
LEFT JOIN depart AS dp ON od.refDepart = dp.depart_id
LEFT JOIN listwork AS lw ON od.refWork = lw.work_id
LEFT JOIN device AS dv ON od.refDevice = dv.device_id
LEFT JOIN order_numberdevice AS nd ON od.id = nd.order_item
LEFT JOIN order_items AS oi ON od.id = oi.order_id
LEFT JOIN device_models AS dm ON oi.list = dm.models_id
WHERE nd.numberDevice IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $stmt->execute($deviceNumbers);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        // Initialize orders array
        $orders = [];

        foreach ($results as $row) {
            $orderId = $row['order_id'];
            $itemId = $row['item_id'];

            // Create order structure if it doesn't exist
            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'numberWork' => $row['numberWork'],
                    'numberDevice' => $row['numberDevice'],
                    'dateWithdraw' => $row['dateWithdraw'],
                    'note' => $row['note'],
                    'withdraw_name' => $row['withdraw_name'],
                    'work_name' => $row['work_name'],
                    'device_name' => $row['device_name'],
                    'depart_name' => $row['depart_name'],
                    'offer_name' => $row['offer_name'],
                    'items' => []
                ];
            }

            // Add items to the order
            if (!empty($itemId) && $row['deleted_item'] != '1') {
                $orders[$orderId]['items'][$itemId] = [
                    'list_name' => $row['list_name'],
                    'quality' => $row['quality'],
                    'amount' => $row['amount'],
                    'price' => $row['price'],
                    'total' => $row['price'] * $row['amount'],
                    'unit' => $row['unit']
                ];
            }
        }

        // Convert the orders array to JSON
        echo json_encode([
            'found' => true,
            'orders' => $orders
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
