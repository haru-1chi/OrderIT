<?php
//send_telegram.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $_POST['message'];
    $chatIds = ['6810241495'
    // , '7542936104', '6684593322', '7551836315', '7929326845', '6221065459'
];

    $botToken = '7695900629:AAEA5RLovP1QDQy8w4jc8PMAvAoj1HZ6Ivo';
    $url = "https://api.telegram.org/bot$botToken/sendMessage";

    foreach ($chatIds as $chatId) {
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_exec($ch);
        curl_close($ch);
    }
}

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $message = $_POST['message'] ?? '';
//     $chatIds = [
//         '6810241495',
//         '7542936104',
//         '6684593322',
//         '7551836315',
//         '7929326845',
//         '6221065459'
//     ];

//     $botToken = '7695900629:AAEA5RLovP1QDQy8w4jc8PMAvAoj1HZ6Ivo';

//     // Check if photo is uploaded
//     if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
//         $photoPath = $_FILES['photo']['tmp_name'];

//         foreach ($chatIds as $chatId) {
//             $url = "https://api.telegram.org/bot$botToken/sendPhoto";

//             $postFields = [
//                 'chat_id' => $chatId,
//                 'caption' => $message,
//                 'parse_mode' => 'HTML',
//                 'photo' => new CURLFile($photoPath)
//             ];

//             $ch = curl_init();
//             curl_setopt($ch, CURLOPT_URL, $url);
//             curl_setopt($ch, CURLOPT_POST, true);
//             curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//             curl_exec($ch);
//             curl_close($ch);
//         }
//     } else {
//         // No image, send as text only
//         foreach ($chatIds as $chatId) {
//             $url = "https://api.telegram.org/bot$botToken/sendMessage";
//             $data = [
//                 'chat_id' => $chatId,
//                 'text' => $message,
//                 'parse_mode' => 'HTML'
//             ];

//             $ch = curl_init();
//             curl_setopt($ch, CURLOPT_URL, $url);
//             curl_setopt($ch, CURLOPT_POST, true);
//             curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//             curl_exec($ch);
//             curl_close($ch);
//         }
//     }
// }
