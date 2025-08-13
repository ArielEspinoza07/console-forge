<?php

use ConsoleForge\App;
use Symfony\Component\Console\Application;

it('creates a Symfony Console Application via App::make()', function () {
    $app = App::make();
    expect($app)->toBeInstanceOf(Application::class);
});
