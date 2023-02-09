<?php

/*
|--------------------------------------------------------------------------
| BigBlueButtonParser Open Error
|--------------------------------------------------------------------------
|
| This error is thrown when a file cannot be opened from the disk.
|
*/

class FileOpenError extends CustomError {
    public function __construct() {
        parent::__construct("Файлът не може да бъде отворен.");
    }
}
