<?php

/*
|--------------------------------------------------------------------------
| Duplicate Item Error
|--------------------------------------------------------------------------
|
| This error is thrown when a user tries to insert a duplicate item
|
*/

class DuplicateStudentError extends CustomError {
    public function __construct(string $data) {
        parent::__construct("Вече същестуват студенти със следните факултетни номера: " . $data);
    }
}
