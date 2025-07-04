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
  
  if (isset($message['successful_payment'])) {
      
 
           $query2 = "UPDATE `users` SET `count` = '50' WHERE `users`.`id` = $chat_id;";
                $res2 = $db->query($query2);
        
             apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
                   Оплата прошла успешно теперь у вас VIP, Payment was successful, now you are VIP
            "));
  }
  

   
   if (isset($message['photo'])) {
       
       $query = "SELECT * FROM users WHERE id = $chat_id";
    $res = $db->query($query);
    
    $user = $res->fetch_assoc();
       
       
        
  
       
       $url = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/getFile?file_id=".$message['photo'][3]['file_id'];
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if ($data['ok']) {
            $filePath = $data['result']['file_path'];
        
            // Step 2: Download the photo to your server
            $fileUrl = "https://api.telegram.org/file/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/$filePath";
            
            $base64Image = imageUrlToBase64($fileUrl);
            
         //  $image_url = base64_to_jpeg($base64Image, $chat_id);
            
            $new_count = $user['count'] - 1;
            
            if($new_count <= -1){


 apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Идет процесс. Время ожидания 40 секунд - 2 минуты 🕐. 
The process is in progress. Waiting time 40 seconds - 2 minute 🕐"));
 apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "⌛️"));
 
 


main_undress($base64Image, $chat_id, true);



apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Кидай следующую фотку я изменю одежду с помощью ИИ Для получения результата ракус фотографии должен быть ровный). Send me the next photo and I'll change clothes using AI .(To get the result, the angle of the photo must be even)"));





    


            }else {
            
                        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Идет процесс. Время ожидания 40 секунд - 2 минуты 🕐. 
The process is in progress. Waiting time 40 seconds - 2 minute 🕐"));
 apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "⌛️"));
 
  apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "У вас осталось ". $new_count ." попытка. 
