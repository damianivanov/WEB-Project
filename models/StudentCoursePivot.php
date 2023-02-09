<?php
class StudentCoursePivot {
	private $studentID, $courseID;

	public static function storeList(array $students, int $courseID) {
		$sql = DB::prepareMultipleInsertSQL("students_courses_pivot", "student_id, course_id", count($students));

        $values = [];
		foreach($students as $student) {
			$values[] = $student['id'];
			$values[] = $courseID;
		}

        $db = (new DB());
        $db->execute($sql, $values);
        // return (new DB())->execute($sql, $values);
        return $db->getLastId();
	}

    public static function getIDs(array $students, int $courseID) {
        $values = [$courseID];
        foreach ($students as $student) {
            $values[] = $student['id'];
        }

        $sql = "SELECT id FROM students_courses_pivot WHERE course_id = (?) AND student_id IN " . DB::getQuestionLine(count($values) - 1);
//        var_dump($sql);
//        var_dump($values);
        return (new DB())->execute($sql, $values);
    }
}
