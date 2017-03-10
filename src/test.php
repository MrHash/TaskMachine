<?php

require 'vendor/autoload.php';

use Auryn\Injector;
use TaskFlux\CustomHandler;
use TaskFlux\Environment;
use TaskFlux\TaskFlux;
use Workflux\Param\InputInterface;

/*
 * name ideas
 * TaskFlux
 * TaskMachine
 * MicroMachine
 * Michine
 * PipeDream
 */

// Bootstrap
$injector = new Injector;
$environment = new Environment(['processed' => 'processed', 'cleanedup' => 'cleaned up', 'custom' => 'finished']);
$injector->share($environment);

// Define tasks
$tf = new TaskFlux($injector);

$tf->task('start', function() {
    echo 'started'.PHP_EOL;
    return [ 'incoming' => 'output from start task' ];
})
->output([ 'string' => 'incoming' ]);

$tf->task('process', function(InputInterface $input, Environment $env) {
    echo $input->get('incoming').PHP_EOL;
    echo $env->get('processed').PHP_EOL;
    return [ 'success' => true ];
})
->input([ 'string' => 'incoming' ])
->output([ 'bool' => 'success' ]);

$tf->task('cleanup', function(Environment $env, InputInterface $input) {
    echo 'value of success: '.$input->get('success').PHP_EOL;
    echo $env->get('cleanedup').PHP_EOL;
})
->input([ 'bool' => 'success' ]);

$tf->task('logging', function() {
    echo 'logged'.PHP_EOL;
});

$tf->task('failed', function() {
    echo 'failed!'.PHP_EOL;
});

$tf->task('finish', CustomHandler::class);

// Setup a machine
$tf->machine('transcoder')
    ->first('start')->then('process')
    ->task('process')->then('cleanup')
    ->task('cleanup')
        ->when('input.get("success")', 'logging')
        ->when('!input.get("success")', 'failed')
    ->task('logging')->then('finish')
    ->finally('failed')
    ->finally('finish');

$output = $tf->run('transcoder');

print_r($output->toArray(), true);
