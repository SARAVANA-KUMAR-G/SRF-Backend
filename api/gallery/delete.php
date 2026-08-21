<?php

require_once "../../config/bootstrap.php";
require_once "../../helpers/cloudinary.php";

requireAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$rawInput = json_decode(file_get_contents("php://input"), true);
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT) 
    ?: (isset($rawInput["id"]) ? filter_var($rawInput["id"], FILTER_VALIDATE_INT) : null)
    ?: filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    sendResponse(false, "A valid image ID is required.", null, 400);
}

try {

    $database = new Database();
    $conn = $database->connect();

    // Find the gallery item
    $query = "SELECT id, cloudinary_public_id
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

    // Delete image from Cloudinary
    if (!empty($image["cloudinary_public_id"])) {
        deleteFromCloudinary(
            $image["cloudinary_public_id"]
        );
    }

    // Delete database record
    $query = "DELETE FROM gallery
              WHERE id = :id";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":id" => $id
    ]);

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