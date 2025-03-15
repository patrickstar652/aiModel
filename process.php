<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 確保請求方式為 POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "請使用 POST 方式傳送資料"]);
    exit;
}

// 取得使用者輸入 & 來自前端的歷史紀錄
$user_input = $_POST["user_input"] ?? "";
$chat_history = json_decode($_POST["chat_history"] ?? "[]", true); // 🔥 來自前端的對話紀錄

// **檢查 API 是否有收到 `chat_history`**
if (!is_array($chat_history)) {
    echo json_encode(["error" => "無效的對話紀錄格式"]);
    exit;
}

// 🔥 **確保每次請求都帶上 "system" 訊息**
$messages = [
    ["role" => "system", "content" => "請用繁體中文回答問題"]
];

// **合併前端傳來的歷史對話，但不重複加入 user 訊息**
foreach ($chat_history as $message) {
    if (!isset($message["role"]) || !isset($message["content"])) {
        continue; // 過濾錯誤訊息
    }
    $messages[] = $message;
}

// **加入當前使用者輸入**
$messages[] = ["role" => "user", "content" => $user_input];

// **API 設置**
$api_url = "https://api.groq.com/openai/v1/chat/completions";
$api_token = "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"; // 🔥 請填入你的 API Key

$headers = [
    "Authorization: Bearer $api_token",
    "Content-Type: application/json"
];

// **發送請求**
$data = json_encode([
    "model" => "llama3-8b-8192",
    "messages" => $messages, // 🔥 確保 messages 內有正確內容
    "temperature" => 0.7,
    "max_tokens" => 200
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// **檢查 API 是否回應錯誤**
if (!$response) {
    echo json_encode(["error" => "API 請求失敗"]);
    exit;
}

$decoded_response = json_decode($response, true);

// **檢查 API 是否有錯誤回應**
if ($http_status !== 200) {
    echo json_encode(["error" => "API 錯誤（狀態碼 $http_status）", "response" => $response]);
    exit;
}

// **取得 AI 回應**
$ai_response = $decoded_response["choices"][0]["message"]["content"] ?? "無法取得 AI 回應";
$messages[] = ["role" => "assistant", "content" => $ai_response]; // 🔥 加入 AI 回應

// **回傳 JSON 給前端**
echo json_encode(["chat_history" => $messages]);
?>
