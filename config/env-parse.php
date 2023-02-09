<?php

/*
|--------------------------------------------------------------------------
| Register The Environmental Variables
|--------------------------------------------------------------------------
|
| Load the variables from the .env file into memory.
|
*/

$filename = APP_ROOT . ".env";
$handle = fopen($filename, "r");

if (!$handle) {
    throw new FileOpenError();
}

$contents = fread($handle, filesize($filename));
fclose($handle);

$arr = explode("\n", $contents);

foreach ($arr as $item) {
    $data = explode("=", $item);
    if ($data[0]) {
        $_ENV[$data[0]] = $data[1];
    }
}