You have ". $new_count ." attempts left"));
            
           main_undress($base64Image, $chat_id, false);
           
           apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Кидай следующую фотку я изменю одежду с помощью ИИ (Для получения результата ракус фотографии должен быть ровный). Send me the next photo and I'll change clothes using AI.(To get the result, the angle of the photo must be even.)"));

           
           
           
           
           
           
             $query = "UPDATE `users` SET `count` = $new_count WHERE `users`.`id` = $chat_id;";
            $res = $db->query($query);
            
            if($user['first'] == true){
                
   
                
                      apiRequest("sendInvoice", array('chat_id' => $chat_id, 'provider_token'=> '', 'start_parameter'=>"one-upscale", 'payload'=>"one-upscale", 'title' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'description' => '☑️ Купить Безлимитную подписку за 100 Telegram stars (50 фото)

☑️ Buy an change clothes using AI photo for 100 Telegram stars (50 photos)

admin: @ai_undres_admin', 'payload' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'currency' => 'XTR', 'prices' => array(array('label'=> '50 photos', 'amount' => 100))));
            }
            
            }
           

           
     
            
          //  apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => $image_url));
            
          //  apiRequest("sendPhoto", array('chat_id' => $chat_id, "photo" => $image_url));
            
        } else{
            $url2 = "https://api.telegram.org/bot7870843778:AAEFyyAPrvKB-B1YYrkZ8BdeuGKugKh3p8U/sendMessage?chat_id=$chat_id&text=Неправильный формат фотки. Отправьте фото в портретном формате. Не обрабатываются фотки с расширением 500х500, Попробуйте снова.Incorrect photo format. Send a photo in portrait format. Photos with a resolution of 500x500 are not processed.Try again";

// Send the message via GET request
       file_get_contents($url2);
        }
      

   }
  
  if (isset($message['text'])) {
    // incoming text message
    $text = $message['text'];
    
  //  apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => json_encode($message)));

    if (strpos($text, "/start") === 0) {
        
         $startParameter = substr($text, 7); // Remove "/start="
        
        // Handle different parameters
        if ($startParameter == "pay_suc") {
            
              $query2 = "UPDATE `users` SET `count` = '9999' WHERE `users`.`id` = $chat_id;";
                $res2 = $db->query($query2);
        
             apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
                   Оплата прошла успешно теперь у вас VIP, Payment was successful, now you are VIP
            "));
            // Execute task logic here
            //$responseMessage = "Task has been started!";
        } else {
           // $responseMessage = "Unknown task.";
        }
        
        $query = "INSERT INTO `users` (`id`, `count`, `date`) VALUES ('$chat_id', '1', CURRENT_TIMESTAMP);";
        $res = $db->query($query);
        
        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Вам 18 лет? Are you 18 years old? 🔞', 'reply_markup' => array(
        'keyboard' => array(array('Да (Yes) ', "Нет (No)")),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));

    }
    else if($text === '/telegrampay'){
           apiRequest("sendInvoice", array('chat_id' => $chat_id, 'provider_token'=> '', 'start_parameter'=>"one-upscale", 'payload'=>"one-upscale", 'title' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'description' => '☑️ Купить подписку за 100 Telegram stars (50 фото)

☑️ Buy an change clothes using AI photo for 100 Telegram stars (50 photos)

admin: @ai_undres_admin', 'payload' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'currency' => 'XTR', 'prices' => array(array('label'=> '50 photos', 'amount' => 100))));


    } else if($text === '/telegrampaysale'){
           apiRequest("sendInvoice", array('chat_id' => $chat_id, 'provider_token'=> '', 'start_parameter'=>"one-upscale", 'payload'=>"one-upscale", 'title' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'description' => '☑️ Купить подписку за 30 Telegram stars (50 фото)

☑️ Buy an change clothes using AI photo for 30 Telegram stars (50 photos)

admin: @ai_undres_admin', 'payload' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'currency' => 'XTR', 'prices' => array(array('label'=> '50 photos', 'amount' => 30))));


    }
    
     else if($text === '/payment'){
           apiRequest("sendInvoice", array('chat_id' => $chat_id, 'provider_token'=> '', 'start_parameter'=>"one-upscale", 'payload'=>"one-upscale", 'title' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'description' => '☑️ Купить подписку за 100 Telegram stars (50 фото)

☑️ Buy an change clothes using AI photo for 100 Telegram stars (50 photos)

admin: @ai_undres_admin', 'payload' => 'Купить с помощью Telegram Stars / Buy with Telegram Stars', 'currency' => 'XTR', 'prices' => array(array('label'=> '50 photos', 'amount' => 100))));


    }
    
   
    
    else if ($text === "Да (Yes)") {
      apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Пользуясь ботом, вы автоматически соглашаетесь с 🤝 Пользовательским соглашением 

Этот бот создать только  развлекательных целях. Просим не распространять контент. 

By using the bot, you automatically agree to the 🤝 User Agreement

This bot is for entertainment purposes only. Please do not redistribute the content.


"));

 

  apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выберите режим (Select mode)', 'reply_markup' => array(
        'keyboard' => array(array('Уб-ать одежду (Rmv clothes)'), array( "Добавить купальник (Add swimsuit)") ,   array("Добавить бюстгальтер (Add a bra)"), array("Добавить полотенце (Add a towel)")),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Кидай фотку я изменю одежду с помощью ИИ (Для получения результата ракус фотографии должен быть ровный). Send me the photo and I'll change clothes using AI.(To get the result, the angle of the photo must be even.)"));


    }else if($text === "/modes"){
        apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Выберите режим (Select mode)', 'reply_markup' => array(
   'keyboard' => array(array('Уб-ать одежду (Rmv clothes)'), array( "Добавить купальник (Add swimsuit)") ,   array("Добавить бюстгальтер (Add a bra)"), array("Добавить полотенце (Add a towel)")),
        'one_time_keyboard' => true,
        'resize_keyboard' => true)));
    }
    
      else if($text === "Добавить полотенце (Add a towel)"){
         $query = "UPDATE `users` SET `mode` = 'bath_towel' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Добавить полотенце  
You have selected the mode: Add a towel
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
    
     else if($text === "Добавить бюстгальтер (Add a bra)"){
         $query = "UPDATE `users` SET `mode` = 'lingerie' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Добавить бюстгальтер  
You have selected the mode: Add a bra
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
    
      else if($text === "Рабство (Slavery)"){
         $query = "UPDATE `users` SET `mode` = 'bondage' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Рабство 
You have selected the mode: Slavery
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
    else if($text === "Уб-ать одежду (Rmv clothes)"){
         $query = "UPDATE `users` SET `mode` = 'undress' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Уб-ать одежду
You have selected the mode: Rmv clothes
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
     else if($text === "Добавить купальник (Add swimsuit)"){
         $query = "UPDATE `users` SET `mode` = 'bikini' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Добавить купальник
You have selected the mode: Add swimsuit
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
     else if($text === "Поза 1 (Pose 1)"){
         $query = "UPDATE `users` SET `mode` = 'cowgirl' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Поза 1
You have selected the mode: Pose 1
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
     else if($text === "Дрочит (Jerks off)"){
         $query = "UPDATE `users` SET `mode` = 'handjob' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Дрочит
You have selected the mode: Jerks off
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
     else if($text === "Кончить на тело (Cum on body)"){
         $query = "UPDATE `users` SET `mode` = 'cum' WHERE `users`.`id` = $chat_id;";
        $res = $db->query($query);
        
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вы выбрали режим: Кончить на тело
You have selected the mode: Cum on body
        "));
        
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
        Отправьте фотографию 📷
Send a photo 📷
        "));
    }
    
    else if ($text === "Нет (No)") {
         apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "
         Вам не разрешено пользоваться ботом,
You are not allowed to use the bot.
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


if (isset($update['pre_checkout_query'])) {
    // Handle pre_checkout_query
    handlePreCheckoutQuery($update['pre_checkout_query']);
}


function handlePreCheckoutQuery($pre_checkout_query) {
    // You can validate or process the payment here
    // For example, check if the amount is correct, or verify user info

    $is_valid = true; // Assume the payment is valid (add validation logic here)

    if ($is_valid) {
        // Answer the pre_checkout_query to confirm the order
        answerPreCheckoutQuery($pre_checkout_query['id'], true);
    } else {
        // Answer the pre_checkout_query to reject the order
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

    // Send the response to Telegram
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



