<?php

require 'vendor/autoload.php';

use TaskMachine\Builder\TaskMachineBuilder;

$tmb = new TaskMachineBuilder;

$tmb->task('hello', function () {
    echo 'hello'.PHP_EOL;
});

$tmb->task('bye', function () {
    echo 'bye'.PHP_EOL;
});

$tm = $tmb
    ->machine('simple')
    ->hello(['initial' => true, 'transition' => 'bye'])
    ->bye(['final' => true])
    ->build();

$tm->run('simple');
