<?php

require __DIR__ . '/vendor/autoload.php';

use Akash\Mylaravelpackage\Test;

$test = new Test();
echo "Test1: " . $test->testing() . PHP_EOL;
echo "Test2: " . $test->testing1() . PHP_EOL;
echo "Test3: " . $test->testing2() . PHP_EOL;
