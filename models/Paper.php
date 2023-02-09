<?php

class Paper
{
    private $id, $name, $student_course_pivot_id, $is_presented;

    public function __construct($id, $name, $student_course_pivot_id, $is_presented) {
        $this->id = $id;
        $this->name = $name;
        $this->student_course_pivot_id = $student_course_pivot_id;
        $this->is_presented = $is_presented;
    }

    public function store() {
        $sql = "INSERT INTO papers (name, course_id, student_course_pivot_id, is_presented) VALUES (?, ?, ?, ?)";
        $values = array($this->name, $this->student_course_pivot_id, $this->is_presented);

        (new DB())->execute($sql, $values);
    }

    public static function storeList(array $result) {
        $papers = [];

        for ($i = 0; $i < count($result); ++$i) {
            $papers[] = rtrim($result[$i]['topic'],"\r\n");
            $papers[] = $result[$i]['id'];
            $papers[] = 0;
        }

        $lenght = count($result);
        $sql = DB::prepareMultipleInsertSQL("papers", "name, student_course_pivot_id, is_presented", $lenght);

        $db = new DB();
        $db->execute($sql, $papers);
        return $db->getLastId();
    }

    public static function getById($id) {
        $sql = "SELECT FROM papers (name, student_course_pivot_id, is_presented) WHERE id = ?";
        $values = array($id);

        return (new DB())->select($sql, $values);
    }


}
