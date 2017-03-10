<?php

require 'vendor/autoload.php';

use TaskFlux\TaskFlux;
use Auryn\Injector;
use TaskFlux\Environment;
use TaskFlux\CustomHandler;
use TaskFlux\Task;
use Workflux\Param\InputInterface;

// Bootstrap
$injector = new Injector;
$environment = new Environment(['process' => 'process', 'cleanup' => 'cleanup', 'custom' => 'custom']);
$injector->share($environment);

// Define tasks
$tf = new TaskFlux($injector);

$tf->task('start', function() {
    echo 'start'.PHP_EOL;
    return ['incoming' => 'outgoing'];
});

$tf->task('process', function(InputInterface $input, Environment $env) {
    echo $input->get('incoming').PHP_EOL;
    echo $env->get('process').PHP_EOL;
});

$tf->task('cleanup', function(Environment $env) {
    echo $env->get('cleanup').PHP_EOL;
});

$tf->task('finish', CustomHandler::class);

// Define pipeline
$tf->pipeline(
    'transcoder',
    [
        'start' => [ 'initial' => true, 'transitions' => [ 'process' => null ] ],
        'process' => [ 'input' => [ 'incoming' => [ 'type' => 'string' ] ], 'transitions' => [ 'cleanup' => null ] ],
        'cleanup' => [ 'transitions' => [ 'finish' => null ] ],
        'finish' => [ 'final' => true ]
    ]
);

// Execute pipeline
$output = $tf->run('transcoder');
print_r($output->toArray(), true);
