<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 確保請求方式為 POST，避免 405 錯誤
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); // 設置 HTTP 405 錯誤碼
    echo "請使用 POST 方式傳送資料";
    exit;
}

// 取得使用者輸入
$user_input = $_POST["user_input"] ?? "";

// 檢查是否有輸入
if (empty($user_input)) {
    echo "請輸入內容";
    exit;
}

// Groq API 設置
$api_url = "https://api.groq.com/openai/v1/chat/completions";
$api_token = "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"; // ←★ 請填入你的 Groq API Key

$headers = [
    "Authorization: Bearer $api_token",
    "Content-Type: application/json"
];

// 設置請求資料
$data = json_encode([
    "model" => "llama-3.1-8b-instant", // 可選模型：llama3-70b-8192, mixtral-8x7b-32768
    "messages" => [
        ["role" => "system", "content" => "用繁體中文回答問題"],
        ["role" => "user", "content" => $user_input]
       
    ],
    "temperature" => 0.7,
    "max_tokens" => 500
]);

// 初始化 cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 確保 API 回應正常
if (!$response) {
    echo "API 請求失敗，請檢查設定";
    exit;
}

// 解析 JSON
$decoded_response = json_decode($response, true);

// 檢查 API 是否回應錯誤
if ($http_status !== 200) {
    echo "API 錯誤（狀態碼 $http_status）：" . htmlspecialchars($response);
    exit;
}

// 顯示 AI 回應
if (isset($decoded_response["choices"][0]["message"]["content"])) {
    echo nl2br(htmlspecialchars($decoded_response["choices"][0]["message"]["content"]));
} else {
    echo "無法取得 AI 回應，請稍後再試。";
}
?>
