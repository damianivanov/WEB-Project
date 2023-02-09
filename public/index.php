<?php

/*
|--------------------------------------------------------------------------
| Application starting point
|--------------------------------------------------------------------------
|
| All requests are redirected to the index file and the router loads
| any page based on the URL.
|
*/

// Define application root path
define("APP_ROOT", realpath($_SERVER["DOCUMENT_ROOT"]) . "/../");

require_once APP_ROOT . "config/auto-load.php";
require_once APP_ROOT . "config/env-parse.php";
require_once APP_ROOT . "config/error-handler.php";

// Import database connection file
require_once APP_ROOT . "database/DB.php";

// Import CSRF protection
require_once APP_ROOT . "router/CSRF.php";
(new CSRF())->validate();

// Import router
require_once APP_ROOT . "router/Router.php";
(new Router())->locate();
