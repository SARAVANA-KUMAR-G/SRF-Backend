<?php

require_once "../../config/cors.php";
require_once "../../config/session.php";
require_once "../../config/response.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Method not allowed");
}

session_unset();
session_destroy();

sendResponse(true, "Logged out successfully.");