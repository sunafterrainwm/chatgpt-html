<?php

define('PHP_IS_CLI', php_sapi_name() == 'cli');

if (PHP_IS_CLI) {
    if ($argc < 2) {
        echo "usage: {$argv[0]} <prompt>";
        exit(1);
    }
    $prompt = $argv[1];
} else {
    if (
        !isset($_SERVER) ||
        $_SERVER['REQUEST_METHOD'] !== 'POST'
    ) {
        http_response_code(405);
        exit();
    } else if (
        !isset($_POST['prompt']) ||
        $_POST['prompt'] === ''
    ) {
        http_response_code(400);
        echo 'parameter prompt lost.';
        exit();
    }
    $prompt = $_POST['promote'];
}

$openAI_token = '123456';
$completions_options = [
    'max_tokens' => 2048,
    'temperature' => 0.5,
    'top_p' => 1,
    'frequency_penalty' => 0,
    'presence_penalty' => 0,
    'model' => 'text-davinci-003'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL , "https://api.openai.com/v1/completions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-type: application/json',
    'Accept:application/json',
    "Authorization: "Bearer $openAI_token"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode(
        ['prompt' => $prompt] + $completions_options
    )
);
curl_setopt(
    $ch,
    CURLOPT_USERAGENT,
    isset($_SERVER) && isset($_SERVER['HTTP_USER_AGENT'])
        ? $_SERVER['HTTP_USER_AGENT'] 
        : 'ChatGPTHtml/v1 (+https://github.com/sunafterrainwm/chatgpt-html)'
);
curl_setopt(
    $ch,
    CURLOPT_HEADERFUNCTION,
    function ($curl, $header) {
        if (strpos($header, ':')) {
            header($header);
        }
        return strlen($header);
    }
);

$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

http_response_code($httpcode);
echo $result;
