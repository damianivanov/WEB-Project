<?php
/*
 * CREATE TABLE time_tables
(
    id        INT       NOT NULL AUTO_INCREMENT,
    paper_id  INT       NOT NULL,
    is_real   BOOLEAN   NOT NULL,
    from_time TIMESTAMP NOT NULL,
    to_time   TIMESTAMP NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (paper_id) REFERENCES papers (id)
);
 */

class TimeTable
{
    private $id, $paper_id, $from_time_planned, $to_time_planned, $from_time_real, $to_time_real;

    public function __construct($paper_id, $from_time_planned, $to_time_planned, $from_time_real, $to_time_real)
    {
        $this->paper_id = $paper_id;
        $this->from_time_planned = $from_time_planned;
        $this->to_time_planned = $to_time_planned;
        $this->from_time_real = $from_time_real;
        $this->to_time_real = $to_time_real;
    }

    public function store()
    {
        $sql = "INSERT INTO time_tables (paper_id, from_time_planned, to_time_planned, from_time_real, to_time_real) VALUES (?, ?, ?, ?, ?)";
        $values = array($this->paper_id, $this->from_time_planned, $this->to_time_planned, $this->from_time_real, $this->to_time_real);

        (new DB())->execute($sql, $values);
    }

    public static function storeList(array $result, string $date, int $firstId)
    {
        $table = [];
        for ($i = 0; $i < count($result); ++$i) {
            $table[] = $firstId + $i;
            $table[] = $date . " " . $result[$i]['start'];
            $table[] = $date . " " . $result[$i]['end'];
        }

        $length = count($result);
        $sql = DB::prepareMultipleInsertSQL("time_tables", "paper_id, from_time_planned, to_time_planned", $length);

        (new DB())->execute($sql, $table);
    }

    public static function getAllByCourseId($id): array
    {
        $sql = <<<EOF
            SELECT S.name, S.id AS student_id, S.faculty_number, P.name AS topic, T.from_time_planned, T.to_time_planned, T.from_time_real, T.to_time_real
            FROM students_courses_pivot AS SCP
                JOIN students S on SCP.student_id = S.id
                JOIN papers AS P ON P.student_course_pivot_id = SCP.id
                JOIN time_tables AS T on P.id = T.paper_id
            WHERE course_id = (?)
        EOF;
        $values = array($id);

        return (new DB())->execute($sql, $values);
    }


    //
    public static function getPresenceToday($courseID, $date): array
    {
        $sql = "select student_id from students_courses_pivot as scp
                    join presences p on scp.id = p.student_course_pivot_id
                    where scp.course_id=(?) and DATE(p.presence_time)=(?);";

        $values = array($courseID, $date);
        $result=(new DB())->execute($sql, $values);
        $ids[]=[];
        foreach ($result as $student){
            $ids[]=$student['student_id'];
        }
        return $ids;
    }

    public static function getPresentersToday($courseID, $date): array
    {
        $sql = "select student_id from time_tables
join papers p on p.id = time_tables.paper_id
         join students_courses_pivot scp on scp.id = p.student_course_pivot_id
where course_id=(?) and DATE(from_time_planned)=(?)";

        $values = array($courseID, $date);
        $result=(new DB())->execute($sql, $values);
        $ids[]=[];
        foreach ($result as $student){
            $ids[]=$student['student_id'];
        }
        return $ids;
    }

    public static function getDates($courseID): array
    {
        $sql = <<<EOF
            SELECT CAST(from_time_planned AS DATE) AS date
            FROM time_tables
                JOIN papers P on time_tables.paper_id = P.id
                JOIN students_courses_pivot AS SCP ON SCP.id = P.student_course_pivot_id
            WHERE SCP.course_id = (?)
            GROUP BY CAST(from_time_planned AS DATE)
        EOF;

        return (new DB())->execute($sql, [$courseID]);
    }

    public static function timeToMinutes($time): int
    {
        $arr = explode(":", $time);
        return intval($arr[0]) * 60 + intval($arr[1]);
    }

    public static function hoursToMinutes($timeFirst, $timeSecond): int
    {
        $minutesFirst = TimeTable::timeToMinutes($timeFirst);
        $minutesSecond = TimeTable::timeToMinutes($timeSecond);

        return ($minutesSecond - $minutesFirst);
    }

    public static function addTime($start_time, $minutes)
    {
        $arrTime = explode(":", $start_time);
        $start_minutes = intval($arrTime[0]) * 60 + intval($arrTime[1]);
        $total_minutes = $start_minutes + $minutes;
        $res_hours = floor($total_minutes / 60);
        $res_minutes = ($total_minutes % 60);

        return sprintf('%02d:%02d', $res_hours, $res_minutes);
    }

