<?php

require_once "../../config/bootstrap.php";
require_once "../../helpers/cloudinary.php";

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
    $query = "SELECT id, title, image_url, cloudinary_public_id
              FROM gallery
              WHERE id = :id
              LIMIT 1";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":id" => $id
    ]);

    $existingImage = $stmt->fetch();

    if (!$existingImage) {
        sendResponse(
            false,
            "Gallery image not found.",
            null,
            404
        );
    }

    /*
     * Check whether a new image was uploaded.
     */
    $hasNewImage = (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    );

    if ($hasNewImage) {

        // Upload new image first
        $uploadResult = uploadToCloudinary($_FILES["image"]);

        $newImageUrl = $uploadResult["url"];
        $newPublicId = $uploadResult["public_id"];

        // Update database
        $query = "UPDATE gallery
                  SET title = :title,
                      image_url = :image_url,
                      cloudinary_public_id = :cloudinary_public_id
                  WHERE id = :id";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            ":title" => $title,
            ":image_url" => $newImageUrl,
            ":cloudinary_public_id" => $newPublicId,
            ":id" => $id
        ]);

        // Delete old Cloudinary image
        if (!empty($existingImage["cloudinary_public_id"])) {
            deleteFromCloudinary(
                $existingImage["cloudinary_public_id"]
            );
        }

    } else {

        // Update title only
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