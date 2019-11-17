<?php

require 'vendor/autoload.php';

use TaskMachine\Builder\TaskMachineBuilder;

$tmb = new TaskMachineBuilder;

$tmb->task('process', function () {
    // This outputs a random true or false result
    $result = (bool)random_int(0,1);
    return ['success' => $result];
});

$tmb->task('finish', function () {
    echo 'finish state'.PHP_EOL;
});

$tmb->task('fail', function () {
    echo 'fail state'.PHP_EOL;
});

$tm = $tmb
    ->machine('random')
    ->process([
        'initial' => true,
        'transition' => [
            'output.success' => 'finish',
            '!output.success' => 'fail'
        ]
    ])
    ->finish(['final' => true])
    ->fail(['final' => true])
    ->build();

$tm->run('random');
