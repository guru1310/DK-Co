<?php
require 'db_config.php';
$client_id = $_GET['id'] ?? die("Client ID missing");

$sql = "SELECT * FROM shipments WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();

$today = date('Y-m-d');

while ($row = $result->fetch_assoc()) {
    // Determine status
    if (!empty($row['actual_arrival'])) {
        $status = "🟢 Delivered";
    } elseif (!empty($row['eta']) && $row['eta'] < $today) {
        $status = "🔴 Delayed";
    } else {
        $status = "🟡 Pending";
    }

    echo "<p>
        BOL: <strong>{$row['bol_number']}</strong> |
        ETA: {$row['eta']} |
        Actual Arrival: {$row['actual_arrival']} |
        Status: <strong>$status</strong>
    </p>";
}
?>
