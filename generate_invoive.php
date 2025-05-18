<?php
// generate_invoice.php

require 'db_config.php';

$boe = $_GET['boe'] ?? die("Missing BOE Number");

$sql = "SELECT s.*, c.name, c.company_name, c.gstin FROM shipments s
        JOIN clients c ON s.client_id = c.id
        WHERE s.boe_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $boe);


// Fetch shipment and client info
$sql = "SELECT s.*, c.name, c.company_name, c.gstin FROM shipments s
        JOIN clients c ON s.client_id = c.id
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shipment_id);
$stmt->execute();
$result = $stmt->get_result();
$shipment = $result->fetch_assoc();

if (!$shipment) {
    die("Shipment not found.");
}

// Calculate GST Breakdown
$gst_rate = $shipment['gst_rate'] ?? 18;
$base_amount = $shipment['charges'];
$gst_amount = ($gst_rate / 100) * $base_amount;
$cgst = $sgst = $gst_amount / 2;
$total_amount = $base_amount + $gst_amount;

// Prepare invoice details
$invoice_date = date('Y-m-d');
$invoice_no = 'INV' . str_pad($shipment['id'], 5, '0', STR_PAD_LEFT);
$boe = $shipment['boe_number'];
$bol = $shipment['bol_number'];

// Output the invoice (Simple HTML)
echo "<html><head><title>Shipment Invoice</title></head><body>";
echo "<h2>Shipment Invoice</h2>";
echo "<p><strong>Invoice No:</strong> $invoice_no</p>";
echo "<p><strong>Invoice Date:</strong> $invoice_date</p>";
echo "<p><strong>Client:</strong> {$shipment['name']} ({$shipment['company_name']})</p>";
echo "<p><strong>GSTIN:</strong> {$shipment['gstin']}</p>";
echo "<hr>";
echo "<p><strong>Origin:</strong> {$shipment['origin']}</p>";
echo "<p><strong>Destination:</strong> {$shipment['destination']}</p>";
echo "<p><strong>Mode:</strong> {$shipment['mode']}</p>";
echo "<p><strong>HSN Code:</strong> {$shipment['hsn_code']} ({$shipment['category']})</p>";
echo "<p><strong>Departure Date:</strong> {$shipment['departure_date']}</p>";
echo "<p><strong>Weight:</strong> {$shipment['weight']} kg</p>";
echo "<hr>";
echo "<p><strong>Base Freight Charges:</strong> ₹$base_amount</p>";
echo "<p><strong>CGST ({$gst_rate/2}%):</strong> ₹$cgst</p>";
echo "<p><strong>SGST ({$gst_rate/2}%):</strong> ₹$sgst</p>";
echo "<p><strong>Total Amount:</strong> <strong>₹$total_amount</strong></p>";
echo "<hr>";
echo "<p><strong>BOE Number:</strong> $boe</p>";
echo "<p><strong>BOL Number:</strong> $bol</p>";
echo "</body></html>";
?>
