<?php
include('config.php');

function verifyNumbers($phone_numbers) {
    global $infobip_api_key, $infobip_base_url;

    $url = "$infobip_base_url/number/1/query";
    $data = array(
        'to' => $phone_numbers
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        "Authorization: App $infobip_api_key"
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        $response_data = json_decode($response, true);
        return $response_data;
    }

    curl_close($ch);
    return false;
}

// Example usage:
$phone_numbers = ['+254791870719', '+254792384325']; // Replace with phone numbers to verify
$result = verifyNumbers($phone_numbers);

if ($result) {
    echo 'Phone number verification results: ';
    print_r($result);
} else {
    echo 'Failed to verify phone numbers.';
}
?>