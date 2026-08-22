<?php

require_once "../../config/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    sendResponse(false, "A valid professional ID is required.", null, 400);
}

try {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT id, name, specialty, image_url, created_at
              FROM professionals
              WHERE id = :id
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->execute([":id" => $id]);

    $item = $stmt->fetch();

    if (!$item) {
        sendResponse(false, "Professional not found.", null, 404);
    }

    $data = [
        "id" => (int) $item["id"],
        "name" => $item["name"],
        "specialty" => $item["specialty"],
        "imageUrl" => $item["image_url"],
        "createdAt" => $item["created_at"]
    ];

    sendResponse(true, "Professional retrieved successfully.", $data, 200);

} catch (Exception $e) {
    sendResponse(false, "Failed to retrieve professional.", null, 500);
}
