<?php
$courseID = Router::$ROUTE['URL_PARAMS']['id'];

if (isset($_POST["importDup"])) {
    foreach ($_POST as $key => $item) {
        if (!str_contains($key, 'student-') && empty($item)) {
            throw new IncompleteFormError();
        }
    }

//        var_dump(count($_POST));

    $sql = '
            SELECT SCP.id
            FROM students AS S
                JOIN students_courses_pivot SCP on S.id = SCP.student_id
            WHERE course_id = (?) AND S.id IN ' . DB::getQuestionLine(count($_POST) - 3) . '
        ';

    $values = [$courseID];
    foreach ($_POST as $key => $item) {
        if (str_contains($key, 'student-')) {
            $values[] = $item;
        }
    }
//        var_dump($sql, $values);
    $scpIdsOfDupStudents = (new DB())->execute($sql, $values);
    $presence = Presence::storeList($_POST['stamp'], $scpIdsOfDupStudents);

    header("Location: /course/" . $courseID);
}
if(isset($_POST['download'])){
    $file_url = '../test-data/bbb-sampleData.txt';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: utf-8");
    header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");
    ob_end_clean();
    exit(file_get_contents($file_url));
    readfile($file_url);
}
?>

    <section id="basic-form" class="mini-container data-section">
        <h1>
            <a class="icon-back is-link" href="<?= '/course/' . $courseID ?>">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            Импортиране на присъствен списък
        </h1>

        <form action="import-bbb" method="post" enctype="multipart/form-data">

            <div id="file-js-example" class="file has-name">
                <label class="file-label">
                    <input class="file-input" type="file" name="presence_list">
                    <span class="file-cta">
                <span class="file-icon">
                    <i class="fas fa-upload"></i>
                </span>
                <span class="file-label">
                    Изберете файл...
                </span>
            </span>
                    <span class="file-name">
                <p class="tiny"></p>
                Не е избран файл
            </span>
                </label>

            </div>

            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"/>
            <label>
                <input type="checkbox" name="confirm" value="true"/>
                <p>Ако списъкът вече е импортиран, искате ли да го качите отново?</p>
            </label>

            <input class="button is-link" type="submit" value="Импортиране" name="import"/>
            <form class="form-inline" action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"/>
                <input class="button is-link green-btn float-left" type="submit" value="Примерен Файл" name="download">
            </form>
        </form>

    </section>

    <script>
        const fileInput = document.querySelector('#file-js-example input[type=file]');
        fileInput.onchange = () => {
            if (fileInput.files.length > 0) {
                const fileName = document.querySelector('#file-js-example .file-name');
                fileName.innerHTML = '<p class="tiny"></p>' + fileInput.files[0].name;
            }
        }
    </script>

<?php
if (isset($_POST["import"])) {
    if (!file_exists($_FILES['presence_list']['tmp_name']) || !is_uploaded_file($_FILES['presence_list']['tmp_name'])) {
        throw new IncompleteFormError();
    }

    $fileContent = BigBlueButtonParser::fileValidation($_FILES['presence_list']);

    $stamp = BigBlueButtonParser::getTimestamp($fileContent);

    if ($_POST['confirm'] != "true" && count(Presence::getByTimestamp($stamp, $courseID)) != 0) {
        throw new DuplicateListError();
    }

    //bbb-list.txt BIG PROBLEMS
    $students = BigBlueButtonParser::getStudentList($fileContent);
    // -----------------------------------------------
    $studentNameCounter = [];
    foreach ($students as $student) {
        if (isset($studentNameCounter[$student])) {
            $studentNameCounter[$student]++;
        } else {
            $studentNameCounter[$student] = 1;
        }
    }
    // var_dump($studentNameCounter);
    // -----------------------------------------------

    $sameNameStudents = Student::getSameNameStudentsByCourse($students, $courseID);
    // var_dump($sameNameStudents);

    // -----------------------------------------------
    $unmatchingStudents = [];
    $unmatchingStudentNames = [];

//        var_dump($sameNameStudents);
    foreach ($sameNameStudents as $i => $item) {
        $count = count(json_decode($item['ids']));
//           var_dump($count);
        if ($count < $studentNameCounter[$item['name']]) {
            throw new InconsistentListError();
        } else if ($count > $studentNameCounter[$item['name']]) {
            $unmatchingStudents[] = [$i, $count - $studentNameCounter[$item['name']]];
            $unmatchingStudentNames[] = $item['name'];
        }
    }
//        var_dump($students);
//        var_dump($unmatchingStudentNames, $unmatchingStudents);
    $clearStudents = [];
    foreach ($students as $student) {
        if (!TimeTable::searchByValue($student, $unmatchingStudentNames)) {
            $clearStudents[] = $student;
        }
    }

    $students = Student::getByNames($clearStudents);
    $student_course_pivots_ids = StudentCoursePivot::getIDs($students, $courseID);
    $presence = Presence::storeList($stamp, $student_course_pivots_ids);

    if (count($unmatchingStudents) != 0) {
        ?>
        <script>
            document.getElementById('basic-form').remove();
        </script>
        <section class="container">
            <h1>
                <a class="icon-back is-link" href="<?= '/course/' . $courseID ?>">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                Импортиране на присъствен списък
            </h1>
            <form action="import-bbb" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"/>
                <input type="hidden" name="stamp" value="<?= $stamp ?>">
                <p>В присъствения списък има студент(и) със съвпадащи имена. Моля изберете кое име с кой факултетен
                    номер е свързано. </p>
                <?php foreach ($unmatchingStudents as $datum) { ?>
                    <?php for ($i = 0; $i < $datum[1]; ++$i) { ?>
                        <label>
                            <?= $sameNameStudents[$datum[0]]['name'] ?>
                        </label>
                        <select class="select is-link" name="student-<?= $sameNameStudents[$datum[0]]['name'] ?>"
                                required>
                            <?php
                            $fns = json_decode($sameNameStudents[$datum[0]]['faculty_numbers']);
                            $ids = json_decode($sameNameStudents[$datum[0]]['ids']);
                            for ($k = 0; $k < count($fns); $k++) {
                                ?>
                                <option value="<?= $ids[$k] ?>"><?= $fns[$k] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    <?php } ?>
                <?php } ?>
                <input class="button is-link" type="submit" value="Поднови" name="importDup"/>
            </form>
        </section>
        <?php
    } else {
        header("Location: /course/" . $courseID);
    }
}


