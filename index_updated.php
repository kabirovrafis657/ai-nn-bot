<?php

include 'login.php';
include 'languages.php';

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
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $imageData = curl_exec($ch);
    curl_close($ch);
    
    if (!$imageData) {
        return false;
    }

    $base64Image = base64_encode($imageData);
    $fileExtension = pathinfo($imageUrl, PATHINFO_EXTENSION);

    return "data:image/{$fileExtension};base64,{$base64Image}";
}

function getUserLanguage($chat_id) {
    $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
    $query = "SELECT language FROM users WHERE id = $chat_id";
    $res = $db->query($query);
    
    if ($res && $user = $res->fetch_assoc()) {
        return $user['language'] ?? 'en';
    }
    return 'en';
}

function setUserLanguage($chat_id, $language) {
    $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
    $query = "UPDATE users SET language = '$language' WHERE id = $chat_id";
    $db->query($query);
}

function processMessage($message) {
    $db = mysqli_connect("localhost", "develosh_ai", "Shaha2001", "develosh_ai");
    
    $message_id = $message['message_id'];
    $chat_id = $message['chat']['id'];
    
    if (isset($message['successful_payment'])) {
        $query2 = "UPDATE `users` SET `count` = `count` + 1 WHERE `users`.`id` = $chat_id;";
        $res2 = $db->query($query2);
        
        $lang = getUserLanguage($chat_id);
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('payment_success', $lang)));
    }
   
    if (isset($message['photo'])) {
        $query = "SELECT * FROM users WHERE id = $chat_id";
        $res = $db->query($query);
        $user = $res->fetch_assoc();
        $lang = $user['language'] ?? 'en';
        
        $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/getFile?file_id=".$message['photo'][3]['file_id'];
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if ($data['ok']) {
            $filePath = $data['result']['file_path'];
            $fileUrl = "https://api.telegram.org/file/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/$filePath";
            $base64Image = imageUrlToBase64($fileUrl);
            
            $new_count = $user['count'] - 1;
            
            if($new_count <= -1){
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('processing', $lang)));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('waiting', $lang)));
                
                main_undress($base64Image, $chat_id, true);
                
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_next_photo', $lang)));
            } else {
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('processing', $lang)));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('waiting', $lang)));
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('attempts_left', $lang, ['count' => $new_count])));
                
                main_undress($base64Image, $chat_id, false);
                
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_next_photo', $lang)));
                
                $query = "UPDATE `users` SET `count` = $new_count WHERE `users`.`id` = $chat_id;";
                $res = $db->query($query);
                
                if($user['first'] == true){
                    apiRequest("sendInvoice", array(
                        'chat_id' => $chat_id, 
                        'provider_token'=> '', 
                        'start_parameter'=>"one-photo", 
                        'payload'=>"one-photo", 
                        'title' => Languages::get('buy_photo', $lang), 
                        'description' => Languages::get('buy_description', $lang), 
                        'payload' => Languages::get('buy_photo', $lang), 
                        'currency' => 'XTR', 
                        'prices' => array(array('label'=> '1 photo', 'amount' => 19))
                    ));
                }
            }
        } else {
            $lang = getUserLanguage($chat_id);
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('incorrect_format', $lang)));
        }
    }
  
    if (isset($message['text'])) {
        $text = $message['text'];
        $lang = getUserLanguage($chat_id);

        if (strpos($text, "/start") === 0) {
            $startParameter = substr($text, 7);
            
            if ($startParameter == "pay_suc") {
                $query2 = "UPDATE `users` SET `count` = '9999' WHERE `users`.`id` = $chat_id;";
                $res2 = $db->query($query2);
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('payment_success', $lang)));
            } else {
                $query = "INSERT INTO `users` (`id`, `count`, `date`, `language`) VALUES ('$chat_id', '1', CURRENT_TIMESTAMP, 'en') ON DUPLICATE KEY UPDATE id=id;";
                $res = $db->query($query);
                
                apiRequestJson("sendMessage", array(
                    'chat_id' => $chat_id, 
                    "text" => Languages::get('select_language', 'en'), 
                    'reply_markup' => array(
                        'keyboard' => array(
                            array('🇺🇸 English', '🇷🇺 Русский'),
                            array('🇪🇸 Español')
                        ),
                        'one_time_keyboard' => true,
                        'resize_keyboard' => true
                    )
                ));
            }
        }
        else if($text === '🇺🇸 English') {
            setUserLanguage($chat_id, 'en');
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('language_selected', 'en')));
            showAgeVerification($chat_id, 'en');
        }
        else if($text === '🇷🇺 Русский') {
            setUserLanguage($chat_id, 'ru');
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('language_selected', 'ru')));
            showAgeVerification($chat_id, 'ru');
        }
        else if($text === '🇪🇸 Español') {
            setUserLanguage($chat_id, 'es');
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('language_selected', 'es')));
            showAgeVerification($chat_id, 'es');
        }
        else if($text === '/telegrampay'){
            apiRequest("sendInvoice", array(
                'chat_id' => $chat_id, 
                'provider_token'=> '', 
                'start_parameter'=>"one-photo", 
                'payload'=>"one-photo", 
                'title' => Languages::get('buy_photo', $lang), 
                'description' => Languages::get('buy_description', $lang), 
                'payload' => Languages::get('buy_photo', $lang), 
                'currency' => 'XTR', 
                'prices' => array(array('label'=> '1 photo', 'amount' => 19))
            ));
        }
        else if ($text === Languages::get('yes', $lang) || $text === "Да" || $text === "Yes" || $text === "Sí") {
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('agreement', $lang)));
            showModeSelection($chat_id, $lang);
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_next_photo', $lang)));
        }
        else if($text === "/modes"){
            showModeSelection($chat_id, $lang);
        }
        else if($text === Languages::get('add_towel', $lang) || strpos($text, 'towel') !== false || strpos($text, 'полотенце') !== false || strpos($text, 'toalla') !== false){
            $query = "UPDATE `users` SET `mode` = 'bath_towel' WHERE `users`.`id` = $chat_id;";
            $res = $db->query($query);
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('mode_selected', $lang) . " " . Languages::get('add_towel', $lang)));
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_photo', $lang)));
        }
        else if($text === Languages::get('add_bra', $lang) || strpos($text, 'bra') !== false || strpos($text, 'бюстгальтер') !== false || strpos($text, 'sujetador') !== false){
            $query = "UPDATE `users` SET `mode` = 'lingerie' WHERE `users`.`id` = $chat_id;";
            $res = $db->query($query);
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('mode_selected', $lang) . " " . Languages::get('add_bra', $lang)));
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_photo', $lang)));
        }
        else if($text === Languages::get('remove_clothes', $lang) || strpos($text, 'remove') !== false || strpos($text, 'убрать') !== false || strpos($text, 'quitar') !== false){
            $query = "UPDATE `users` SET `mode` = 'undress' WHERE `users`.`id` = $chat_id;";
            $res = $db->query($query);
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('mode_selected', $lang) . " " . Languages::get('remove_clothes', $lang)));
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_photo', $lang)));
        }
        else if($text === Languages::get('add_swimsuit', $lang) || strpos($text, 'swimsuit') !== false || strpos($text, 'купальник') !== false || strpos($text, 'traje de baño') !== false){
            $query = "UPDATE `users` SET `mode` = 'bikini' WHERE `users`.`id` = $chat_id;";
            $res = $db->query($query);
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('mode_selected', $lang) . " " . Languages::get('add_swimsuit', $lang)));
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('send_photo', $lang)));
        }
        else if ($text === Languages::get('no', $lang) || $text === "Нет" || $text === "No") {
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => Languages::get('age_restriction', $lang)));
        }
    }
}

