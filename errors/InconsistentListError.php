<?php

/*
|--------------------------------------------------------------------------
| BigBlueButtonParser Open Error
|--------------------------------------------------------------------------
|
| This error is thrown when a file cannot be opened from the disk.
|
*/

class InconsistentListError extends CustomError {
    public function __construct() {
        parent::__construct("В присъствения списък има студенти, които не са добавени в предварителния график.");
    }
}
