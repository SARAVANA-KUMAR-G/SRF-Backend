<?php

require_once "../../config/bootstrap.php";
require_once "../../helpers/upload.php";

requireAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed.");
}

try {

    $title = trim($_POST["title"] ?? "");

    if ($title === "") {
        sendResponse(false, "Title is required.", null, 400);
    }

    $title = trim($_POST["title"]);

    $imagePath = uploadGalleryImage($_FILES["image"]);

    $database = new Database();
    $conn = $database->connect();

    $query = "INSERT INTO gallery (title, image_path)
              VALUES (:title, :image_path)";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":title" => $title,
        ":image_path" => $imagePath
    ]);

    sendResponse(true, "Image uploaded successfully.");

} catch (Exception $e) {

    sendResponse(false, $e->getMessage());

}