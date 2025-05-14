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

// Check if user is registered
$db = Database::getInstance();
$phoneNumberEscaped = $db->escape($phoneNumber);
$sql = "SELECT * FROM users WHERE phone_number = '$phoneNumberEscaped'";
$result = $db->query($sql);
$isRegistered = ($result && $result->num_rows > 0);

// Main menu
if ($text == "") {
    $response = "CON Welcome to " . APP_NAME . "\n";
    if (!$isRegistered) {
        $response .= "1. Register\n";
        $response .= "2. Exit";
    } else {
        $response .= "1. My Account\n";
        $response .= "2. Market Prices\n";
        $response .= "3. Weather Info\n";
        $response .= "4. Farming Tips\n";
        $response .= "5. Send Message\n";
        $response .= "6. Help\n";
        $response .= "7. Exit";
    }
}

// Handle user input
else {
    if (!$isRegistered) {
        // Registration flow
        if ($textArray[0] == "1") {
            if ($userLevel == 1) {
                $response = "CON Please enter your full name:";
            } else if ($userLevel == 2) {
                $response = "CON Please enter your location:";
            } else if ($userLevel == 3) {
                // Save user data to database
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
        } else if ($textArray[0] == "2") {
            $response = "END Thank you for using " . APP_NAME;
        }
    } else {
        // Registered user menu
        switch ($textArray[0]) {
            case "1": // My Account
                if ($userLevel == 1) {
                    $response = "CON My Account\n";
                    $response .= "1. View Profile\n";
                    $response .= "2. Update Profile\n";
                    $response .= "3. Back to Main Menu";
                } else if ($userLevel == 2) {
                    switch ($textArray[1]) {
                        case "1": // View Profile
                            $user = $result->fetch_assoc();
                            $response = "END Your Profile:\n";
                            $response .= "Name: " . $user['name'] . "\n";
                            $response .= "Location: " . $user['location'];
                            break;
                        case "2": // Update Profile
                            $response = "CON Update Profile\n";
                            $response .= "1. Update Name\n";
                            $response .= "2. Update Location\n";
                            $response .= "3. Back";
                            break;
                        case "3": // Back to Main Menu
                            $response = "CON Welcome to " . APP_NAME . "\n";
                            $response .= "1. My Account\n";
                            $response .= "2. Market Prices\n";
                            $response .= "3. Weather Info\n";
                            $response .= "4. Farming Tips\n";
                            $response .= "5. Send Message\n";
                            $response .= "6. Help\n";
                            $response .= "7. Exit";
                            break;
                    }
                } else if ($userLevel == 3 && $textArray[1] == "2") {
                    switch ($textArray[2]) {
                        case "1": // Update Name
                            $response = "CON Enter your new name:";
                            break;
                        case "2": // Update Location
                            $response = "CON Enter your new location:";
                            break;
                        case "3": // Back
                            $response = "CON My Account\n";
                            $response .= "1. View Profile\n";
                            $response .= "2. Update Profile\n";
                            $response .= "3. Back to Main Menu";
                            break;
                    }
                } else if ($userLevel == 4 && $textArray[1] == "2") {
                    $updateField = $textArray[2] == "1" ? "name" : "location";
                    $newValue = $db->escape($textArray[3]);
                    $sql = "UPDATE users SET $updateField = '$newValue' WHERE phone_number = '$phoneNumberEscaped'";
                    
                    if ($db->query($sql)) {
                        $response = "END Profile updated successfully!";
                    } else {
                        $response = "END Failed to update profile. Please try again.";
                    }
                }
                break;

            case "2": // Market Prices
                if ($userLevel == 1) {
                    $response = "CON Select Crop:\n";
                    $response .= "1. Maize\n";
                    $response .= "2. Beans\n";
                    $response .= "3. Potatoes\n";
                    $response .= "4. Back to Main Menu";
                } else if ($userLevel == 2) {
                    $prices = [
                        "1" => "Maize: KES 3,500 per 90kg bag",
                        "2" => "Beans: KES 4,200 per 90kg bag",
                        "3" => "Potatoes: KES 2,800 per 50kg bag"
                    ];
                    
                    if (isset($prices[$textArray[1]])) {
                        $response = "END " . $prices[$textArray[1]];
                    } else if ($textArray[1] == "4") {
                        $response = "CON Welcome to " . APP_NAME . "\n";
                        $response .= "1. My Account\n";
                        $response .= "2. Market Prices\n";
                        $response .= "3. Weather Info\n";
                        $response .= "4. Farming Tips\n";
                        $response .= "5. Send Message\n";
                        $response .= "6. Help\n";
                        $response .= "7. Exit";
                    }
                }
                break;

            case "3": // Weather Info
                if ($userLevel == 1) {
                    $response = "CON Weather Information:\n";
                    $response .= "1. Today's Forecast\n";
                    $response .= "2. Weekly Forecast\n";
                    $response .= "3. Back to Main Menu";
                } else if ($userLevel == 2) {
                    switch ($textArray[1]) {
                        case "1":
                            $response = "END Today's Weather:\n";
                            $response .= "Temperature: 25°C\n";
                            $response .= "Condition: Sunny\n";
                            $response .= "Rainfall: 0mm";
                            break;
                        case "2":
                            $response = "END Weekly Forecast:\n";
                            $response .= "Mon: Sunny, 25°C\n";
                            $response .= "Tue: Cloudy, 23°C\n";
                            $response .= "Wed: Rain, 20°C\n";
                            $response .= "Thu: Cloudy, 22°C\n";
                            $response .= "Fri: Sunny, 24°C";
                            break;
                        case "3":
                            $response = "CON Welcome to " . APP_NAME . "\n";
                            $response .= "1. My Account\n";
                            $response .= "2. Market Prices\n";
                            $response .= "3. Weather Info\n";
                            $response .= "4. Farming Tips\n";
                            $response .= "5. Send Message\n";
                            $response .= "6. Help\n";
                            $response .= "7. Exit";
                            break;
                    }
                }
                break;

            case "4": // Farming Tips
                if ($userLevel == 1) {
                    $response = "CON Farming Tips:\n";
                    $response .= "1. Crop Management\n";
                    $response .= "2. Pest Control\n";
                    $response .= "3. Soil Management\n";
                    $response .= "4. Back to Main Menu";
                } else if ($userLevel == 2) {
                    $tips = [
                        "1" => "Crop Management Tips:\n- Rotate crops regularly\n- Use proper spacing\n- Monitor growth stages",
                        "2" => "Pest Control Tips:\n- Use natural predators\n- Regular inspection\n- Proper pesticide use",
                        "3" => "Soil Management Tips:\n- Regular testing\n- Proper fertilization\n- Maintain pH balance"
                    ];
                    
                    if (isset($tips[$textArray[1]])) {
                        $response = "END " . $tips[$textArray[1]];
                    } else if ($textArray[1] == "4") {
                        $response = "CON Welcome to " . APP_NAME . "\n";
                        $response .= "1. My Account\n";
                        $response .= "2. Market Prices\n";
                        $response .= "3. Weather Info\n";
                        $response .= "4. Farming Tips\n";
                        $response .= "5. Send Message\n";
                        $response .= "6. Help\n";
                        $response .= "7. Exit";
                    }
                }
                break;

            case "5": // Send Message
                if ($userLevel == 1) {
                    $response = "CON Enter the message you want to send:";
                } else if ($userLevel == 2) {
                    try {
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

            case "6": // Help
                $response = "END Available Services:\n";
                $response .= "1. My Account - Manage your profile\n";
                $response .= "2. Market Prices - Check crop prices\n";
                $response .= "3. Weather Info - Get weather updates\n";
                $response .= "4. Farming Tips - Learn best practices\n";
                $response .= "5. Send Message - Contact support\n";
                $response .= "6. Help - Show this message\n";
                $response .= "7. Exit - End session";
                break;

            case "7": // Exit
                $response = "END Thank you for using " . APP_NAME;
                break;

            default:
                $response = "END Invalid option selected. Please try again.";
                break;
        }
    }
}

// Print the response
header('Content-type: text/plain');
echo $response;
?> 