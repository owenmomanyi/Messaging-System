<?php
session_start();
include('config.php');

ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/apache/logs/error.log'); // Update this path

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    // Fetch receiver details from contacts table
    $sql_receiver = "SELECT * FROM contacts WHERE id = ?";
    $stmt_receiver = $conn->prepare($sql_receiver);
    $stmt_receiver->bind_param("i", $receiver_id);
    $stmt_receiver->execute();
    $result_receiver = $stmt_receiver->get_result();

    if ($result_receiver && $result_receiver->num_rows > 0) {
        $receiver = $result_receiver->fetch_assoc();
        $receiver_phone = $receiver['contact_phone'];

        // Infobip API configuration
        $infobip_api_key = '6489ae1290250c0e4419077d90d9b293-ad8d6747-f3dd-4311-b4ad-d274b3f2e164';
        $infobip_api_url = 'https://api.infobip.com/sms/1/text/single'; // Update with correct Infobip SMS endpoint

        // Prepare data for Infobip API
        $data = json_encode([
            'from' => 'YourSenderName',
            'to' => $receiver_phone,
            'text' => $message
        ]);

        // Initialize CURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $infobip_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: App ' . $infobip_api_key
        ]);

        // Execute the CURL request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Log the API response
        error_log('API Response: ' . print_r($response, true));

        // Check for CURL errors
        if ($response === false) {
            $error_message = curl_error($ch);
            header("HTTP/1.1 500 Internal Server Error");
            echo "Error sending SMS: CURL Error - " . htmlspecialchars($error_message);
            error_log("Error sending SMS: CURL Error - " . $error_message);
        } else {
            // Decode the API response
            $response_data = json_decode($response, true);

            // Check HTTP response code and response data
            if ($http_code == 200 || $http_code == 201) {
                if (isset($response_data['messages']) && $response_data['messages'][0]['status']['groupName'] == 'PENDING') {
                    // SMS sent successfully
                    $sent_at = date('Y-m-d H:i:s');
                    $sql_insert_message = "INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, ?)";
                    $stmt_insert = $conn->prepare($sql_insert_message);
                    $stmt_insert->bind_param("iiss", $sender_id, $receiver_id, $message, $sent_at);

                    if ($stmt_insert->execute()) {
                        // Message saved in the database, prepare HTML response to display
                        $message_html = "<div class='message sent'><p>" . htmlspecialchars($message) . "</p></div>";
                        echo $message_html;
                    } else {
                        // Database error
                        header("HTTP/1.1 500 Internal Server Error");
                        echo "Error saving message to database: " . $conn->error;
                        error_log("Error saving message to database: " . $conn->error);
                    }
                } else {
                    // Error in API response
                    header("HTTP/1.1 $http_code Internal Server Error");
                    echo "Error sending SMS: " . htmlspecialchars($response);
                    error_log("Error sending SMS: " . $response);
                }
            } else {
                // Error sending SMS through Infobip API
                header("HTTP/1.1 $http_code Internal Server Error");
                echo "Error sending SMS: HTTP $http_code - " . htmlspecialchars($response);
                error_log("Error sending SMS: HTTP $http_code - " . $response);
            }
        }

        // Close CURL session
        curl_close($ch);
    } else {
        // Invalid receiver_id or contact not found
        header("HTTP/1.1 400 Bad Request");
        echo "Invalid receiver ID. The contact does not exist or is unauthorized.";
        error_log("Invalid receiver ID. The contact does not exist or is unauthorized.");
    }
} else {
    // Invalid request method
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Method Not Allowed";
}
?>