    public static function mapHours($element): array
    {
        $result = [];
        $times = json_decode($element['times']);
        $students = json_decode($element['student_ids']);
        foreach ($times as $i => $time) {
            $time = substr($time, 0, 5);
            $result[$time][] = $students[$i];
        }
        return $result;
    }

//    public static function compareTimeForPresences($needle,$key): bool{
//        return $needle == substr($key,0,5);
//    }

//    public static function searchByKey($needle, $haystack): bool {
//        if (is_array($haystack) || is_object($haystack))
//        {
//            foreach ($haystack as $key => $value) {
//                if (gettype($key) == "string" && TimeTable::compareTimeForPresences($needle,$key)) {
//                    return true;
//                }
//                return false;
//            }
//        }
//
//        return false;
//    }

    public static function searchByKey($needle, $haystack): bool
    {
        foreach ($haystack as $key => $value) {
            if ($needle == $key) {
                return true;
            }
        }

        return false;
    }

    public static function searchByValue($needle, $haystack): bool
    {
        foreach ($haystack as $value) {
            if ($needle == $value) {
                return true;
            }
        }

        return false;
    }

    public static function timeAndDatesParser($student):array{

        $fromTimePlaned = $student['from_time_planned'];
        $toTimePlaned = $student['to_time_planned'];

        $fromPlanedDate = substr($fromTimePlaned, 0, 10);
        $fromTimePlannedHourMin = substr($fromTimePlaned, 11, 5);

        $toTimePlanedDate = substr($toTimePlaned, 0, 10);
        $toTimePlannedHourMin = substr($toTimePlaned, 11, 5);

        $fromRealDate = substr($student['from_time_real'] ?? "", 0, 10);
        $fromRealDateHourMin = substr($student['from_time_real']?? "", 11, 5);

        $toTimeRealHourMin = substr($student['to_time_real']?? "", 11, 5);

        return [$fromPlanedDate,$fromTimePlannedHourMin,$toTimePlanedDate,$toTimePlannedHourMin,$fromRealDate,$fromRealDateHourMin,$toTimeRealHourMin];
    }
    public static function determinePresence($currTime, $presences, $student_id): string
    {
        if (TimeTable::searchByKey($currTime, $presences)) {    // check if current time is in presences
            if (TimeTable::searchByValue($student_id, $presences[$currTime])) {  // student was present ?
                return 'g';
            } else {
                return 'r';
            }
        }
        return '';
    }

    public static function isLast($currTime, $endTime, $dateTime, $dateTimes): string
    {
        $endTimeHour = substr($endTime, 0, -3);
        $lastTimeOfLesson = TimeTable::addTime($currTime, 1);
        if ($lastTimeOfLesson == $endTimeHour && $dateTime != end($dateTimes)) {
            return 'e';
        }

        return '';
    }

    public static function isPlanned($currTime, $fromTimePlanned,$fromTimePlannedHourMin, $isFrom, $currDate): string
    {
//        $plannedDate = substr($fromTimePlanned, 0, 10);
        if ($fromTimePlanned != $currDate) {
            return "";
        }

//        $fromTimePlannedHourMin = substr($fromTimePlanned, 11, 5);

        if ($currTime == $fromTimePlannedHourMin) {
            return $isFrom ? "s" : "e";
        }

        return "";
    }

    public static function isMid($currTime, $fromTimePlanned, $fromTimePlannedHourMin,$toTimePlannedHourMin, $currDate, $class): string
    {
        if ($fromTimePlanned == null) return '';
        //$plannedDate = substr($fromTimePlanned, 0, 10);
        if ($fromTimePlanned != $currDate) {
            return '';
        }

       // $fromTimePlannedHourMin = substr($fromTimePlanned, 11, 5);
       // $toTimePlannedHourMin = substr($toTimePlanned, 11, 5);

        $fromTimePlannedToMinutes = TimeTable::timeToMinutes($fromTimePlannedHourMin);
        $toTimePlannedToMinutes = TimeTable::timeToMinutes($toTimePlannedHourMin);
        $currTimeToMinutes = TimeTable::timeToMinutes($currTime);


        if ($currTimeToMinutes >= $fromTimePlannedToMinutes && $currTimeToMinutes <= $toTimePlannedToMinutes) {
            return $class;
        }

        return '';
    }

    public static function validateDate($date, $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public static function getPlannedTimesByCourseID($courseID): array
    {
        $sql = <<<EOF
            SELECT CAST(T.from_time_planned AS DATE) AS date,
                   MIN(CAST(T.from_time_planned AS TIME)) AS start_time,
                   MAX(CAST(T.to_time_planned AS TIME)) AS end_time
            FROM students_courses_pivot AS SCP
                JOIN papers P on SCP.id = P.student_course_pivot_id
                JOIN time_tables T on P.id = T.paper_id
            WHERE course_id = (?)
            GROUP BY CAST(T.from_time_planned AS DATE);
        EOF;
        return (new DB())->execute($sql, [$courseID]);
    }
}
