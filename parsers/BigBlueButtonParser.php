<?php
//TRICKY REGEX--Fixed
class BigBlueButtonParser {
    private const REGEX = [
        "time-stamp" => "/([1-9]|1[0-2])\/([1-9]|1[0-9]|2[0-9]|3[0|1])\/(\d{4}):(0[0-9]|1[0-2]):([0-5]?[0-9]):([0-5]?[0-9]) (AM|PM)/",
        "student-list" => "/Sorted by first name:\r\n(([^\r]|\r)*)\n  \r\n\r\nSorted by last name:/",
    ];

    public static function fileValidation(array $file): string {
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);

        if ($ext != "txt") {
            throw new InvalidFileFormatError();
        }

        if ($file["size"] > 500000) {
            throw new FileTooLargeError();
        }

        return file_get_contents($file["tmp_name"]);
    }

    public static function getTimestamp(string $fileContent): string {
        $matches = BigBlueButtonParser::find($fileContent, BigBlueButtonParser::REGEX['time-stamp']);
        $month = $matches[1][0];
        $day = $matches[2][0];
        $year = $matches[3][0];
        $hour = $matches[4][0];
        $minute = $matches[5][0];
        $second = $matches[6][0];

        $string = $year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second;
        //throw "Error";
        return $string;
    }

    private static function find(string $fileContent, string $pattern) {
        $matches = [];
        preg_match_all($pattern, $fileContent, $matches);

        if (count($matches) == 0) {
          throw new InvalidFileFormatError();
        }

        return $matches;
    }

    public static function getStudentList(string $fileContent): array {
        $match = BigBlueButtonParser::find($fileContent, BigBlueButtonParser::REGEX['student-list']);

        return explode("\r\n", $match[1][0]);
    }
}
