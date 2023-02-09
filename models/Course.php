<?php

class Course
{
    private $name, $year, $teacher_id;

    public function __construct($name, $year, $teacher_id)
    {
        $this->name = $name;
        $this->year = $year;
        $this->teacher_id = $teacher_id;
    }

    public function store()
    {
        $sql = "INSERT INTO courses (name, year, teacher_id) VALUES (?, ?, ?)";
        $values = array($this->name, $this->year, $this->teacher_id);

        (new DB())->execute($sql, $values);
    }

    public static function delete($id)
    {
        $sql = "DELETE FROM courses WHERE id = ?";
        $values = array($id);

        (new DB())->execute($sql, $values);
    }

    public static function getAll($teacher_id): array
    {
        $sql = "SELECT * FROM courses WHERE teacher_id = ?";
        $values = array($teacher_id);

        return (new DB())->execute($sql, $values);
    }

    public static function getById($id): array
    {
        $sql = "SELECT * FROM courses WHERE id = ?";
        $values = array($id);

        return (new DB())->execute($sql, $values)[0];
    }

    public function hasDuplicate(): bool
    {
        return DB::hasDuplicate("SELECT * FROM courses WHERE name = (?) AND year = (?)", [$this->name, $this->year]);
    }

    public static function doesCourseBelongToUser(string $courseID): bool
    {
        $teacherID = $_SESSION['id'];
        $sql = "SELECT * FROM courses WHERE id = (?) AND teacher_id = (?)";

        $result = (new DB())->execute($sql, [$courseID, $teacherID]);

        return count($result) == 1;
    }

    public static function getTeachersInfoForCourse($teacher_id): array
    {
        $sql = "SELECT * FROM teachers WHERE id = (?)";
        $teacher_id = array($teacher_id);
        return (new DB())->execute($sql, $teacher_id);
    }

    public static function getAllInfo($courseID, $date): array
    {
        $sql = "select p.name as PaperName,
       s.name,
       s.faculty_number,
       DATE_FORMAT(t.from_time_planned ,'%H:%i')  as from_Planed,
       DATE_FORMAT(t.to_time_planned,'%H:%i')                                                     as to_Planed,
       DATE_FORMAT(t.from_time_real,'%H:%i')                                                      as from_Real,
       DATE_FORMAT(t.to_time_real,'%H:%i')                                                        as to_Real,
       TIMESTAMPDIFF(minute, cast(t.from_time_real as Time), cast(t.to_time_real as Time)) AS duration
from time_tables as t
         join papers p on t.paper_id = p.id
         join students_courses_pivot scp on p.student_course_pivot_id = scp.id
         join students s on scp.student_id = s.id
where scp.course_id = (?) and DATE(t.from_time_planned) = (?)";

        return (new DB())->execute($sql, [$courseID, $date]);

    }
}
