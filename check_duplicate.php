<?php
header('Content-Type: application/json');

// Database connection
require_once 'config/db.php';

// Get device number from POST request
if (isset($_POST['device_number'])) {
    $numberDevice = trim($_POST['device_number']);

    $sql = "
    SELECT 
        od.id AS order_id, 
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
    WHERE nd.numberDevice = :numberDevice";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':numberDevice', $numberDevice, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'found' => true,
            'device_number' => $result['numberDevice'],
            'withdraw_name' => $result['withdraw_name'],
            'work_name' => $result['work_name'],
            'device_name' => $result['device_name'],
            'depart_name' => $result['depart_name'],
            'offer_name' => $result['offer_name']
        ]);
    } else {
        echo json_encode(['found' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}
