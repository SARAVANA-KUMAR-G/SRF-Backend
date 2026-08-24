<?php
// setup_database.php

require_once __DIR__ . "/config/database.php";

$host = $_ENV["DB_HOST"] ?? "127.0.0.1";
$port = $_ENV["DB_PORT"] ?? "3306";
$user = $_ENV["DB_USERNAME"] ?? "root";
$pass = $_ENV["DB_PASSWORD"] ?? "";
$dbname = $_ENV["DB_DATABASE"] ?? "rehab_db";

try {
    echo "Connecting to MySQL server at {$host}:{$port}...\n";
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database `{$dbname}` if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbname}`;");

    echo "Creating tables (`admins`, `gallery`, `professionals`)...\n";
    $sql = file_get_contents(__DIR__ . "/database.sql");
    $pdo->exec($sql);

    // Check if admin user exists, if not create default admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `admins` WHERE `username` = :u");
    $stmt->execute([":u" => "admin"]);
    if ($stmt->fetchColumn() == 0) {
        $hashed = password_hash("admin123", PASSWORD_BCRYPT);
        $insertStmt = $pdo->prepare("INSERT INTO `admins` (`username`, `password`, `role`) VALUES (:u, :p, 'admin')");
        $insertStmt->execute([":u" => "admin", ":p" => $hashed]);
        echo "Default admin created: username = 'admin', password = 'admin123'\n";
    } else {
        echo "Admin user 'admin' already exists.\n";
    }

    echo "\n SUCCESS! Database `{$dbname}` and all tables created successfully!\n";
} catch (Exception $e) {
    echo "\n ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
