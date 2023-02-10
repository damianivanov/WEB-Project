<?php
$initialRealData = "0	09:00	09:05	9999	0	Милен Петров	0	Откриване на презентациите
2	09:20	09:25	82057	3	Дамян Иванов	10	Работа със сесии и cookies (от страна насървъра и клиента).";
$initialConfiguration='{"field_delimiter":"\t", "line_delimiter":"\n", "skip_header_rows":"0", "validate":"true"}';
?>
<section class="mini-container data-section">
    <h1>
        <a class="icon-back is-link" href="<?= '/course/' . Router::$ROUTE['URL_PARAMS']['id'] ?>"><i
                class="fa-solid fa-chevron-left"></i></a>
        Импортиране на реален план
    </h1>
</section>
<form action="import-real" method="post" enctype="multipart/form-data">
    <label for="dates">Дата на представяне</label>
    <select class="select is-link" name="date" id="dates">
        <?php
        $dates = TimeTable::getDates(Router::$ROUTE['URL_PARAMS']['id']);
        foreach ($dates as $datum) {
            ?>
            <option value="<?= $datum['date'] ?>"><?= $datum['date'] ?></option>
            <?php
        }
        ?>
    </select>
    <label>
        Реален план (копиран от Google Spreadsheets)
        <textarea class="textarea large is-link" name="plan" required><?= $_POST['plan'] ?? $initialRealData ?></textarea>
    </label>
    <label>
        Конфигурационни данни
        <textarea class="textarea small is-link" name="configuration"><?= $_POST['configuration'] ?? $initialConfiguration ?></textarea>
    </label>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>"/>
    <input class="button is-link" type="submit" value="Импортиране" name="import"/>
</form>
<?php
if (isset($_POST["import"])) {
    if (empty($_POST['plan']) || empty($_POST['date'])) {
        throw new IncompleteFormError();
    }

    if (!TimeTable::validateDate($_POST['date'])) {
        throw new InvalidDataError("дата на представяне");
    }

    if (!empty($_POST['configuration'])) {
        $config = json_decode($_POST['configuration']);
        $parser = new PlanCSVParser($config->field_delimiter, $config->line_delimiter, $config->skip_header_rows, $config->validate);
    } else {
        $parser = new PlanCSVParser("\t", "\n", '0', 'true');
    }

    $plan = $_POST['plan'];
    $date = $_POST['date'];
    $parser->processReal($plan, $date);

    header("Location: /course/" . Router::$ROUTE['URL_PARAMS']['id']);
}

?>
