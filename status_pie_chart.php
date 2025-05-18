<?php
require 'db_config.php';
$data = ["pending" => 0, "delivered" => 0, "delayed" => 0];

$res = $conn->query("SELECT status, COUNT(*) as total FROM shipments GROUP BY status");
while ($row = $res->fetch_assoc()) {
    $data[$row['status']] = (int)$row['total'];
}

echo json_encode($data);
?>
