<?php

require_once "../../config/bootstrap.php";
require_once "../../helpers/upload.php";

requireAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$title = trim($_POST["title"] ?? "");

if (!$id || $id <= 0) {
    sendResponse(false, "A valid image ID is required.", null, 400);
}

if ($title === "") {
    sendResponse(false, "Title is required.", null, 400);
}

try {

    $database = new Database();
    $conn = $database->connect();

    // Find existing gallery item
    $query = "SELECT id, title, image_path
              FROM gallery
              WHERE id = :id
              LIMIT 1";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":id" => $id
    ]);

    $existingImage = $stmt->fetch();

    if (!$existingImage) {
        sendResponse(false, "Gallery image not found.", null, 404);
    }

    $newImagePath = null;

    // Check whether a new image was uploaded
    if (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {
        $newImagePath = uploadGalleryImage($_FILES["image"]);
    }

    if ($newImagePath !== null) {

        $query = "UPDATE gallery
                  SET title = :title,
                      image_path = :image_path
                  WHERE id = :id";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            ":title" => $title,
            ":image_path" => $newImagePath,
            ":id" => $id
        ]);

        // Delete old physical image
        $oldImagePath = __DIR__ . "/../../" . $existingImage["image_path"];

        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }

    } else {

        $query = "UPDATE gallery
                  SET title = :title
                  WHERE id = :id";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            ":title" => $title,
            ":id" => $id
        ]);
    }

    sendResponse(
        true,
        "Gallery image updated successfully.",
        null,
        200
    );

} catch (Exception $e) {

    sendResponse(
        false,
        "Failed to update gallery image.",
        null,
        500
    );
}