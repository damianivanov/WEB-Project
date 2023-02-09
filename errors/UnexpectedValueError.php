<?php

/*
|--------------------------------------------------------------------------
| Unexpected Value Error
|--------------------------------------------------------------------------
|
| This error is thrown when a function argument does not have an allowed value.
|
*/

class UnexpectedValueError extends CustomError {
    public function __construct() {
        parent::__construct("Подава се неочаквана стойност.");
    }
}
