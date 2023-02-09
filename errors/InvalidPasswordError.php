<?php

/*
|--------------------------------------------------------------------------
| Invalid Password Error
|--------------------------------------------------------------------------
|
| This error is thrown when the user submits a password that does not
| follow the requirements.
|
*/

class InvalidPasswordError extends CustomError {
    public function __construct() {
        parent::__construct("Паролата трябва да съдържа поне 6 символа,малка буква, число.");
    }
}
