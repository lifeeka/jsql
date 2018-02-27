<?php

function sd()
{
    $Args = func_get_args();
    $console = new \PhpConsoleColor\Console();

    foreach ($Args as $str) {
        $str = print_r($str, true);
        $console->writeLn("<green>$str</green>");
    }
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $console->writeLn("<blue>".$caller['file'].":".$caller['line']."<blue>");

    die();
}


function s()
{
    $Args = func_get_args();
    $console = new \PhpConsoleColor\Console();

    foreach ($Args as $str) {
        $str = print_r($str, true);
        $console->writeLn("<green>$str</green>");
    }
    $bt = debug_backtrace();
    $caller = array_shift($bt);
    $console->writeLn("<blue>".$caller['file'].":".$caller['line']."<blue>");
}
