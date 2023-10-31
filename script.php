<?php

/** REQUIREMENTS */
    $envFilePath = __DIR__ . '/.env';

    if (file_exists($envFilePath)) {
        // Read the .env file into an array
        $envData = file($envFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($envData) {
            foreach ($envData as $line) {
                // Parse the line and split it into key and value
                list($key, $value) = explode('=', $line, 2);

                // Set the environment variable (remove leading/trailing whitespace)
                putenv(trim($key) . '=' . trim($value));
            }
        }
    } else {
        // Handle the case where the .env file doesn't exist
        echo '.env file not found.';
    }

    // Specify the path to your JSON file
    $jsonFile = 'user.json';
    // Check if the file exists
    if (file_exists($jsonFile)) {
        // Read the file content
        $jsonContent = file_get_contents($jsonFile);

        // Check if the content is valid JSON
        if ($jsonContent !== false) {
            // Parse the JSON data
            $data = json_decode($jsonContent, true); // Pass true to get an associative array
            checkBirthDate($data);
        } else {
            echo "Failed to read JSON content.";
        }
    } else {
        echo "JSON file not found.";
    }


/*** CONFIGURATION */
    const PERSONNAL_BIRTHDAY = 1;
    const WORK_BIRTHDAY = 2;

    const PERSONNAL_MESSAGE = "🎉🎂 Birthday Reminder 🎂🎉

    Team, today is a special day! It's [Coworker's Name]'s birthday! 🥳

    Let's make their day even brighter by sending some warm birthday wishes and spreading the joy. 🎈🎁

    Please take a moment to send your heartfelt \"Happy Birthday\" messages in this channel to celebrate with [Coworker's Name]. 🎊🍰

    Remember, it's the little things that make a big difference, so let's show our appreciation and make this day memorable for them. 🎉🎂

    Happy Birthday, [Coworker's Name]! 🎉🥳🎂";

    const WORK_MESSAGE = "🎉🎂 Work Anniversary Reminder 🎂🎉

    Team, it's time to celebrate a special day! Today marks [Coworker's Name]'s work anniversary! 🥳

    Let's take a moment to appreciate their dedication, hard work, and contributions to our team. 🙌🎉

    Please join us in sending your warmest wishes and congratulations in this channel to honor [Coworker's Name]'s achievements and the time they've spent with us. 🥂🎈

    Your kind words can make a big difference and show our appreciation for their commitment. 🎊🎉

    Happy Work Anniversary, [Coworker's Name]! 🎉🥳🙌";

    const CHANNEL_NAME = 'general';



function checkBirthDate(array $data)
{
    $today = '1985-10-21';
    // $today = '1973-06-12';

    foreach ($data as $user) {
        if ($user['Birthdate'] === $today) {
            $message = generateMessage($user, PERSONNAL_BIRTHDAY);
            sendMessage($message);
        }
        if ($user['AnniversaryCompanyDate'] === $today) {
            $message = generateMessage($user, WORK_BIRTHDAY);
            sendMessage($message);
        }
    }

    return true;
}
function generateMessage(array $user, int $type): array
{
    $coworkerName = $user['FirstName'] . ' ' . $user['LastName'];
    $formatedMessage = [];
    if ($type == 1) {
        $formatedMessage['message'] = urlencode(preg_replace("/\[Coworker's Name\]/", $coworkerName, PERSONNAL_MESSAGE));
        $formatedMessage['icon'] = ':confetti_ball:';
    }
    if ($type == 2) {
        $formatedMessage['message'] = urlencode(preg_replace("/\[Coworker's Name\]/", $coworkerName, WORK_MESSAGE));
        $formatedMessage['icon'] = ':sports_medal:';
    }

    return $formatedMessage;
}
function sendMessage(array $message)
{
    $id_channel = getIdChannel(CHANNEL_NAME);
    // Initialize cURL session
    $curl = curl_init(getenv('API_URL').'chat.postMessage?channel=' . $id_channel . '&icon_emoji=' . $message['icon'] . '&text=' . $message['message'] . '&pretty=1');
    // Set cURL options
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . getenv('AUTH_TOKEN'), // Send the authentication token in the header
    ));
    // Execute the cURL session and fetch the response
    $response = curl_exec($curl);
    // Check for cURL errors
    if (curl_errno($curl)) {
        echo 'cURL Error: ' . curl_error($curl);
    }
    // Close the cURL session
    curl_close($curl);
}
function getIdChannel(string $channel_name): string
{
    $id_channel = '';
    // Initialize cURL session
    $ch = curl_init(getenv('API_URL').'conversations.list?pretty=1');
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . getenv('AUTH_TOKEN'), // Send the authentication token in the header
    ));
    // Execute the cURL session and fetch the response
    $response = curl_exec($ch);
    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    }
    // Close the cURL session
    curl_close($ch);


    // Handle the API response (you can process or print it)
    if ($response) {
        // echo "Response: " . $response;
        $data = json_decode($response, true);
        if ($data !== null) {
            // Access the JSON data
            $channels = $data['channels'];
            foreach ($channels as $channel) {
                if ($channel['name'] === $channel_name) {
                    $id_channel = $channel['id'];
                    echo $id_channel;
                }
            }
        }
    } else {
        echo "No response received.";
    }

    return $id_channel;
}
