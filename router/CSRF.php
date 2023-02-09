<?php

/*
|--------------------------------------------------------------------------
| CSRF Prevention
|--------------------------------------------------------------------------
|
| Create a unique token that prevents the CSRF attack.
|
*/

class CSRF {
    public function __construct() {
        session_start();
        // Add the CSRF token as a session variable.
        if ($_SERVER['REQUEST_METHOD'] != "POST") {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function validate() {
        if ($_SERVER['REQUEST_METHOD'] === "POST") {
            if ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
                header("Location: /csrf");
            }
        }
    }
}
