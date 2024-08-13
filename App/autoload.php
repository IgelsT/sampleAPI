<?php

require_once __DIR__ . '/app.php';

function regClasses($classname)
{
    $classname = str_replace("\\", "/", $classname);
    //echo $classname;
    require_once __DIR__ . '/../' . $classname . '.php';
}

spl_autoload_register('regClasses');
