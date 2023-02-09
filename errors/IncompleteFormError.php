<?php

/*
|--------------------------------------------------------------------------
| BigBlueButtonParser Save Error
|--------------------------------------------------------------------------
|
| This error is thrown when a file cannot be written on the disk.
|
*/

class IncompleteFormError extends CustomError {
    public function __construct() {
        parent::__construct("Моля, попълнете всички полета във формуляра.");
    }
}
