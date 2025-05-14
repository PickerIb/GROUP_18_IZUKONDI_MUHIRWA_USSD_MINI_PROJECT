<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

// Initialize Africa's Talking SDK
$at = new AfricasTalking(AT_USERNAME, AT_API_KEY);

// Get the SMS parameters
$from = $_POST['from'];
$to = $_POST['to'];
$text = $_POST['text'];
$date = $_POST['date'];
$id = $_POST['id'];


// Log the incoming SMS
$db = Database::getInstance();
$from = $db->escape($from);
$text = $db->escape($text);
$date = $db->escape($date);
$id = $db->escape($id);

$sql = "INSERT INTO sms_logs (phone_number, message, received_date, message_id) 
        VALUES ('$from', '$text', '$date', '$id')";
$db->query($sql);

// Process the SMS based on content
$response = "";

if (strtolower($text) == "help") {
    $response = "Welcome to " . APP_NAME . "! Available commands:\n";
    $response .= "- help: Show this help message\n";
    $response .= "- info: Get your account information\n";
    $response .= "- register: Start registration process";
} 
else if (strtolower($text) == "info") {
    $sql = "SELECT * FROM users WHERE phone_number = '$from'";
    $result = $db->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $response = "Your Profile:\n";
        $response .= "Name: " . $user['name'] . "\n";
        $response .= "Location: " . $user['location'];
    } else {
        $response = "Profile not found. Please register first.";
    }
}
else if (strtolower($text) == "register") {
    $response = "Please dial *384*1742# to register for " . APP_NAME;
}
else {
    $response = "Invalid command. Send 'help' for available commands.";
}

try {
    // Send the response SMS using Africa's Talking SDK
    $sms = $at->sms();
    $result = $sms->send([
        'to'      => $from,
        'message' => $response,
        'from'    => AT_SENDER_ID
    ]);

    // Log the response
    $response = $db->escape($response);
    $sql = "INSERT INTO sms_logs (phone_number, message, received_date, message_id, is_outgoing) 
            VALUES ('$from', '$response', NOW(), '', 1)";
    $db->query($sql);
} catch (Exception $e) {
    // Log the error
    error_log("Failed to send SMS: " . $e->getMessage());
}
?> 