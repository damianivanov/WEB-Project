<?php

/*
|--------------------------------------------------------------------------
| Custom Error class
|--------------------------------------------------------------------------
|
| Custom Error class that inherits the PHP Exception class and handles the
| errors that may happen during the work of the application.
|
*/

class CustomError extends Exception {
    public function __construct(string $message) {
        parent::__construct($message);
    }
}
