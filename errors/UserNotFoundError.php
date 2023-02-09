<?php

/*
|--------------------------------------------------------------------------
| User Not Found Error
|--------------------------------------------------------------------------
|
| This error is thrown when a user in the database cannot be found.
|
*/

class UserNotFoundError extends CustomError {
    public function __construct() {
        parent::__construct("Потребителят не е намерен.");
    }
}
