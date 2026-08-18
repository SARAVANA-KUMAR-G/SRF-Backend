<?php

function sendResponse(
    bool $success,
    string $message,
    $data = null,
    int $statusCode = 200
) {
    http_response_code($statusCode);

    header("Content-Type: application/json");

    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);

    exit();
}