#TaskMachine
###Modular micro-service task pipelining with validated state machine integrity.

Define micro-service tasks and arrange them into state machine managed flows with a simple and expressive API. You can create build tool chains, processing pipelines, utility services, or even serve web pages...

####State machines for the masses!

#Examples
##Simple pipeline
We define two simple inline tasks which are independent. The machine executes the two tasks in order and then finishes.
```php
$tm = new TaskMachine;

$tm->task('hello', function () {
  echo 'Hello World';
});

$tm->task('goodbye', function () {
  echo 'Goodbye World';
});

$tm->machine('greetings')
  ->first('hello')     // select the initial task
    ->then('goodbye')  // specify the next task to transition to
  ->finally('goodbye') // select the final task
  ->run();             // run the machine!
```

##Simple pipeline with DI & I/O
Now we introduce some more tasks with DI and input/output. Tasks are isolated by definition and have expected inputs and outputs.
```php
// Bootstrap your own Auryn injector and throw it in
$tm = new TaskMachine($myInjector);

$tm->task(
  'translate',
  function (InputInterface $input, MyTranslationInterface $translator) {
    // Auryn injects service fully constructed. Run your things.
    $translation = $translator->translate($input->get('text'));
    return ['text' => $translation];
  }
)
// Declare expected input and output for this task
->input(['string' => 'text'])->output(['string' => 'text']);

// Input from previous task is injectable and immutable
$tm->task('echo', function(InputInterface $input) {
  echo $input->get('text');
})->input(['string' => 'text']);

$tm->task('goodbye', function () {
  echo 'Goodbye World';
});

$tm->machine('greetings')
  ->first('translate')->then('echo')
  ->task('echo')->then('goodbye')
  ->finally('goodbye')
  ->run(['text' => 'Hello World']); // run the machine with input!
```

>##Any faults in the configuration of your machine will result in a build error! Tasks must be linked together correctly and have valid and unambiguous transitions.
