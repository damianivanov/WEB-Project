<?php
$courseID = Router::$ROUTE['URL_PARAMS']['id'];
$list = Presence::getPresenceList($courseID);
$presencesRequired = 2;
if (isset($_POST['filterButton'])) {
    $presencesRequired = $_POST['presences'];
    $students = array_filter($list, function ($student) use ($presencesRequired) {
        return $student['TimesPresent'] >= $presencesRequired;
    });
    $list = $students;
}
//if(isset($_POST['removeFilter'])){
//    $presencesRequired = 0;
//}
if (isset($_POST['export'])) {
    $delimiter = ";";
    $minPresenceRequired = $_POST['presencesRequired'];
    $filename = 'presenceList.csv';
    $header_args = array("Име", "Фак.Номер", "Брой присъствие");
    header("Content-type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=$filename");
    $fp = fopen("php://output", 'w');
    ob_end_clean();
    fputcsv($fp, $header_args);
    foreach ($list as $fields) {
        if ($fields['TimesPresent'] >= $minPresenceRequired) {
            fputcsv($fp, $fields);
        }
    }
    exit;
}
?>
<section class="mini-container">

    <h1>
        <a class="icon-back is-link" href="<?= '/course/' . $courseID ?>">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        Присъствен списък
    </h1>
    <form class="form-inline" action="" method="post" enctype="multipart/form-data">
        <input class="input is-link" id="presences" type="number" name="presences" min=0
               placeholder="Минимален брой присъствия" value="<?= $_POST['presences'] ?? 2 ?>">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"/>
        <input class="button is-link" type="submit" value="Филтриране" name="filterButton"/>
        <input class="button red is-link" type="submit" value="Изчистване на Филтър" name="removeFilter"/>
        <input type="hidden" name="presencesRequired" value="<?= $presencesRequired ?>"/>
        <input class="button is-link green" type="submit" name="export" value="Експорт"/>
    </form>

</section>

<div class="presence-list">
    <table>
        <tbody>
        <tr>
            <th>Име</th>
            <th>Факултетен Номер</th>
            <th>Брой присъствия</th>
        </tr>
        <?php
        foreach ($list as $student) {
//            if ($filtered && $student['TimesPresent'] < $minPresences) {
//                $filtered = false;
//                break;
//            }
            ?>
            <tr class="<?= $student['TimesPresent'] >= $presencesRequired ? "greenRow" : "redRow" ?>">
                <td><?= $student['name'] ?></td>
                <td><?= $student['faculty_number'] ?></td>
                <td><?= $student['TimesPresent'] ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
