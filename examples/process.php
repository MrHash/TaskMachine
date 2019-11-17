<?php

require 'vendor/autoload.php';
require 'TextProcessor.php';

use Auryn\Injector;
use TaskMachine\Builder\TaskFactory;
use TaskMachine\Builder\TaskMachineBuilder;
use Workflux\Param\InputInterface;

$injector = new Injector;
$injector->define(
    TextProcessor::class,
    // this setting determines the processor function
    [':settings' => ['process' => 'reverse' /* 'upper' or 'lower' */]]
);

$tmb = new TaskMachineBuilder(new TaskFactory($injector));

$tmb->task('process', TextProcessor::class);

$tmb->task('finish', function (InputInterface $input) {
    // return input as output to passthru
    return $input;
});

$tm = $tmb
    ->machine('process')
    ->process(['initial' => true, 'transition' => 'finish'])
    ->finish(['final' => true])
    ->build();

// run machine with input data
$output = $tm->run('process', ['text' => 'Process This']);
echo $output->get('text').PHP_EOL;
