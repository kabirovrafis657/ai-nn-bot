<?php

include 'users.php';

function lookup($idToken, $base64, $chat_id, $paid){
    // URL for the API endpoint
$url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=AIzaSyD_omM03MyUQdBNAQ3lW0RzjRS5x29GDnM';



$timestamp = time();

//$url2 = "https://api.telegram.org/bot7905195248:AAFB97i8Cuq8qIRhnQKkmUCYwZ4e4Gq2mHI/sendMessage?chat_id=$chat_id&text=Prosess 2";

// Send the message via GET request
// file_get_contents($url);
// Data to send in the POST request
$data = array(
    "idToken" => $idToken,
);

// Convert the data to JSON format
$json_data = json_encode($data);

// Initialize cURL session
$ch = curl_init($url);

// Set the necessary cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Accept: */*',
    'Accept-Language: ru-RU,ru;q=0.9,en-GB;q=0.8,en;q=0.7,en-US;q=0.6',
    'Cache-Control: no-cache',
    'Pragma: no-cache',
    'X-Client-Data: CIq2yQEIprbJAQipncoBCJfjygEIlqHLAQj7mM0BCIagzQEIjNPOAQjD1c4B',
    'X-Client-Version: Safari/JsCore/10.12.1/FirebaseCore-web'.$timestamp,
    'X-Firebase-Gmpid: 1010946768366',
     'Sec-Fetch-Dest: document',
    'Sec-Fetch-Mode: navigate',
    'Sec-Fetch-Site: none',
    'Sec-Fetch-User: ?1'.$timestamp,
    'Sec-CH-UA: "Chromium";v="125", "Google Chrome";v="125", "Not:A-Brand";v="99"'.$timestamp,
    'Sec-CH-UA-Mobile: ?0'.$timestamp,
    'Sec-CH-UA-Platform: "Windows"'.$timestamp,
        "User-Agent: Mozilla/5.0 (Linux; Android " . rand(8, 13) . "; Pixel " . rand(2, 6) . ") AppleWebKit/537.36 (KHTML, like Gecko) Chrome/" . rand(90, 115) . ".0." . rand(1000, 9999) . "." . rand(10, 99) . " Mobile Safari/537.36",
    "X-Forwarded-For: " . rand(1, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 254),
));

// Execute the cURL request and get the response
$response = curl_exec($ch);

$data_josn = json_decode(''.$response.'', true);



// Check for any cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    // Output the response from the API
    
   // echo $response
   
   
     //   $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendMessage?chat_id=$chat_id&text=Prosess 2 ";
    
    // Send the message via GET request
    // file_get_contents($url);
    

   // echo $data_josn['users'][0]['localId'];
   
   users($data_josn['users'][0]['localId'], $data_josn['users'][0]['email'], $base64, $chat_id, $paid);
    
  // header('Location: https://academytable.ru/bot/users.php?firebase_id='.$data_josn['users'][0]['localId'].'&email='.$data_josn['users'][0]['email']);
}

// Close the cURL session
curl_close($ch);
}

?>
