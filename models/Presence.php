<?php


class Presence
{
    private $id, $presence_time, $student_course_pivot;

    public function __construct(string $stamp, $student_course_pivot)
    {
        $this->presence_time = $stamp;
        $this->student_course_pivot = $student_course_pivot;
    }

    public function getId()
    {
        return $this->id;
    }

    public function store()
    {
        $sql = "INSERT INTO presences (presence_time, student_course_pivot) VALUES (?, ?)";
        $values = array($this->presence_time, $this->student_course_pivot);

        $db = new DB();
        $db->execute($sql, $values);
        $this->id = $db->getLastId();
    }

    public static function getByTimestamp(string $timestamp, string $courseID): array
    {
        $sql = <<<EOF
            SELECT *
            FROM presences AS P
                JOIN students_courses_pivot AS SCP ON P.student_course_pivot_id = SCP.id
            WHERE presence_time = (?) AND course_id = (?)
        EOF;

        $values = array($timestamp);
        $values[] = $courseID;

        return (new DB())->execute($sql, $values);
    }

    public static function getPresenceList($courseID): array
    {
        $sql = "SELECT s.name,s.faculty_number,Count(p.student_course_pivot_id) AS TimesPresent FROM students AS s
JOIN students_courses_pivot scp ON s.id = scp.student_id
LEFT JOIN presences p ON p.student_course_pivot_id = scp.id
JOIN courses c ON c.id = scp.course_id
WHERE c.id = (?)
GROUP BY s.name,s.faculty_number,p.student_course_pivot_id
ORDER BY TimesPresent DESC ;";


        $values[] = $courseID;
        return (new DB())->execute($sql, $values);
    }

    public static function storeList(string $timestamp, array $student_course_pivot_ids)
    {
        $result = [];
        foreach ($student_course_pivot_ids as $id) {
            $result[] = $timestamp;
            $result[] = $id['id'];
        }
        $sql = DB::prepareMultipleInsertSQL("presences", "presence_time, student_course_pivot_id", count($student_course_pivot_ids));

        $db = new DB();
        $db->execute($sql, $result);

        return $db->getLastId();
    }

    public static function getPresencesByCourseID($courseID): array
    {
        $sql = <<<EOF
                SELECT CAST(P.presence_time AS DATE) AS date,
                       JSON_ARRAYAGG(CAST(P.presence_time AS TIME)) AS times,
                       JSON_ARRAYAGG(SCP.student_id) AS student_ids
                FROM presences AS P
                    JOIN (
                            SELECT id, student_id
                            FROM students_courses_pivot
                            WHERE course_id = (?)
                        ) AS SCP on SCP.id = P.student_course_pivot_id
                GROUP BY CAST(P.presence_time AS DATE);
        EOF;

        return (new DB())->execute($sql, [$courseID]);
    }

//    public static function mapPresences($result):array{
//        $res = [];
////        $times = json_decode($result['times']);
////        $students = json_decode($result['student_ids']);
//        foreach ($result as $i => $time) {
//            $t = substr($time['times'], 0, 5);
//        }
//        return $res;
//    }
//    public static function getPresencesForCourse($courseID): array
//    {
//        $sql = "select DATE(presence_time) as date,cast(presence_time as time) as times,scp.student_id from courses as c
//join students_courses_pivot scp on c.id = scp.course_id
//join presences p on scp.id = p.student_course_pivot_id
//where c.id = (?)
//group by DATE(presence_time),scp.student_id";
//
//        $result = (new DB())->execute($sql, [$courseID]);
//        return Presence::mapPresences($result);
//    }

}
