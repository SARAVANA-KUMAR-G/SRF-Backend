<?php

require_once "../../config/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(false, "Method not allowed.", null, 405);
}

try {
    $database = new Database();
    $conn = $database->connect();

    $query = "SELECT id, name, specialty, image_url, created_at
              FROM professionals
              ORDER BY id ASC";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    $professionals = [];

    while ($row = $stmt->fetch()) {
        $professionals[] = [
            "id" => (int) $row["id"],
            "name" => $row["name"],
            "specialty" => $row["specialty"],
            "imageUrl" => $row["image_url"],
            "createdAt" => $row["created_at"]
        ];
    }

    sendResponse(
        true,
        "Professionals retrieved successfully.",
        $professionals,
        200
    );

} catch (Exception $e) {
    sendResponse(
        false,
        "Failed to retrieve professionals.",
        null,
        500
    );
}
