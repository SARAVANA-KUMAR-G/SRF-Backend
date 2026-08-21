<?php

require_once __DIR__ . "/config/cors.php";

header("Content-Type: application/json");

echo json_encode([
    "status" => "online",
    "service" => "Sivam Research Foundation (SRF) API",
    "timestamp" => date("c")
]);
