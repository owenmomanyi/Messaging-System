<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

function formatPhoneNumber($phoneNumber) {
    // Ensure the phone number starts with a '+'
    if (substr($phoneNumber, 0, 1) !== '+') {
        $phoneNumber = '+' . $phoneNumber;
    }
    return $phoneNumber;
}

$user_id = $_SESSION['user_id'];
$contact_name = $_POST['contact_name'];
$contact_phone = formatPhoneNumber($_POST['contact_phone']);

$sql = "INSERT INTO contacts (user_id, contact_name, contact_phone) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $contact_name, $contact_phone);

if ($stmt->execute()) {
    header("Location: index.php");
} else {
    echo "Error: " . $conn->error;
}
?>