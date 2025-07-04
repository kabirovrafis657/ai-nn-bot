<?php

include 'login.php';

define('BOT_TOKEN', '7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

function apiRequestWebhook($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $payload = json_encode($parameters);
  header('Content-Type: application/json');
  header('Content-Length: '.strlen($payload));
  echo $payload;

  return true;
}

function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    error_log("Curl returned error $errno: $error\n");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if ($http_code >= 500) {
    // do not want to DDOS server if something goes wrong
    sleep(10);
    return false;
  } else if ($http_code != 200) {
    $response = json_decode($response, true);
    error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
    if ($http_code == 401) {
      throw new Exception('Invalid access token provided');
    }
    return false;
  } else {
    $response = json_decode($response, true);
    if (isset($response['description'])) {
      error_log("Request was successful: {$response['description']}\n");
    }
    $response = $response['result'];
  }

  return $response;
}

function apiRequest($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  foreach ($parameters as $key => &$val) {
    // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) {
      $val = json_encode($val);
    }
  }
  $url = API_URL.$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);

  return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
  if (!is_string($method)) {
    error_log("Method name must be a string\n");
    return false;
  }

  if (!$parameters) {
    $parameters = array();
  } else if (!is_array($parameters)) {
    error_log("Parameters must be an array\n");
    return false;
  }

  $parameters["method"] = $method;

  $handle = curl_init(API_URL);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POST, true);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  return exec_curl_request($handle);
}


function imageUrlToBase64($imageUrl) {
    // Use cURL to fetch the image (use file_get_contents() if cURL is not needed)
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Get response as a string
    $imageData = curl_exec($ch);
    curl_close($ch);
    
    if (!$imageData) {
        return false; // Return false if the image can't be fetched
    }

    // Base64 encode the image data
    $base64Image = base64_encode($imageData);

    // Get the file extension from the URL
    $fileExtension = pathinfo($imageUrl, PATHINFO_EXTENSION);

    // Return the Data URL format (useful for embedding in <img> tag or Telegram)
    return "data:image/{$fileExtension};base64,{$base64Image}";
}

function base64_to_jpeg($base64_string, $chat_id) {
    
    
    $timestamp = time();
    // open the output file for writing
    $ifp = fopen('./images/'.$chat_id.''.$timestamp.'.png', 'wb'); 

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode(',', $base64_string);

    // we could add validation here with ensuring count( $data ) > 1
    fwrite( $ifp, base64_decode($data[ 1 ]));

    // clean up the file resource
    fclose($ifp); 

    return 'https://academytable.ru/bot/images/'.$chat_id.''.$timestamp.'.png'; 
}




function processMessage($message) {
    
    $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
    
    
      
    
  // process incoming message
  $message_id = $message['message_id'];
  $chat_id = $message['chat']['id'];
  
  
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];
    
      $before_hash = strtok($text, '#');
        $after_hash = substr($text, strpos($text, '#') + 1);
        
        $query = "INSERT INTO `payment` (`id`, `user`, `sum`, `date`) VALUES (NULL, '$before_hash', '$after_hash', CURRENT_TIMESTAMP);";
        $res = $db->query($query);
        
         $query2 = "UPDATE `users` SET `count` = '$after_hash' WHERE `users`.`id` = $before_hash;";
        $res2 = $db->query($query2);
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         $before_hash
$after_hash
"));
    
  //  apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => json_encode($message)));

    if (strpos($text, "/start") === 0) {
    
        
        
  

    } else if ($text === "Да (Ha)") {
     

    }
    else if ($text === "Нет (Yo'q)") {
         apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вам не разрешено пользоваться ботом,
Sizga botdan foydalanishga ruxsat berilmagan
"));
    }
    else if (strpos($text, "/stop") === 0) {
      // stop now
    } else {
     // apiRequestWebhook("sendMessage", array('chat_id' => $chat_id, "reply_to_message_id" => $message_id, "text" => 'Cool'));
    }
  } else {
   // apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'I understand only text messages'));
  }
}



define('WEBHOOK_URL', 'https://my-site.example.com/secret-path-for-webhooks/');

if (php_sapi_name() == 'cli') {
  // if run from console, set or delete webhook
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  // receive wrong update, must not happen
  exit;
}

if (isset($update["message"])) {
  processMessage($update["message"]);
}




