<?php

require_once __DIR__ . "/../config/cloudinary.php";

function uploadToCloudinary($file, $folder = "rehab/gallery")
{
    if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Please select an image.");
    }

    $allowedExtensions = ["jpg", "jpeg", "png", "webp"];

    $allowedMimeTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];

    $extension = strtolower(
        pathinfo($file["name"], PATHINFO_EXTENSION)
    );

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

    $result = $GLOBALS["cloudinary"]
        ->uploadApi()
        ->upload(
            $file["tmp_name"],
            [
                "folder" => $folder
            ]
        );

    return [
        "url" => $result["secure_url"],
        "public_id" => $result["public_id"]
    ];
}

function deleteFromCloudinary($publicId)
{
    if (empty($publicId)) {
        return;
    }

    $GLOBALS["cloudinary"]
        ->uploadApi()
        ->destroy(
            $publicId,
            [
                "resource_type" => "image",
                "type" => "upload"
            ]
        );
}