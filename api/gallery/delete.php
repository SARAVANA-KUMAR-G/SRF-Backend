<?php

require_once "../../config/bootstrap.php";

requireAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    sendResponse(false, "A valid image ID is required.", null, 400);
}

try {

    $database = new Database();
    $conn = $database->connect();

    // Find the image first
    $query = "SELECT id, image_path
              FROM gallery
              WHERE id = :id
              LIMIT 1";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":id" => $id
    ]);

    $image = $stmt->fetch();

    if (!$image) {
        sendResponse(false, "Gallery image not found.", null, 404);
    }

    // Delete database record
    $query = "DELETE FROM gallery
              WHERE id = :id";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":id" => $id
    ]);

    // Delete physical image
    $filePath = __DIR__ . "/../../" . $image["image_path"];

    if (file_exists($filePath)) {
        unlink($filePath);
    }

    sendResponse(
        true,
        "Gallery image deleted successfully.",
        null,
        200
    );

} catch (Exception $e) {

    sendResponse(
        false,
        "Failed to delete gallery image.",
        null,
        500
    );
}