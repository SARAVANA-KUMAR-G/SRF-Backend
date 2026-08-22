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

$name = trim($_POST["name"] ?? ($rawInput["name"] ?? ""));
$specialty = trim($_POST["specialty"] ?? ($rawInput["specialty"] ?? ""));

if (!$id || $id <= 0) {
    sendResponse(false, "A valid professional ID is required.", null, 400);
}

if ($name === "") {
    sendResponse(false, "Name is required.", null, 400);
}

if ($specialty === "") {
    sendResponse(false, "Specialty is required.", null, 400);
}

try {
    $database = new Database();
    $conn = $database->connect();

    // Find existing professional
    $query = "SELECT id, name, specialty, image_url, cloudinary_public_id
              FROM professionals
              WHERE id = :id
              LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->execute([":id" => $id]);

    $existing = $stmt->fetch();

    if (!$existing) {
        sendResponse(false, "Professional not found.", null, 404);
    }

    $hasNewImage = (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    );

    if ($hasNewImage) {
        // Upload new image
        $uploadResult = uploadToCloudinary($_FILES["image"], "rehab/professionals");
        $newImageUrl = $uploadResult["url"];
        $newPublicId = $uploadResult["public_id"];

        // Update record
        $query = "UPDATE professionals
                  SET name = :name,
                      specialty = :specialty,
                      image_url = :image_url,
                      cloudinary_public_id = :cloudinary_public_id
                  WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ":name" => $name,
            ":specialty" => $specialty,
            ":image_url" => $newImageUrl,
            ":cloudinary_public_id" => $newPublicId,
            ":id" => $id
        ]);

        // Delete old Cloudinary image
        if (!empty($existing["cloudinary_public_id"])) {
            deleteFromCloudinary($existing["cloudinary_public_id"]);
        }

    } else {
        // Update name and specialty only
        $query = "UPDATE professionals
                  SET name = :name,
                      specialty = :specialty
                  WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->execute([
            ":name" => $name,
            ":specialty" => $specialty,
            ":id" => $id
        ]);
    }

    sendResponse(
        true,
        "Professional updated successfully.",
        null,
        200
    );

} catch (Exception $e) {
    sendResponse(
        false,
        $e->getMessage() ?: "Failed to update professional.",
        null,
        500
    );
}
