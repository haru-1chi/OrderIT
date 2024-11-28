<?php
require_once 'config/db.php';

if (isset($_POST['numberWork'])) {
    $numberWork = $_POST['numberWork'];

    $sql = "SELECT * FROM orderdata_new 
            JOIN order_numberdevice ON orderdata_new.id = order_numberdevice.order_id
            JOIN order_items ON orderdata_new.id = order_items.order_id
            WHERE orderdata_new.numberWork = :numberWork";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':numberWork', $numberWork, PDO::PARAM_STR);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        echo json_encode([
            'success' => true,
            'dateWithdraw' => $record['dateWithdraw'],
            'quality' => $record['quality'],
            'amount' => $record['amount'],
            'price' => $record['price'],
            'unit' => $record['unit'],
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
