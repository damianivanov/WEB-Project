<div class="container">
    <h2 class="text-center header-title">Gradeview</h2>
    <p class="text-center">Система за отбелязване на присъствията на студентите.</p>
    <section class="box-center">
        <?php
        if (Router::isLoggedIn()) {
            //TODO:landing page
            header("Location: /dashboard");
        } else {
            header("Location: /login");
        }
        die();
        ?>
        <img class="image" src="/assets/images/" alt="image with a pen"/>
    </section>
</div>

