<?php

class Database
{
    private $host = "localhost";
    private $db_name = "rehab_db";
    private $username = "root";
    private $password = "SKpubg123";
    private $conn;

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