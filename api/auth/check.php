<?php

require_once "../../config/cors.php";
require_once "../../config/session.php";
require_once "../../config/response.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    sendResponse(false, "Method not allowed");
}

if (!isset($_SESSION["user"])) {

    sendResponse(true, "No active session", [
        "loggedIn" => false
    ]);

}

sendResponse(true, "Session active", [

    "loggedIn" => true,

    "user" => $_SESSION["user"]

]);