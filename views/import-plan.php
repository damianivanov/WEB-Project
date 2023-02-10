<?php
$initialRealData = "0	09:00	9999	0	Милен Петров	0	Откриване на презентациите
2	09:20	82057	3	Дамян Иванов	10	Работа със сесии и cookies (от страна насървъра и клиента).";
$initialConfiguration='{"field_delimiter":"\t", "line_delimiter":"\n", "skip_header_rows":"0", "validate":"true"}';
?>
<section class="mini-container  data-section">
    <h1>
        <a class="icon-back is-link" href="<?= '/course/' . Router::$ROUTE['URL_PARAMS']['id'] ?>"><i
                class="fa-solid fa-chevron-left"></i></a>
        Импортиране на предварителен план
    </h1>
</section>

<form action="import-plan" method="post" enctype="multipart/form-data">
    <label>
        Дата на представяне
        <input class="input is-link" type="date" name="date" required value="<?= $_POST['date'] ?? "" ?>">
    </label>
    <label>
        Предварителен план
        <textarea class="textarea is-link large" name="plan" required><?= $_POST['plan'] ?? $initialRealData ?></textarea>
    </label>
    <label>
        Конфигурационни данни
        <textarea class="textarea is-link small" name="configuration"><?= $_POST['configuration'] ?? $initialConfiguration ?></textarea>
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
    $parser->processPlan($plan, $date);

    header("Location: /course/" . Router::$ROUTE['URL_PARAMS']['id']);
}
?>
