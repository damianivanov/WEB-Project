<?php

session_start();
unset($_SESSION["id"]);
unset($_SESSION["name"]);
unset($_SESSION["email"]);
unset($_SESSION["expertise"]);
unset($_SESSION["login_time"]);
unset($_SESSION["csrf_token"]);
session_destroy();

header('Location: /login');