function showAgeVerification($chat_id, $lang) {
    apiRequestJson("sendMessage", array(
        'chat_id' => $chat_id, 
        "text" => Languages::get('welcome', $lang), 
        'reply_markup' => array(
            'keyboard' => array(array(Languages::get('yes', $lang), Languages::get('no', $lang))),
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        )
    ));
}

function showModeSelection($chat_id, $lang) {
    apiRequestJson("sendMessage", array(
        'chat_id' => $chat_id, 
        "text" => Languages::get('select_mode', $lang), 
        'reply_markup' => array(
            'keyboard' => array(
                array(Languages::get('remove_clothes', $lang)), 
                array(Languages::get('add_swimsuit', $lang)),   
                array(Languages::get('add_bra', $lang)), 
                array(Languages::get('add_towel', $lang))
            ),
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        )
    ));
}

define('WEBHOOK_URL', 'https://my-site.example.com/secret-path-for-webhooks/');

if (php_sapi_name() == 'cli') {
  apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
  exit;
}

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
  exit;
}

if (isset($update["message"])) {
  processMessage($update["message"]);
}

if (isset($update['pre_checkout_query'])) {
    handlePreCheckoutQuery($update['pre_checkout_query']);
}

function handlePreCheckoutQuery($pre_checkout_query) {
    $is_valid = true;

    if ($is_valid) {
        answerPreCheckoutQuery($pre_checkout_query['id'], true);
    } else {
        answerPreCheckoutQuery($pre_checkout_query['id'], false, "Invalid payment details");
    }
}

function answerPreCheckoutQuery($query_id, $ok, $error_message = "") {
    $telegram_bot_token = "7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U";
    $url = "https://api.telegram.org/bot$telegram_bot_token/answerPreCheckoutQuery";

    $data = [
        'pre_checkout_query_id' => $query_id,
        'ok' => $ok,
    ];

    if (!$ok) {
        $data['error_message'] = $error_message;
    }

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}

?>