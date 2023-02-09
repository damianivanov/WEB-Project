<?php

/*
|--------------------------------------------------------------------------
| BigBlueButtonParser Save Error
|--------------------------------------------------------------------------
|
| This error is thrown when a file cannot be written on the disk.
|
*/

class FileSaveError extends CustomError {
    public function __construct() {
        parent::__construct("Възникнал е технически проблем и файлът не е могъл да бъде запазен.");
    }
}
