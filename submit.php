<?php
include 'db.php';

$name = $_POST['name'];
$company = $_POST['company_name'];
$phone = $_POST['phone'];
$gstin = $_POST['gstin'];

$sql = "INSERT INTO clients (name, company_name, phone, gstin)
        VALUES ('$name', '$company', '$phone', '$gstin')";

if ($conn->query($sql) === TRUE) {
    echo "Client data saved successfully.";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
