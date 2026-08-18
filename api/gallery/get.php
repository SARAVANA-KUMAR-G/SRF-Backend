<?php

require_once "../../config/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(false, "Method not allowed.", null, 405);
}

try {

    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT id, title, image_path
              FROM gallery
              ORDER BY created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $images = [];

    while ($row = $stmt->fetch()) {

        $row["imageUrl"] = BASE_URL . $row["image_path"];

        unset($row["image_path"]);

        $images[] = $row;
    }

    sendResponse(
            true,
            "Gallery retrieved successfully.",
            $images,
            200
        );
} catch (Exception $e) {

    sendResponse(
            false,
            "Failed to retrieve gallery.",
            null,
            500
        );
}