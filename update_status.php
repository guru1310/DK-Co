<?php
// update_status.php

require 'db_config.php'; // contains $conn

$boe = $_POST['boe_number'] ?? '';
$eta = $_POST['eta'] ?? null;
$actual = $_POST['actual_arrival'] ?? null;

if (!$boe) {
    die("BOE Number required.");
}

$status = 'pending';
$today = date('Y-m-d');

if ($actual) {
    $status = 'delivered';
} elseif ($eta && $eta < $today) {
    $status = 'delayed';
}

$sql = "UPDATE shipments SET eta = ?, actual_arrival = ?, status = ? WHERE boe_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $eta, $actual, $status, $boe);

if ($stmt->execute()) {
    echo "Status updated successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
