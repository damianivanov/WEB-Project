<section class="container data-section">
    <div id="courses">
        <h1>
            Курсове
            <a class="add-button button icon-button" href="/add-course"><i class="fa-solid fa-plus"></i></a>
        </h1>
        <?php
        $teacher_id = $_SESSION['id'];
        $courses = Course::getAll($teacher_id);

        if (count($courses) == 0) {
            ?>
            <p class="text-center">Няма създадени курсове, използвайте бутона „+“, за да добавите първия.</p>
            <?php
        }
        ?>
        <ul class="list">
            <?php
            foreach ($courses as $course) {
                echo "<li><a class=\"is-link\" href=\"course/" . $course['id'] . "\">" . $course["name"] . ", " . $course["year"] . "</a></li>";
            }
            ?>
        </ul>
    </div>
</section>



