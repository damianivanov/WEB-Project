<?php

/*
|--------------------------------------------------------------------------
| BigBlueButtonParser Too Large Error
|--------------------------------------------------------------------------
|
| This error is thrown when the user attempts to upload a file larger than 0.5MB.
|
*/

class FileTooLargeError extends CustomError {
    public function __construct() {
        parent::__construct("Файлът е твърде голям. Максималният размер е 0,5 MB.");
    }
}
