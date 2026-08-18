<?php

require_once "../../config/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    sendResponse(false, "A valid image ID is required.", null, 400);
}

try {

    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT id, title, image_url
              FROM gallery
              WHERE id = :id
              LIMIT 1";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":id" => $id
    ]);

    $image = $stmt->fetch();

    if (!$image) {
        sendResponse(
            false,
            "Gallery image not found.",
            null,
            404
        );
    }

    $data = [
        "id" => (int) $image["id"],
        "title" => $image["title"],
        "imageUrl" => $image["image_url"]
    ];

    sendResponse(
        true,
        "Gallery image retrieved successfully.",
        $data,
        200
    );

} catch (Exception $e) {

    sendResponse(
        false,
        "Failed to retrieve gallery image.",
        null,
        500
    );
}