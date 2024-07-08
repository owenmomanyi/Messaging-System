<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "messaging system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Infobip credentials
$infobip_api_key = '6489ae1290250c0e4419077d90d9b293-ad8d6747-f3dd-4311-b4ad-d274b3f2e164';
$infobip_base_url = 'https://api.infobip.com/sms/1/text/single';
?>