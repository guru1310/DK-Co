<?php
// insert.php

// Database credentials — adjust as per your setup
$servername = "localhost";
$username = "root";
$password = ""; // or your DB password
$dbname = "dk_company";  // Replace with your actual DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize and validate POST data
$name = trim($_POST['name'] ?? '');
$company_name = trim($_POST['company_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gstin = trim($_POST['gstin'] ?? '');

// Basic validation
if (empty($name) || empty($company_name) || empty($phone)) {
    die("Name, Company, and Phone are required fields.");
}

// Prepare and bind (prevents SQL injection)
$stmt = $conn->prepare("INSERT INTO clients (name, company_name, phone, gstin) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param("ssss", $name, $company_name, $phone, $gstin);

if ($stmt->execute()) {
    header("Location: index.html?msg=success");
    exit();
} else {
    header("Location: index.html?msg=error");
    exit();
}


$stmt->close();
$conn->close();
?>
