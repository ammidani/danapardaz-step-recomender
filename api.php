<?php
header('Content-Type: application/json');

// Function to read config
function get_config() {
    $config_path = 'app/data/config.json';
    if (!file_exists($config_path)) {
        http_response_code(500);
        echo json_encode(['error' => 'Configuration file not found.']);
        exit;
    }
    $json = file_get_contents($config_path);
    return json_decode($json, true);
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['answers'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input.']);
    exit;
}

$answers = $input['answers'];
$config = get_config();
$api_settings = $config['api_settings'];
$prompt_template = $config['prompt_template'];

// Replace placeholders in the prompt template
$prompt = $prompt_template;
foreach ($answers as $key => $value) {
    $prompt = str_replace("{{{$key}}}", $value, $prompt);
}

// Prepare the request to the AI service
$url = '';
$headers = ['Content-Type: application/json'];
$body = [];

switch ($api_settings['provider']) {
    case 'gemini':
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$api_settings['api_key']}";
        $body = ['contents' => [['parts' => [['text' => $prompt]]]]];
        break;
    case 'qwen':
        $url = 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation';
        $headers[] = "Authorization: Bearer {$api_settings['api_key']}";
        $body = ['model' => 'qwen-turbo', 'input' => ['prompt' => $prompt]];
        break;
    case 'openrouter':
        $url = 'https://openrouter.ai/api/v1/chat/completions';
        $headers[] = "Authorization: Bearer {$api_settings['api_key']}";
        $body = ['model' => 'google/gemini-flash-1.5', 'messages' => [['role' => 'user', 'content' => $prompt]]];
        break;
    case 'custom':
        $url = $api_settings['api_url'];
        $headers[] = "Authorization: Bearer {$api_settings['api_key']}";
        $body = ['prompt' => $prompt];
        break;
    default:
        http_response_code(500);
        echo json_encode(['error' => 'Invalid AI provider configured.']);
        exit;
}

// Make the cURL request
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    http_response_code(502); // Bad Gateway
    echo json_encode(['error' => 'Failed to get response from AI service.', 'details' => $response]);
    exit;
}

// Extract the text from the AI response
$responseData = json_decode($response, true);
$text = '';

switch ($api_settings['provider']) {
    case 'gemini':
        $text = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Error parsing response.';
        break;
    case 'qwen':
        $text = $responseData['output']['text'] ?? 'Error parsing response.';
        break;
    case 'openrouter':
        $text = $responseData['choices'][0]['message']['content'] ?? 'Error parsing response.';
        break;
    case 'custom':
        $text = $responseData['response'] ?? $responseData['text'] ?? json_encode($responseData);
        break;
}

echo json_encode(['response' => $text]);
