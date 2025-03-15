<!-- 接收 POST 請求，取得使用者輸入
發送 API 請求 到 Hugging Face
解析 AI 回應，然後 echo 給 JavaScript -->
<!-- 前端送資料 → PHP 發送 API → 回應給前端 -->
<?php
error_reporting(E_ALL); // 顯示所有類型的錯誤
ini_set('display_errors', 1); // 確保錯誤資訊顯示在網頁上

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = $_POST["user_input"];  // 獲取使用者輸入
    $api_url = "https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.3";
    $api_token = "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";  // Hugging Face API Key

    // HTTP 標頭（Headers）是在 客戶端（Client）和伺服器（Server）之間傳輸的額外資訊
    $headers = array(
        "Authorization: Bearer $api_token", // 使用 API 金鑰來驗證請求 bearer 表示請求是「攜帶 Token」的授權請求
        "Content-Type: application/json" // 指定請求的內容類型為 JSON 傳送的資料格式
    );

    $data = json_encode([ // 將 PHP 陣列轉換成 JSON 字串
        "inputs" => $user_input,
        "parameters" => [
            "max_length" => 500 // 限制 AI 回應的最大長度為 500 個 token
        ]
    ]);

    // 是一個強大的擴展庫，允許你透過網路請求來與遠端伺服器進行資料傳輸
    //  cURL 擴展提供的內建常數
    // curl_setopt(資源變數, 設定選項, 設定值) : 設定函數
    $ch = curl_init(); // 初始化
    curl_setopt($ch, CURLOPT_URL, $api_url); // 設定 API URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 讓回應結果以字串回傳，而不是直接輸出
    curl_setopt($ch, CURLOPT_POST, 1); // 1（或 true）代表啟用 POST 模式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // 設定要發送的資料
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);// CURLOPT_HTTPHEADER 參數必須是 陣列格式

    $response = curl_exec($ch); // 存取回應
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE); // 代表 API 回應的 HTTP 狀態碼，可以用來判斷請求是否成功
    curl_close($ch);

    if (!$response) {
        die("cURL 執行失敗，請檢查 API 請求是否正確");
    }

    $decoded_response = json_decode($response, true); // 解析 JSON 回應，並轉換為關聯陣列

    if ($http_status !== 200) {
        echo "API 錯誤（狀態碼 $http_status）";
        exit;
    }
    // nl2br() 會把換行符 \n 轉換成 HTML <br>，確保換行格式正確顯示
    if (isset($decoded_response[0]['generated_text'])) {
        echo nl2br(htmlspecialchars($decoded_response[0]['generated_text']));
    } else {
        echo "API 無法解析回應，請稍後再試。";
    }
} else {
    echo "請使用 POST 方式傳送資料";
}
?>
