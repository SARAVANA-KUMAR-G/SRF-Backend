<?php

require_once __DIR__ . "/response.php";
require_once __DIR__ . "/session.php";

function requireAuth()
{
    if (!isset($_SESSION["user"])) {
        sendResponse(false, "Unauthorized");
    }
}