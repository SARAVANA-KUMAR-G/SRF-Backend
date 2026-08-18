<?php

require_once "../../config/cors.php";
require_once "../../config/auth.php";

requireAuth();

sendResponse(true, "You are authenticated.");