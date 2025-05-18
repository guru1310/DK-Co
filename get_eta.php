<?php
require 'db_config.php';
header('Content-Type: application/json');

$boe = $_GET['boe_number'] ?? '';

if (!$boe) {
    echo json_encode(["success" => false, "message" => "No BOE number provided."]);
    exit;
}

$stmt = $conn->prepare("SELECT shipment_date, shipment_mode FROM shipments WHERE boe_number = ?");
$stmt->bind_param("s", $boe);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "BOE number not found."]);
    exit;
}

$data = $result->fetch_assoc();
$shipment_date = new DateTime($data['shipment_date']);
$mode = strtolower($data['shipment_mode']);
$today = new DateTime();

$interval_days = ($mode === 'air') ? 6 : 15;
$eta = clone $shipment_date;
$eta->modify("+$interval_days days");

$diff = (int)$today->diff($eta)->format('%r%a');

if ($diff > 1) {
    $status = "Shipment is arriving in $diff days.";
} elseif ($diff === 1) {
    $status = "Shipment will arrive tomorrow.";
} elseif ($diff === 0) {
    $status = "Shipment is arriving today.";
} else {
    $status = "Shipment has already arrived.";
}

echo json_encode([
    "success" => true,
    "eta" => $eta->format('Y-m-d'),
    "status" => $status
]);
