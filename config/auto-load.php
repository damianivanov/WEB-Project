<?php

spl_autoload_register(function ($className) {
    $paths = [
        APP_ROOT . "errors",
        APP_ROOT . "models",
        APP_ROOT . "parsers",
    ];

    foreach ($paths as $path) {
        $classPath = "$path/$className.php";
        if (file_exists($classPath)) {
            require_once $classPath;
        }
    }
});
