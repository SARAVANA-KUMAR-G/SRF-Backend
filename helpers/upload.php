<?php

function uploadGalleryImage($file)
{
    $uploadDir = __DIR__ . "/../uploads/gallery/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Please select an image.");
    }

    $allowedExtensions = ["jpg", "jpeg", "png", "webp"];
    $allowedMimeTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception("Invalid image format.");
    }

    $mimeType = mime_content_type($file["tmp_name"]);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        throw new Exception("Invalid image type.");
    }

    if ($file["size"] > (5 * 1024 * 1024)) {
        throw new Exception("Image must be smaller than 5 MB.");
    }

    $fileName = uniqid("gallery_", true) . "." . $extension;

    $destination = $uploadDir . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        throw new Exception("Failed to upload image.");
    }

    return "uploads/gallery/" . $fileName;
}