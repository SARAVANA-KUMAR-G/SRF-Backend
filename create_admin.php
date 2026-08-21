<?php

/**
 * Admin User Creation / Password Reset Utility
 *
 * Usage via CLI:
 *   php create_admin.php <username> <password> [role]
 *
 * Example:
 *   php create_admin.php admin admin123 admin
 */

require_once __DIR__ . "/config/database.php";

$isCli = php_sapi_name() === "cli";

if ($isCli) {
    global $argv;
    $username = $argv[1] ?? null;
    $password = $argv[2] ?? null;
    $role = $argv[3] ?? "admin";
} else {
    $username = $_GET["username"] ?? null;
    $password = $_GET["password"] ?? null;
    $role = $_GET["role"] ?? "admin";
}

if (empty($username) || empty($password)) {
    $msg = "Please provide username and password.\nUsage (CLI): php create_admin.php <username> <password> [role]\nUsage (Browser): create_admin.php?username=admin&password=password123\n";
    if ($isCli) {
        echo $msg;
    } else {
        echo nl2br(htmlspecialchars($msg));
    }
    exit(1);
}

try {
    $database = new Database();
    $conn = $database->connect();

    // Check if user already exists
    $checkStmt = $conn->prepare("SELECT id FROM admins WHERE username = :username LIMIT 1");
    $checkStmt->execute([":username" => $username]);
    $existing = $checkStmt->fetch();

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    if ($existing) {
        // Update password
        $updateStmt = $conn->prepare("UPDATE admins SET password = :password, role = :role WHERE id = :id");
        $updateStmt->execute([
            ":password" => $hashedPassword,
            ":role" => $role,
            ":id" => $existing["id"]
        ]);
        $out = "Successfully updated admin user '{$username}' with new password.\n";
    } else {
        // Insert new admin
        $insertStmt = $conn->prepare("INSERT INTO admins (username, password, role) VALUES (:username, :password, :role)");
        $insertStmt->execute([
            ":username" => $username,
            ":password" => $hashedPassword,
            ":role" => $role
        ]);
        $out = "Successfully created new admin user '{$username}' with role '{$role}'.\n";
    }

    if ($isCli) {
        echo $out;
    } else {
        header("Content-Type: application/json");
        echo json_encode(["success" => true, "message" => trim($out)]);
    }

} catch (Exception $e) {
    $err = "Error: " . $e->getMessage() . "\n";
    if ($isCli) {
        echo $err;
    } else {
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode(["success" => false, "message" => $err]);
    }
}
