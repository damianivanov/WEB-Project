<?php

/*
|--------------------------------------------------------------------------
| Duplicate Item Error
|--------------------------------------------------------------------------
|
| This error is thrown when a user tries to insert a duplicate item
|
*/

class DuplicateListError extends CustomError {
    public function __construct() {
        parent::__construct("Списъкът вече е качен. Моля потвърдете, че искате да се качи отново.");
    }
}
