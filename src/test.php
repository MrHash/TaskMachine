<?php

require 'vendor/autoload.php';

use Auryn\Injector;
use TaskFlux\CustomHandler;
use TaskFlux\Environment;
use TaskFlux\TaskFlux;
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

$tf->machine('transcoder')
    ->task('start')->initial(true)->then('process')
    ->task('process')->then('cleanup')
    ->task('cleanup')->then('finish')
    ->task('finish')->final(true)->transitions([]);

$output = $tf->run('transcoder');
print_r($output->toArray(), true);