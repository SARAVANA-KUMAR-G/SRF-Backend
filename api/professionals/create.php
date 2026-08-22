<?php

require_once "../../config/bootstrap.php";
require_once "../../helpers/cloudinary.php";

requireAuth();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed.", null, 405);
}

$name = trim($_POST["name"] ?? "");
$specialty = trim($_POST["specialty"] ?? "");

if ($name === "") {
    sendResponse(false, "Name is required.", null, 400);
}

if ($specialty === "") {
    sendResponse(false, "Specialty / Designation is required.", null, 400);
}

if (!isset($_FILES["image"])) {
    sendResponse(false, "Profile picture / portrait is required.", null, 400);
}

try {
    $database = new Database();
    $conn = $database->connect();

    // Upload to Cloudinary under 'rehab/professionals'
    $uploadResult = uploadToCloudinary($_FILES["image"], "rehab/professionals");

    $query = "INSERT INTO professionals
              (name, specialty, image_url, cloudinary_public_id)
              VALUES
              (:name, :specialty, :image_url, :cloudinary_public_id)";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ":name" => $name,
        ":specialty" => $specialty,
        ":image_url" => $uploadResult["url"],
        ":cloudinary_public_id" => $uploadResult["public_id"]
    ]);

    $id = $conn->lastInsertId();

    sendResponse(
        true,
        "Professional created successfully.",
        [
            "id" => (int) $id,
            "name" => $name,
            "specialty" => $specialty,
            "imageUrl" => $uploadResult["url"]
        ],
        201
    );

} catch (Exception $e) {
    sendResponse(
        false,
        $e->getMessage() ?: "Failed to create professional.",
        null,
        500
    );
}
