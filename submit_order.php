<?php
// submit_order.php

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dk_company"; // CHANGE THIS

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$name = trim($_POST['name'] ?? '');
$company = trim($_POST['company_name'] ?? '');
$hsn_code_str = trim($_POST['hsn_code'] ?? '');
$mode = trim($_POST['mode'] ?? '');
$origin = trim($_POST['origin'] ?? '');
$departure_date = $_POST['departure_date'] ?? '';
$port = trim($_POST['port'] ?? '');
$weight = floatval($_POST['weight'] ?? 0);

// Extract HSN code from combined string
$hsn_code = explode(" - ", $hsn_code_str)[0];

// Fetch HSN details
$hsn_query = $conn->prepare("SELECT description, gst_rate FROM hsn_codes WHERE hsn_code = ? LIMIT 1");
$hsn_query->bind_param("s", $hsn_code);
$hsn_query->execute();
$hsn_result = $hsn_query->get_result();

if ($hsn_result->num_rows === 0) {
    die("HSN Code not found.");
}

$hsn_data = $hsn_result->fetch_assoc();
$description = $hsn_data['description'];
$gst_rate = floatval($hsn_data['gst_rate']);

// Distance mapping (you can replace this with a better distance API or logic)
$distances = [
    'mumbai' => 1400,
    'bangalore' => 2100,
    'tughlakabad' => 30,
    'pune' => 1450
];
$distance = $distances[strtolower($port)] ?? 0;

// Calculate freight (simple logic)
$rate_per_km_per_kg = ($mode === 'air') ? 0.10 : 0.05;
$freight = $distance * $weight * $rate_per_km_per_kg;

// Calculate GST
$taxable_amount = $freight;
$gst_amount = ($gst_rate / 100) * $taxable_amount;
$total_amount = $freight + $gst_amount;

// Auto-generate BOL & BOE
$unique_id = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
$year = date('Y');
$bol_number = "BOL{$year}-{$unique_id}";
$boe_number = "BOE{$year}-{$unique_id}";

// Insert into shipments table
$stmt = $conn->prepare("INSERT INTO shipments (client_id, origin, destination, weight, mode, hsn_code, charges, eta, status, boe_number, bol_number, created_at) VALUES (
    (SELECT id FROM clients WHERE name = ? AND company_name = ? LIMIT 1), ?, ?, ?, ?, ?, ?, NULL, 'Pending', ?, ?, NOW()
)");

$destination = $port; // for clarity
$stmt->bind_param("ssssssdsss",
    $name,
    $company,
    $origin,
    $destination,
    $weight,
    $mode,
    $hsn_code,
    $freight,
    $boe_number,
    $bol_number
);

if ($stmt->execute()) {
    echo "<h3>Delivery Order Submitted Successfully!</h3>";
    echo "<p><strong>BOL Number:</strong> $bol_number</p>";
    echo "<p><strong>BOE Number:</strong> $boe_number</p>";
    echo "<p><strong>Freight:</strong> ₹" . number_format($freight, 2) . "</p>";
    echo "<p><strong>GST (" . $gst_rate . "%):</strong> ₹" . number_format($gst_amount, 2) . "</p>";
    echo "<p><strong>Total Payable:</strong> ₹" . number_format($total_amount, 2) . "</p>";
} else {
    echo "Error submitting delivery order: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
