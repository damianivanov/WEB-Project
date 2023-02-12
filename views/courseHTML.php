<script>
    document.querySelector(".nav").remove();
</script>
<style>
    <?php include '../public/assets/css/styles.css'; ?>
    <?php include '../public/assets/css/nav.css'; ?>
</style
<?php


$courseID = Router::$ROUTE['URL_PARAMS']['id'];
$filteredDate = Router::$ROUTE['URL_PARAMS']['date'] ?? "";
$data = Course::getById($courseID);
$teacher_id = $data['teacher_id'];
$timeTableData = TimeTable::getAllByCourseId($courseID);
$data1 = Presence::getPresencesByCourseID($courseID);

$result = [];
foreach ($data1 as $element) {
    $result[$element['date']] = TimeTable::mapHours($element);
}

$date_times_copy = TimeTable::getPlannedTimesByCourseID($courseID);
$date_times = $date_times_copy;

$presence_today = [];
$presenters_today = [];
$filtered = false;
if ($filteredDate != "" || isset($_POST['filter'])) {
    $filterDate = $_POST['date'] ?? $filteredDate;
    foreach ($date_times_copy as $date) {
        if ($date['date'] == $filterDate) {
            $date_times = [];
            $date_times = array($date);
            //filter only presentations for today
            $presenters_today = TimeTable::getPresentersToday($courseID, $filterDate);
            //filter presence list for today
            $presence_today = TimeTable::getPresenceToday($courseID, $filterDate);
        }
    }

    $filtered = true;
}?>
<div class="container">
        <div class="grid">
            <?php
if (count($timeTableData) != 0) {
    $start = hrtime(true); ?>
    <div class="table">
        <table>
            <thead>
            <tr>
                <td class="header" rowspan="2">Име</td>
                <td class="header1" rowspan="2">ФН</td>
                <td class="header2" rowspan="2">Тема</td>
                <?php foreach ($date_times as $i => $date_time) {
                    $start_time = $date_time['start_time'];
                    $end_time = $date_time['end_time'];
                    $cellCount = TimeTable::hoursToMinutes($start_time, $end_time);
                    ?>
                    <td class="time <?php if ($date_time != end($date_times)) {echo 'e';}?>"
                        colspan="<?= $cellCount ?>"> <?= $date_time['date'] ?></td>
                <?php }
                ?>
            </tr>
            <tr>
                <?php
                foreach ($date_times as $i => $date_time) {
                    $start_time = $date_time['start_time'];
                    $end_time = $date_time['end_time'];
                    $cellCount = TimeTable::hoursToMinutes($start_time, $end_time);

                    for ($i = 0; $i < $cellCount; $i += 15) {
                        ?>
                        <td class="time <?php if ($i + $cellCount % 15 == $cellCount) {echo 'e';} ?>"
                            colspan="<?= $i + $cellCount % 15 == $cellCount ? $cellCount % 15 : 15 ?>">
                            <?= TimeTable::addTime($start_time, $i) . ' - ' . (($i + $cellCount % 15 == $cellCount) ?
                                substr($end_time, 0, -3) :
                                TimeTable::addTime($start_time, $i + 15)) ?>
                        </td>
                        <?php
                    }
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($timeTableData as $student) {
                if ((!$filtered) || (in_array($student['student_id'], $presence_today) || in_array($student['student_id'], $presenters_today))) {
                    ?>
                    <tr>
                        <td class="header" title="<?= $student['name'] ?>">
                            <div >
                                <span><?= $student['name'];?>
                                <span class="links"><?php if(!empty($socials)){
                                foreach ($socials as $social){
                            ?><a href="<?=$social[1].''.$student['faculty_number']?>" target="_blank" class="small-button"><?=strtoupper($social[0][0])?></i></a>
                                    <?php
                                }
                            }?>
                        </span>
                                </span>
                            </div>
                        </td>
                        <td class="header1"><?= $student['faculty_number'] ?></td>
                        <td class="header2"
                            title="<?= (!$filtered) || (in_array($student['student_id'], $presenters_today)) ? $student['topic'] : "" ?>">
                            <div class="hide-long-text">
                                <span><?= (!$filtered) || in_array($student['student_id'], $presenters_today) ? $student['topic'] : "" ?></span>
                            </div>
                        </td>
                        <?php
                        foreach ($date_times as $i => $date_time) {
                            $start_time = $date_time['start_time'];
                            $end_time = $date_time['end_time'];
                            $cellCount = TimeTable::hoursToMinutes($start_time, $end_time);
                            $presences = $result[$date_time['date']] ?? "";

                            //[$fromPlanedDate,$fromTimePlannedHourMin,$toTimePlanedDate,$toTimePlannedHourMin,$fromRealDate,$fromRealDateHourMin,$toTimeRealHourMin];
                            $parsedTimes = TimeTable::timeAndDatesParser($student);

                            for ($j = 0; $j < $cellCount; ++$j) {
                                $currTime = TimeTable::addTime($start_time, $j);
                                ?>
                                <td class="<?= TimeTable::isPlanned($currTime, $parsedTimes[0], $parsedTimes[1], true, $date_time['date']) ?> <?= TimeTable::isPlanned($currTime, $parsedTimes[2], $parsedTimes[3], false, $date_time['date']) ?> <?= TimeTable::isMid($currTime, $parsedTimes[0], $parsedTimes[1], $parsedTimes[3], $date_time['date'], 'm') ?> <?= TimeTable::isMid($currTime, $parsedTimes[4], $parsedTimes[5], $parsedTimes[6], $date_time['date'], 'g') ?> <?= TimeTable::determinePresence($currTime, $presences, $student['student_id']) ?> <?= TimeTable::isLast($currTime, $end_time, $date_time, $date_times) ?>" title="<?= $currTime ?>"><div class="p"></div></td>
                                <?php
                            }

                        }
                        ?>
                    </tr>
                    <?php
                }
            } ?>
            </tbody>
        </table>
    </div>
    <?php
//$end = hrtime(true);
//$s = ($end - $start) / 1000000000;
//print_r($s);
} else { ?>

    <section class="mini-container">
        <p class="text-center">Все още няма информация за студентите в курса. Моля, първо качете предварителния график
            за представянето.</p>
    </section>
    </div>
    </div>
<?php }
?>



