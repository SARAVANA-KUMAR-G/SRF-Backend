<?php

require_once "../../config/bootstrap.php";
require_once "../../helpers/cloudinary.php";

requireAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$title = trim($_POST["title"] ?? "");

if ($title === "") {
    sendResponse(false, "Title is required.", null, 400);
}

if (!isset($_FILES["image"])) {
    sendResponse(false, "Image is required.", null, 400);
}

try {

    $database = new Database();
    $conn = $database->connect();

    $uploadResult = uploadToCloudinary($_FILES["image"]);

    $query = "INSERT INTO gallery
              (title, image_url, cloudinary_public_id)
              VALUES
              (:title, :image_url, :cloudinary_public_id)";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":title" => $title,
        ":image_url" => $uploadResult["url"],
        ":cloudinary_public_id" => $uploadResult["public_id"]
    ]);

    $id = $conn->lastInsertId();

    sendResponse(
        true,
        "Image uploaded successfully.",
        [
            "id" => (int) $id,
            "title" => $title,
            "imageUrl" => $uploadResult["url"]
        ],
        201
    );

} catch (Exception $e) {

    sendResponse(
        false,
        "Failed to upload image.",
        null,
        500
    );
}