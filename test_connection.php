<?php

require_once "config/database.php";

$database = new Database();
$conn = $database->connect();

if ($conn) {
    echo "✅ Database Connected Successfully!";
}