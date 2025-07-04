<?php

// The API endpoint
function undress($uuid, $uid, $base64, $chat_id, $paid){
    $url = 'https://igv2.undressai.tools/undress_get_resuls';
    
      $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
      
      $query = "SELECT * FROM users WHERE id = $chat_id";
    $res = $db->query($query);
    
    $user = $res->fetch_assoc();
    
    
      //  $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendMessage?chat_id=$chat_id&text=Prosess Undress";

// Send the message via GET request
   // file_get_contents($url);


//lingerie - bikini 2
//undress - goloe 1
//cowgirl - poza sex 1 - 3
//handjob - drochit - 4
//cum - konchit na telo



// high

// Data to send in the POST request (body)
$data = array(
    "uiid" => $uuid,
    "uid" => $uid,
    "masks" => $base64,
    "operation" => $user['mode'],
    "breast_size" => 0,
    "pubic_hair" => 0,
    "body_size" => 0,
    "product" => "UT",
    "image_format" => "base64",
    "prompt" => "",
    "watermark" => "",
    "quality" => "low"
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
    'Accept: application/json, text/plain, */*',
    'Accept-Language: ru-RU,ru;q=0.9,en-GB;q=0.8,en;q=0.7,en-US;q=0.6',
    'Authorization: Basic cG9ybmdlbjpwb3JuZ2Vu',
    'Cache-Control: no-cache',
    'Content-Type: application/json',
    'Pragma: no-cache',
    'Priority: u=1, i',
    'Sec-Fetch-Dest: empty',
    'Sec-Fetch-Mode: cors',
    'Sec-Fetch-Site: cross-site',
    'Referer: https://undresser.ai/',
    'Referrer-Policy: strict-origin-when-cross-origin'
));

// Set additional cURL options to mimic the JavaScript fetch options
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

// Execute the cURL request and get the response
$response = curl_exec($ch);

$data_josn = json_decode(''.$response.'', true);



   


// Check for any cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
} else {
    // Output the response from the API
   // echo $response;
    
    base64_to_jpeg_naked('data:image/png;base64,'.$data_josn['data'], $chat_id, $paid);
}

// Close the cURL session
curl_close($ch);
}


function base64_to_jpeg_naked($base64_string, $chat_id, $paid) {
    
      $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
      
      $query = "SELECT * FROM users WHERE id = $chat_id";
    $res = $db->query($query);
    
    $user = $res->fetch_assoc();
    
    //    $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendMessage?chat_id=$chat_id&text=Prosess Send Image";

// Send the message via GET request
   // file_get_contents($url);
   
   $base64Image = $base64_string;
   
       // Extract the base64-encoded part (remove the data URL prefix)
    $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
    
    // Decode the base64 string into raw image data
    $imageData = base64_decode($base64Image);
    
    // Check if decoding was successful
    if ($imageData === false) {
        die('Failed to decode base64 image.');
    }
    
    // Create an image from the decoded data (assuming it's JPEG for this example)
    $image = imagecreatefromstring($imageData);
    
    // Check if the image resource was created successfully
    if (!$image) {
        die('Failed to create image from string.');
    }
    
    // Apply Gaussian blur filter
    
    if($user['first'] == true){
         $blurAmount = 0; // Adjust the strength of the blur
    }else{
         $blurAmount = 0; // Adjust the strength of the blur
    }
    
   
    for ($i = 0; $i < $blurAmount; $i++) {
        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
    }
    
    // Start output buffering
    ob_start();
    
    // Output the image to a buffer as JPEG (or PNG/GIF if you need another format)
    imagejpeg($image);
    
    // Get the image data from the buffer
    $blurredImageData = ob_get_contents();
    
    // End the buffer and clean up
    ob_end_clean();
    
    // Encode the image data back into base64
    $blurredBase64 = base64_encode($blurredImageData);
    
    $first_blur_image = 'data:image/png;base64,' . $blurredBase64;
    
    
    $timestamp = time();
    // open the output file for writing
    $ifp = fopen('./naked/'.$chat_id.''.$timestamp.'.png', 'wb'); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode(',', $first_blur_image);

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode($data[ 1 ]));

    // clean up the file resource
    fclose($ifp); 
    
    
    
    if($paid == true){
        
          
        
        // URLs or file paths to the photos you want to send (can be file_ids, URLs, or local files)
        $mediaFiles = [
            "https://academytable.ru/bot/naked/$chat_id$timestamp.png",
  
            // You can also use file_ids or local files as shown below
            // 'path/to/photo4.jpg', // For local files, use 'attach://<file_name>'
            // 'FILE_ID', // Use an existing file_id from Telegram if available
        ];
        
        // Prepare the media array in the required format
        $mediaArray = [];
        foreach ($mediaFiles as $index => $file) {
            $mediaArray[] = [
                'type' => 'photo',  // Type of media (photo, video, document, etc.)
                'media' => $file,   // This could be a file_id, URL, or local file (with 'attach://<file_name>')
            ];
        }
        
        // API URL to send multiple media items (sendMediaGroup)
        $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendPaidMedia";
        
        // Prepare the post fields
        $postFields = [
            'chat_id' => $chat_id,  // The chat ID of the recipient
            'star_count' => 40,
            'protect_content' => true,
            'media' => json_encode($mediaArray),  // JSON-serialized array of media items
        ];
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Execute the request and get the response
        $response = curl_exec($ch);
        
        // Check for errors
        if(curl_errno($ch)) {
            //echo 'Error: ' . curl_error($ch);
        } else {
            // Decode and output the response (for debugging)
            $result = json_decode($response, true);
            if ($result['ok']) {
              //  echo 'Media group sent successfully!';
            } else {
               // echo 'Error: ' . $result['description'];
            }
        }
        
        
        
        // Close cURL session
        curl_close($ch);
        
            //   $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendPaidMedia?chat_id=$chat_id&star_count=5&media=[]";
    }else{
               $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendPhoto?chat_id=$chat_id&protect_content=true&has_spoiler=true&photo=https://academytable.ru/bot/naked/$chat_id$timestamp.png";
                file_get_contents($url);
    }
    
  
    
  
    


// Send the message via GET request

       
        $url2 = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendMessage?chat_id=$chat_id&text=Выберите режим (Select mode)/modes";

// Send the message via GET request
       file_get_contents($url2);
       
       imagedestroy($image);
       
         $query2 = "UPDATE `users` SET `first` = '0' WHERE `users`.`id` = $chat_id;";
        $res2 = $db->query($query2);

   // return 'https://academytable.ru/bot/images/'.$chat_id.''.$timestamp.'.png'; 
}


?>
