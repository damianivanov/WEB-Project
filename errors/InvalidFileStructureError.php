<?php

/*
|--------------------------------------------------------------------------
| Invalid File Structure Error
|--------------------------------------------------------------------------
|
| This error is thrown when the user attempts to upload a disallowed
| file structure.
|
*/

class InvalidFileStructureError extends CustomError {
    public function __construct(int $lineNumber, string $row, string $reason) {
        parent::__construct($reason . " на ред " . $lineNumber . ": <br/>\"" . $row . "\" ");
    }
}
