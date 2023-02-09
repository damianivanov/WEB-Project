<section class="container">
    <h2 class="FormTitle">Вход</h2>

    <form action="login" method="post">
<!--        <label for="email">Електронна поща</label>-->
        <input class="input is-link" id="email" type="email" name="email" placeholder="Електронна поща" value="<?= $_POST['email'] ?? null ?>"/>
<!--        <label for="pass">Парола</label>-->
        <input class="input is-link" id="pass" type="password" placeholder="Парола" name="password"/>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? null ?>"/>
        <input class="button is-link" type="submit" name="login" value="Вход"/>
        <a class="is-link" href="/register">Нямате акаунт?</a>

    </form>

</section>

<?php
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = User::verifyCredentials($email, $password);
    $_SESSION['login_time'] = time();
    $_SESSION['id'] = $user["id"];
    $_SESSION['name'] = $user["name"];
    $_SESSION['email'] = $user["email"];
    $_SESSION['expertise'] = $user['expertise'];

    header('Location: /dashboard');
}
?>
