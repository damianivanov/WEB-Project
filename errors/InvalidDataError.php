<?php

/*
|--------------------------------------------------------------------------
| Invalid BigBlueButtonParser Format Error
|--------------------------------------------------------------------------
|
| This error is thrown when the user attempts to upload a disallowed
| file format.
|
*/

class InvalidDataError extends CustomError {
    public function __construct($item) {
        parent::__construct("Избраната стойност за " . $item . " не е валидена.");
    }
}
