<?php

require_once __DIR__ . "/../vendor/autoload.php";

use Dotenv\Dotenv;
use Cloudinary\Cloudinary;

$dotenv = Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->load();

if (empty($_ENV["CLOUDINARY_URL"])) {
    throw new Exception("Cloudinary configuration is missing.");
}

$GLOBALS["cloudinary"] = new Cloudinary($_ENV["CLOUDINARY_URL"]);