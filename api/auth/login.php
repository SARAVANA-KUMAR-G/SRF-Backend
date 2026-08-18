<?php

require_once "../../config/cors.php";
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/response.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed");
}

$input = json_decode(file_get_contents("php://input"), true);

if (
    empty($input["username"]) ||
    empty($input["password"])
) {
    sendResponse(false, "Username and password are required.");
}

$username = trim($input["username"]);
$password = trim($input["password"]);

$database = new Database();
$conn = $database->connect();

$query = "SELECT * FROM admins WHERE username = :username LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bindParam(":username", $username);
$stmt->execute();

$user = $stmt->fetch();

if (!$user) {
    sendResponse(false, "Invalid username or password.");
}

if (!password_verify($password, $user["password"])) {
    sendResponse(false, "Invalid username or password.");
}

$_SESSION["user"] = [
    "id" => $user["id"],
    "username" => $user["username"],
    "role" => $user["role"]
];

sendResponse(true, "Login successful.", [
    "id" => $user["id"],
    "username" => $user["username"],
    "role" => $user["role"]
]);