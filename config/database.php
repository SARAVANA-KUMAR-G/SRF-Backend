<?php

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    public function __construct()
    {
        if (file_exists(__DIR__ . "/../vendor/autoload.php")) {
            require_once __DIR__ . "/../vendor/autoload.php";
            if (class_exists('Dotenv\Dotenv')) {
                try {
                    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
                    $dotenv->safeLoad();
                } catch (Exception $e) {
                    // Ignore if already loaded or missing
                }
            }
        }

        $this->host = $_ENV["DB_HOST"] ?? "127.0.0.1";
        $this->db_name = $_ENV["DB_DATABASE"] ?? "rehab_db";
        $this->username = $_ENV["DB_USERNAME"] ?? "root";
        $this->password = $_ENV["DB_PASSWORD"] ?? "";
    }

    public function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            die(json_encode([
                "success" => false,
                "message" => "Database Connection Failed",
                "error" => $e->getMessage()
            ]));

        }

        return $this->conn;
    }
}