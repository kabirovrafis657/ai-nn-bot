<?php

class Languages {
    private static $languages = [
        'en' => [
            'welcome' => "Welcome! Are you 18 years old? 🔞",
            'yes' => "Yes",
            'no' => "No",
            'age_restriction' => "You are not allowed to use the bot.",
            'agreement' => "By using the bot, you automatically agree to the 🤝 User Agreement\n\nThis bot is for entertainment purposes only. Please do not redistribute the content.",
            'select_mode' => "Select mode",
            'remove_clothes' => "Remove clothes",
            'add_swimsuit' => "Add swimsuit", 
            'add_bra' => "Add a bra",
            'add_towel' => "Add a towel",
            'send_photo' => "Send a photo 📷",
            'mode_selected' => "You have selected the mode:",
            'processing' => "Processing... Please wait 40 seconds - 2 minutes 🕐",
            'waiting' => "⌛️",
            'send_next_photo' => "Send me the next photo and I'll change clothes using AI. (To get the result, the angle of the photo must be even.)",
            'photo_error' => "Photo error. Try another photo. Send a photo in good quality from another angle.",
            'incorrect_format' => "Incorrect photo format. Send a photo in portrait format. Photos with a resolution of 500x500 are not processed. Try again.",
            'payment_success' => "Payment was successful, now you have VIP",
            'buy_photo' => "Buy photo for 19 Telegram Stars",
            'buy_description' => "☑️ Buy one AI photo for 19 Telegram stars\n\n☑️ High quality AI processing\n\nadmin: @ai_undres_admin",
            'language_selected' => "Language selected: English 🇺🇸",
            'select_language' => "Select language / Выберите язык",
            'attempts_left' => "You have {count} attempts left"
        ],
        'ru' => [
            'welcome' => "Добро пожаловать! Вам 18 лет? 🔞",
            'yes' => "Да",
            'no' => "Нет", 
            'age_restriction' => "Вам не разрешено пользоваться ботом.",
            'agreement' => "Пользуясь ботом, вы автоматически соглашаетесь с 🤝 Пользовательским соглашением\n\nЭтот бот создан только для развлекательных целей. Просим не распространять контент.",
            'select_mode' => "Выберите режим",
            'remove_clothes' => "Убрать одежду",
            'add_swimsuit' => "Добавить купальник",
            'add_bra' => "Добавить бюстгальтер", 
            'add_towel' => "Добавить полотенце",
            'send_photo' => "Отправьте фотографию 📷",
            'mode_selected' => "Вы выбрали режим:",
            'processing' => "Идет процесс. Время ожидания 40 секунд - 2 минуты 🕐",
            'waiting' => "⌛️",
            'send_next_photo' => "Кидай следующую фотку я изменю одежду с помощью ИИ (Для получения результата ракурс фотографии должен быть ровный).",
            'photo_error' => "Ошибка фото. Попробуйте другое фото. Отправьте фото в хорошем качестве с другого ракурса.",
            'incorrect_format' => "Неправильный формат фотки. Отправьте фото в портретном формате. Не обрабатываются фотки с расширением 500х500, Попробуйте снова.",
            'payment_success' => "Оплата прошла успешно теперь у вас VIP",
            'buy_photo' => "Купить фото за 19 Telegram Stars",
            'buy_description' => "☑️ Купить одно AI фото за 19 Telegram stars\n\n☑️ Высокое качество обработки ИИ\n\nadmin: @ai_undres_admin",
            'language_selected' => "Выбран язык: Русский 🇷🇺",
            'select_language' => "Выберите язык / Select language",
            'attempts_left' => "У вас осталось {count} попытка"
        ],
        'es' => [
            'welcome' => "¡Bienvenido! ¿Tienes 18 años? 🔞",
            'yes' => "Sí",
            'no' => "No",
            'age_restriction' => "No tienes permitido usar el bot.",
            'agreement' => "Al usar el bot, automáticamente aceptas el 🤝 Acuerdo de Usuario\n\nEste bot es solo para fines de entretenimiento. Por favor no redistribuyas el contenido.",
            'select_mode' => "Seleccionar modo",
            'remove_clothes' => "Quitar ropa",
            'add_swimsuit' => "Añadir traje de baño",
            'add_bra' => "Añadir sujetador",
            'add_towel' => "Añadir toalla",
            'send_photo' => "Envía una foto 📷",
            'mode_selected' => "Has seleccionado el modo:",
            'processing' => "Procesando... Espera 40 segundos - 2 minutos 🕐",
            'waiting' => "⌛️",
            'send_next_photo' => "Envíame la siguiente foto y cambiaré la ropa usando IA. (Para obtener el resultado, el ángulo de la foto debe ser uniforme.)",
            'photo_error' => "Error de foto. Prueba otra foto. Envía una foto de buena calidad desde otro ángulo.",
            'incorrect_format' => "Formato de foto incorrecto. Envía una foto en formato vertical. Las fotos con resolución 500x500 no se procesan. Inténtalo de nuevo.",
            'payment_success' => "El pago fue exitoso, ahora tienes VIP",
            'buy_photo' => "Comprar foto por 19 Telegram Stars",
            'buy_description' => "☑️ Compra una foto AI por 19 Telegram stars\n\n☑️ Procesamiento AI de alta calidad\n\nadmin: @ai_undres_admin",
            'language_selected' => "Idioma seleccionado: Español 🇪🇸",
            'select_language' => "Seleccionar idioma / Select language",
            'attempts_left' => "Te quedan {count} intentos"
        ]
    ];

    public static function get($key, $lang = 'en', $params = []) {
        $text = self::$languages[$lang][$key] ?? self::$languages['en'][$key] ?? $key;
        
        // Replace parameters
        foreach ($params as $param => $value) {
            $text = str_replace('{' . $param . '}', $value, $text);
        }
        
        return $text;
    }

    public static function getLanguages() {
        return array_keys(self::$languages);
    }
}

?>