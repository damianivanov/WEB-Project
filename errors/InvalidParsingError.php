<?php

class InvalidParsingError extends CustomError {
    public function __construct() {
        parent::__construct("Грешка при обработката на листа със студенти.");
    }
}
