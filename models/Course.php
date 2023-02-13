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

    public  static function deleteCourseById($courseID){
        $sqlStudentCoursePivot="select * from students_courses_pivot where course_id=(?)";
        $studentCoursePivotIds= (new DB())->execute($sqlStudentCoursePivot, [$courseID]);
        $SCPids=[];
        $StudentsIds=[];
        foreach ($studentCoursePivotIds as $item){
            $SCPids[]=$item['id'];
            $StudentsIds[]=$item['student_id'];
        }
        if(count($SCPids)!=0) {
            $sqlDeletePresences = "delete from presences where student_course_pivot_id in " . DB::getQuestionLine(count($SCPids));
            (new DB())->execute($sqlDeletePresences, $SCPids);

            $sqlPaperIds = "select id from papers where student_course_pivot_id in " . DB::getQuestionLine(count($SCPids));
            $result = (new DB())->execute($sqlPaperIds, $SCPids);

            $paper_ids = [];
            foreach ($result as $paper_id) {
                $paper_ids[] = $paper_id['id'];
            }

            if(count($paper_ids) != 0) {
                $sqlDeleteTimeTable = "delete from time_tables where paper_id in" . DB::getQuestionLine(count($paper_ids));
                (new DB())->execute($sqlDeleteTimeTable, $paper_ids);


                $sqlDeletePapers = "delete from papers where student_course_pivot_id in " . DB::getQuestionLine(count($paper_ids));
                (new DB())->execute($sqlDeletePapers, $paper_ids);
            }

            $sqlDeleteStudentCoursePivot = "delete from students_courses_pivot where course_id=(?)";
            (new DB())->execute($sqlDeleteStudentCoursePivot, [$courseID]);

            $sqlDeleteStudentsInCourse = "delete from students where id in" . DB::getQuestionLine(count($StudentsIds));
            (new DB())->execute($sqlDeleteStudentsInCourse, $StudentsIds);
        }

        $sqlDeleteCourse = "delete from courses where id = (?)";
        (new DB())->execute($sqlDeleteCourse, [$courseID]);

    }
}
