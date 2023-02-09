<?php

/*
|--------------------------------------------------------------------------
| Duplicate Item Error
|--------------------------------------------------------------------------
|
| This error is thrown when a user tries to insert a duplicate item
|
*/

class DuplicateItemError extends CustomError {
    public function __construct(string $message = "Не е позволено да съхранявате дублиращи се данни.") {
        parent::__construct($message);
    }
}
