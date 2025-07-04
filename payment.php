<?php

$uuid = strtoupper(bin2hex(openssl_random_pseudo_bytes(16)));

$merchant_id = 'df685b2f-6584-4fe4-a939-25c5b2a2de60'; // ID Вашего магазина
$amount = 1000; // Сумма к оплате
$currency = 'RUB'; // Валюта заказа
$secret = '3e55f2242f4d20bc251da8f59145f2ed'; // Секретный ключ №1 из настроек магазина
$order_id = $uuid; // Идентификатор заказа в Вашей системе
$sign = hash('sha256', implode(':', [$merchant_id, $amount, $currency, $secret, $order_id]));
$desc = 'Order Payment'; // Описание заказа
$lang = 'ru'; // Язык формы

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://aaio.so/merchant/get_pay_url');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'merchant_id' => $merchant_id,
    'amount' => $amount,
    'currency' => $currency,
    'order_id' => $order_id,
    'sign' => $sign,
    'desc' => $desc,
    'lang' => $lang
]));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Таймаут подключения к нашему серверу
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Таймаут обработки запроса

$result = curl_exec($ch); // Ответ
$http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE); // Код ответа

if (curl_errno($ch)) {
	die('Connect error:' . curl_error($ch)); // Вывод ошибки соединения
}
curl_close($ch);

if(!in_array($http_code, [200, 400, 401])) {
	die('Response code: ' . $http_code); // Вывод неизвестного кода ответа
}

$decoded = json_decode($result, true); // Парсинг результа. На выходе получаем массив данных

if(json_last_error() !== JSON_ERROR_NONE) {
	die('Не удалось пропарсить ответ');
}

if($decoded['type'] == 'success') {
    header("Location: ". str_replace("https://aaio-pay.io/", "https://aaio.so/", $decoded['url']));
       // Вывод результата
} else {
	die('Ошибка: ' . $decoded['message']); // Вывод ошибки
}