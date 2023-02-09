<?php

/*
|--------------------------------------------------------------------------
| Hashing Password Error
|--------------------------------------------------------------------------
|
| This error is thrown when the hashing algorithm fails to confirm that the
| newly hashed password matches the original one.
|
*/

class HashingPasswordError extends CustomError {
    public function __construct() {
        parent::__construct("Хеширането се е провалило. Моля, опитайте отново по-късно.");
    }
}
