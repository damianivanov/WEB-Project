<div class="container">
    <h2 class="FormTitle">Регистрация</h2>

    <form action="register" method="post" autocomplete="off">
        <input class="input is-link" id="name" type="text" name="name" placeholder="Име" <?= $_POST['name'] ?? null ?>
        "/>
        <input class="input is-link" id="email" type="email" name="email" placeholder="Електронна поща"
               value="<?= $_POST['email'] ?? null ?>"/>
        <input class="input is-link" id="expertise" type="text" name="expertise" placeholder="Специалност"
               value="<?= $_POST['expertise'] ?? null ?>"/>
        <input class="input is-link" id="pass" type="password" placeholder="Парола" name="password"/>
        <input class="input is-link" id="conf-pass" type="password" placeholder="Потвърдете паролата"
               name="conf_password"/>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? null ?>"/>

        <input class="button is-link" type="submit" name="register" value="Регистрация"/>
        <a class="is-link" href="/login">Вече имате акаунт?</a>

    </form>

</div>
<?php
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $expertise = $_POST['expertise'];
    $password = $_POST['password'];
    $conf_password = $_POST['conf_password'];

    if (empty($name) ||
        empty($email) ||
        empty($expertise) ||
        empty($password) ||
        empty($conf_password)) {
        throw new IncompleteFormError();
    }

    $user = new User($name, $email, $expertise, $password, $conf_password);
    $user->store();
    header('Location: login');
}
?>
