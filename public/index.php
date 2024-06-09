<?php

# Принимаем запрос
$input = file_get_contents('php://input');
$data = json_decode($input, TRUE);

// Логируем входящий запрос для отладки
file_put_contents('file.txt', '$data: '.print_r($data, 1)."\n", FILE_APPEND);

// Проверяем, что данные не пустые и корректные
if (is_null($data)) {
    // Если нет данных, можно вернуть HTTP статус 400 или просто завершить скрипт
    http_response_code(400);
    echo "No data provided";
    exit;
}

// Обрабатываем ручной ввод или нажатие на кнопку
$data = isset($data['callback_query']) ? $data['callback_query'] : $data['message'];

if (!isset($data['text']) && !isset($data['data'])) {
    http_response_code(400);
    echo "Invalid data";
    exit;
}

# Важные константы
define('TOKEN', '7310194104:AAGbJ-u1dQLOrYdg7NarAUyIHQ7rV40b5AI');

# Записываем сообщение пользователя
$message = mb_strtolower(($data['text'] ?? $data['data'] ?? 'unknown'),'utf-8');

# Обрабатываем сообщение
switch ($message)
{
    case 'текст':
        $method = 'sendMessage';
        $send_data = [
            'text'   => 'Вот мой ответ'
        ];
        break;

    case 'кнопки':
        $method = 'sendMessage';
        $send_data = [
            'text'   => 'Вот мои кнопки',
            'reply_markup' => [
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => 'Видео'],
                        ['text' => 'Кнопка 2'],
                    ],
                    [
                        ['text' => 'Кнопка 3'],
                        ['text' => 'Кнопка 4'],
                    ]
                ]
            ]
        ];
        break;

    case 'видео':
        $method = 'sendVideo';
        $send_data = [
            'video'   => 'https://chastoedov.ru/video/amo.mp4',
            'caption' => 'Вот мое видео',
            'reply_markup' => [
                'resize_keyboard' => true,
                'keyboard' => [
                    [
                        ['text' => 'Кнопка 1'],
                        ['text' => 'Кнопка 2'],
                    ],
                    [
                        ['text' => 'Кнопка 3'],
                        ['text' => 'Кнопка 4'],
                    ]
                ]
            ]
        ];
        break;

    default:
        $method = 'sendMessage';
        $send_data = [
            'text' => 'Не понимаю о чем вы :('
        ];
}

# Добавляем данные пользователя
if (isset($data['chat']['id'])) {
    $send_data['chat_id'] = $data['chat']['id'];
} else {
    echo "Chat ID is missing";
    exit;
}

$res = sendTelegram($method, $send_data);

function sendTelegram($method, $data, $headers = [])
{
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://api.telegram.org/bot' . TOKEN . '/' . $method,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array_merge(array("Content-Type: application/json"), $headers)
    ]);

    $result = curl_exec($curl);
    if (curl_errno($curl)) {
        file_put_contents('curl_errors.txt', curl_error($curl) . "\n", FILE_APPEND);
    }
    curl_close($curl);
    return (json_decode($result, 1) ? json_decode($result, 1) : $result);
}
