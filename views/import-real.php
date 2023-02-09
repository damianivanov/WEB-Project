<section class="mini-container data-section">
    <h1>
        <a class="icon-back is-link" href="<?= '/course/' . Router::$ROUTE['URL_PARAMS']['id'] ?>"><i class="fa-solid fa-chevron-left"></i></a>
        Импортиране на реален план
    </h1>
</section>
    <form action="import-real" method="post" enctype="multipart/form-data">
        <label for="dates">Дата на представяне</label>
        <select class="select is-link" name="date" id="dates">
            <?php
                $dates = TimeTable::getDates(Router::$ROUTE['URL_PARAMS']['id']);
                foreach($dates as $datum) {
                    ?>
                        <option value="<?= $datum['date'] ?>"><?= $datum['date'] ?></option>
                    <?php
                }
            ?>
        </select>
        <label>
            Реален план (копиран от Google Spreadsheets)
            <textarea class="textarea large is-link" name="plan" required><?= $_POST['plan'] ?? ""?></textarea>
        </label>
        <label>
            Конфигурационни данни
            <textarea class="textarea small is-link" name="configuration" placeholder="{&quot;field_delimiter&quot;:&quot;\t&quot;, &quot;line_delimiter&quot;:&quot;\n&quot;, &quot;skip_header-rows&quot;:&quot;3&quot;, &quot;validate&quot;:&quot;true&quot;}"><<?= $_POST['configuration'] ?? "" ?>/textarea>
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

    if(!empty($_POST['configuration'])) {
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
