<?php

/*
|--------------------------------------------------------------------------
| Database Query Error
|--------------------------------------------------------------------------
|
| This error is thrown when the database cannot execute an SQL request.
|
*/

class DatabaseQueryError extends CustomError {
    public function __construct() {
        parent::__construct("Възникна техническа грешка и Вашата заявка не беше изпълена.");
    }
}
