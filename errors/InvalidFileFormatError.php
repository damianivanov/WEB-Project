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

class InvalidFileFormatError extends CustomError {
    public function __construct() {
        parent::__construct("Форматът на файла е невалиден.");
    }
}
