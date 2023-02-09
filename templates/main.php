<html lang="bg">
<head>
    <title>Gradeview
        <?php if (isset(Router::$ROUTE['title'])) {
            echo  "| " . Router::$ROUTE['title'];
        } ?>
    </title>
    <meta charset="UTF-8"/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" type="text/css" href="/assets/css/styles.css"/>
    <?php
    // TODO Fix this
        if(isset(Router::$ROUTE['css'])){
            foreach (Router::$ROUTE['css'] as $link) {
                ?>
                    <link rel="stylesheet" type="text/css" href="<?= $link ?>"/>
                <?php
            }
        }
    ?>
</head>
<body>
    <header>
        <nav class="nav">
            <div class="container">
                <div class="grid">
                    <div class="left">
                        <h1><a class="no-decoration title" href="/">Gradeview</a></h1>
                    </div>
                    <div class="right">
                        <?php
                        if (Router::isLoggedIn()) {
                            ?>
                            <a class="link" href="/dashboard">Панел</a>
                            <a class="link" href="/logout">Изход</a>
                            <?php
                        } else {
                            ?>
                            <a class="link" href="/login">Вход</a>
                            <a class="link" href="/register">Регистрация</a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <?php require_once Router::$ROUTE['view']; ?>

</body>
</html>
