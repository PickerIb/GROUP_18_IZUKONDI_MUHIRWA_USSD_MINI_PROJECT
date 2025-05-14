<?php
require_once 'config.php';
require_once 'db_connect.php';
require_once 'vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

// Initialize Africa's Talking SDK
$at = new AfricasTalking(AT_USERNAME, AT_API_KEY);

// Get the USSD parameters
$sessionId = $_POST['sessionId'];
$serviceCode = $_POST['serviceCode'];
$phoneNumber = $_POST['phoneNumber'];
$text = $_POST['text'];

// Initialize the response
$response = "";

// Split the text into an array
$textArray = explode("*", $text);
$userLevel = count($textArray);

// Main menu
if ($text == "") {
    $response = "CON Welcome to " . APP_NAME . "\n";
    $response .= "1. Register\n";
    $response .= "2. View Profile\n";
    $response .= "3. Send Message\n";
    $response .= "4. Exit";
}

// Handle user input
else {
    switch ($textArray[0]) {
        case "1": // Registration
            if ($userLevel == 1) {
                $response = "CON Please enter your full name:";
            } else if ($userLevel == 2) {
                $response = "CON Please enter your location:";
            } else if ($userLevel == 3) {
                // Save user data to database
                $db = Database::getInstance();
                $name = $db->escape($textArray[1]);
                $location = $db->escape($textArray[2]);
                
                $sql = "INSERT INTO users (phone_number, name, location) 
                        VALUES ('$phoneNumber', '$name', '$location')";
                
                if ($db->query($sql)) {
                    // Send welcome SMS
                    try {
                        $sms = $at->sms();
                        $welcomeMessage = "Welcome to " . APP_NAME . "!\n\n";
                        $welcomeMessage .= "Dear " . $name . ",\n";
                        $welcomeMessage .= "Thank you for registering with us. You can now access our services through USSD or SMS.\n\n";
                        $welcomeMessage .= "USSD Code: *384*1742#\n";
                        $welcomeMessage .= "SMS Commands: help, info, register";
                        
                        $result = $sms->send([
                            'to'      => $phoneNumber,
                            'message' => $welcomeMessage,
                            'from'    => AT_SENDER_ID
                        ]);
                        
                        // Log the welcome SMS
                        $welcomeMessage = $db->escape($welcomeMessage);
                        $sql = "INSERT INTO sms_logs (phone_number, message, received_date, message_id, is_outgoing) 
                                VALUES ('$phoneNumber', '$welcomeMessage', NOW(), '', 1)";
                        $db->query($sql);
                    } catch (Exception $e) {
                        error_log("Failed to send welcome SMS: " . $e->getMessage());
                    }
                    
                    $response = "END Registration successful! Welcome to " . APP_NAME;
                } else {
                    $response = "END Registration failed. Please try again.";
                }
            }
            break;

        case "2": // View Profile
            $db = Database::getInstance();
            $phoneNumber = $db->escape($phoneNumber);
            
            $sql = "SELECT * FROM users WHERE phone_number = '$phoneNumber'";
            $result = $db->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $response = "END Your Profile:\n";
                $response .= "Name: " . $user['name'] . "\n";
                $response .= "Location: " . $user['location'];
            } else {
                $response = "END Profile not found. Please register first.";
            }
            break;

        case "3": // Send Message
            if ($userLevel == 1) {
                $response = "CON Enter the message you want to send:";
            } else if ($userLevel == 2) {
                try {
                    // Send SMS using Africa's Talking SDK
                    $message = $textArray[1];
                    $sms = $at->sms();
                    $result = $sms->send([
                        'to'      => $phoneNumber,
                        'message' => $message,
                        'from'    => AT_SENDER_ID
                    ]);
                    
                    $response = "END Message sent successfully!";
                } catch (Exception $e) {
                    $response = "END Failed to send message. Please try again.";
                }
            }
            break;

        case "4": // Exit
            $response = "END Thank you for using " . APP_NAME;
            break;

        default:
            $response = "END Invalid option selected. Please try again.";
            break;
    }
}

// Print the response
header('Content-type: text/plain');
echo $response;
?> 