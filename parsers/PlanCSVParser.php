<?php

class PlanCSVParser {
    private string $fieldDelimiter;
    private string $lineDelimiter;
    private string $skipHeader;
    private string $validation;

    public function __construct(string $fieldDelimiter, string $lineDelimiter, string $skipHeader, bool $validation) {
        $this->fieldDelimiter = $fieldDelimiter;
        $this->lineDelimiter = $lineDelimiter;
        $this->skipHeader = $skipHeader;
        $this->validation = $validation;
    }

    public  function fileValidation(array $file): string {
        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);

        if ($ext != "csv") {
            throw new InvalidFileFormatError();
        }

        if ($file["size"] > 500000) {
            throw new FileTooLargeError();
        }

        return file_get_contents($file["tmp_name"]);
    }

    public function inputValidation($fields, $row, $rowNumber, $fieldCount){

        if (count($fields) != $fieldCount) {
            throw new InvalidFileStructureError($rowNumber, $row, "Неправилен брой колони");
        }

        foreach ($fields as $col) {
            if ($col == "") {
                throw new InvalidFileStructureError($rowNumber, $row, "Съдържат се празни колони");
            }
        }
    }

   // public function getParser()

    private  function getData(string $file_data): Array {
        $rows = explode($this->lineDelimiter, $file_data);

        $result = [];
        for ($i = $this->skipHeader; $i < count($rows); ++$i) {
            if (!empty($rows[$i])) {
                if (str_contains($rows[$i], "Почивка")) {
                    continue;
                }

                $temp_row = explode($this->fieldDelimiter, $rows[$i]);

                if($this->validation) {
                    $this->inputValidation($temp_row, $rows[$i], $i + 1, 7);
                }

                $result[] =  [
                    "faculty_number" => $temp_row[2],
                    "name" => $temp_row[4],
                    "topic" => $temp_row[6],
                    "start" => $temp_row[1],
                    "end" => TimeTable::addTime($temp_row[1], 5)
                ];
            }
        }
        return $result;
    }

    private  function getRealData(string $file_data) : Array {
        $rows = explode($this->lineDelimiter, $file_data);
        $result = [];
        for ($i = $this->skipHeader; $i < count($rows); ++$i) {
            if (!empty($rows[$i])) {
                if (str_contains($rows[$i], "Почивка")) {
                    continue;
                }

                $temp_row = explode($this->fieldDelimiter, $rows[$i]);

                if($this->validation) {
                    $this->inputValidation($temp_row, $rows[$i], $i + 1, 8);
                }

                $result[] =  [
                    "faculty_number" => $temp_row[3],
                    "name" => $temp_row[5],
                    "topic" => rtrim($temp_row[7],$this->lineDelimiter),
                    "start" => $temp_row[1],
                    "end" => $temp_row[2],
                ];
            }
        }

        return $result;

    }

    public  function processPlan(string $plan, string $date) {
        $courseID = Router::$ROUTE['URL_PARAMS']['id'];
        $result = PlanCSVParser::getData($plan);


        $sql =<<<EOF
            SELECT faculty_number
            FROM students AS S
                JOIN students_courses_pivot scp on S.id = scp.student_id
            WHERE course_id = (?)
        EOF;

        $registeredFNs = (new DB())->execute($sql, [$courseID]);
        $fns = [];

        foreach ($registeredFNs as $registeredFN) {
            $fns[] = $registeredFN['faculty_number'];
        }

        foreach ($result as $student) {
            if (TimeTable::searchByValue($student['faculty_number'], $fns)) {
                throw new DuplicateStudentError($student['faculty_number']);
            }
        }

        $firstId = Student::StoreList($result);

        for ($i = 0; $i < count($result); ++$i) {
            $result[$i]['id'] = $firstId + $i;
        }

        $courseID = Router::$ROUTE['URL_PARAMS']['id'];
        $firstIdSCP = StudentCoursePivot::storeList($result, $courseID);

        for($i = 0; $i < count($result); ++$i) {
            $result[$i]['id'] = $firstIdSCP + $i;
        }

        $firstIdPaper = Paper::StoreList($result);
        TimeTable::storeList($result, $date, $firstIdPaper);

    }

    public function processReal(string $real, string $date) {
        $result = PlanCSVParser::getRealData($real);

        $studentNames = [];
        $facultyNumbers = [];

        foreach ($result as $student) {
            $studentNames[] = $student['name'];
            $facultyNumbers[] = $student['faculty_number'];
        }

        $sql ='
            SELECT P.name AS topic, DATA.name, DATA.faculty_number, P.id AS paper_id
            FROM papers AS P
                JOIN (
                    SELECT SCP.id, S.name, S.faculty_number
                    FROM students_courses_pivot AS SCP
                        JOIN (
                            SELECT id, name, faculty_number
                            FROM students
                            WHERE name IN ' . DB::getQuestionLine(count($studentNames)) . '
                              AND faculty_number IN ' . DB::getQuestionLine(count($facultyNumbers)) . '
                        ) AS S ON SCP.student_id = S.id
                    WHERE SCP.course_id = (?)
            ) AS DATA ON P.student_course_pivot_id = DATA.id
        ';

        $values =array_merge($studentNames,$facultyNumbers);

//        foreach ($result as $student) {
//            $values[] = $student['name'];
//        }
//        foreach ($result as $student) {
//            $values[] = $student['faculty_number'];
//        }

        $values[] = Router::$ROUTE['URL_PARAMS']['id'];
        $data = (new DB())->execute($sql, $values);

        $sql =<<<EOF
            UPDATE time_tables
            SET from_time_real = (?), to_time_real = (?)
            WHERE paper_id = (?)
        EOF;

        $values = [];

        function getPaperID($student, $data): string {
            foreach ($data as $datum) {
                if ($datum['name']==$student['name'] && $datum['faculty_number']==$student['faculty_number'] && $datum['topic']==$student['topic']) {
                    return  $datum['paper_id'];
                }
            }

            return "";
        }

        foreach ($result as $student) {
            $paper_id = getPaperID($student, $data);

            $values[] = [
                $date . " " . $student['start'],
                $date . " " . $student['end'],
                $paper_id
            ];
        }

        (new DB())->multipleExecute($sql, $values);
    }

    public static function loadSocials():array{
        $filename='../parsers/socials.txt';
        $fp = @fopen($filename, 'r');
        if ($fp) {
            $result=[];
            $array = explode("\n", fread($fp, filesize($filename)));
            $i=0;
            foreach ($array as $line) {
                $tokens = explode(" = ",$line);
                if($tokens[0]=="") continue;
                $result[$i] = [$tokens[0],$tokens[1]];
                $i++;
            }
            return  array_filter($result);
        }
        return [];
    }
}
