<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Simple endpoint test
if (isset($_GET['test'])) {
    die(json_encode([
        'status' => 'success',
        'message' => 'API endpoint is working',
        'timestamp' => time()
    ]));
}

// Main API functionality
if (isset($_GET['role']) && isset($_GET['prompt'])) {
    $role = trim($_GET['role']);
    $prompt = trim($_GET['prompt']);
    
    if (empty($role) || empty($prompt)) {
        http_response_code(400);
        die(json_encode(['error' => 'Role and prompt parameters are required']));
    }

    $api_url = "https://api.groq.com/openai/v1/chat/completions";
    $api_key = "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"; // REPLACE WITH YOUR ACTUAL API KEY

    $data = [
        "model" => "llama3-70b-8192",
        "messages" => [
            [
                "role" => "system",
                "content" => $role
            ],
            [
                "role" => "user",
                "content" => $prompt
            ]
        ],
        "temperature" => 0.7,
        "max_tokens" => 1000
    ];

    $ch = curl_init($api_url);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: Bearer " . $api_key
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        http_response_code(500);
        die(json_encode([
            'error' => 'API request failed',
            'curl_error' => curl_error($ch)
        ]));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode !== 200) {
        http_response_code($httpCode);
        die(json_encode([
            'error' => 'API returned error',
            'http_code' => $httpCode,
            'response' => $response
        ]));
    }

    curl_close($ch);
    
    $json_response = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(500);
        die(json_encode([
            'error' => 'Invalid JSON response',
            'raw_response' => $response
        ]));
    }

    echo json_encode($json_response);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
}
?>