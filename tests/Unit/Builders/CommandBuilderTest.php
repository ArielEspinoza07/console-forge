<?php

declare(strict_types=1);

use ConsoleForge\Builders\CommandBuilder;
use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Descriptors\ArgDescriptor;
use ConsoleForge\Descriptors\OptDescriptor;
use ConsoleForge\Exceptions\DescriptorException;

// ---------- make() ----------

it('make() rejects invalid names (empty or bad regex)', function () {
    CommandBuilder::make(name: '');
})->throws(LogicException::class, "Invalid command name ''.");

it('make() accepts a valid name', function () {
    $b = CommandBuilder::make(name: 'foo:bar_baz-123');

    expect($b)->toBeInstanceOf(CommandBuilder::class);
});

// ---------- Happy path + build ----------

it('builds a CommandDescriptorInterface with chained setters', function () {
    $builder = CommandBuilder::make(name: 'app:demo')
        ->description('My command')
        ->arg(ArgDescriptor::withRequired('user'))
        ->args(
            ArgDescriptor::optional(name: 'env', default: 'prod')
        )
        ->opt(OptDescriptor::flag('force', 'f', 'force it'))
        ->opts(
            OptDescriptor::value('config', 'c', 'config file', '/etc/app.conf')
        )
        ->handler(fn () => null)
        ->help('some help')
        ->hidden()
        ->extra(['a' => 1])
        ->mergeExtra(['b' => 2, 'a' => 3]); // merge debe pisar 'a' => 3

    $cmd = $builder->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->name())->toBe('app:demo')
        ->and($cmd->description())->toBe('My command')
        ->and($cmd->help())->toBe('some help')
        ->and($cmd->hidden())->toBeTrue()
        ->and($cmd->extra())->toBe(['a' => 3, 'b' => 2]);

    // Args/opts propagados
    $args = $cmd->args();
    $opts = $cmd->opts();

    expect($args)->toHaveCount(2)
        ->and($args[0]->name())->toBe('user')
        ->and($args[0]->required())->toBeTrue()
        ->and($args[1]->name())->toBe('env')
        ->and($args[1]->default())->toBe('prod');

    expect($opts)->toHaveCount(2)
        ->and($opts[0]->name())->toBe('force')
        ->and($opts[0]->acceptValue())->toBeFalse()
        ->and($opts[1]->name())->toBe('config')
        ->and($opts[1]->acceptValue())->toBeTrue()
        ->and($opts[1]->default())->toBe('/etc/app.conf');
});

// ---------- setArgs / setOptsreplace lists ----------

it('setArgs() replaces args list', function () {
    $b = CommandBuilder::make('cmd')
        ->arg(ArgDescriptor::withRequired('a'))
        ->setArgs([ArgDescriptor::optional('x')]);

    $cmd = $b->build();

    expect($cmd->args())->toHaveCount(1)
        ->and($cmd->args()[0]->name())->toBe('x');
});

it('setOpts() replaces opts list', function () {
    $b = CommandBuilder::make('cmd')
        ->opt(OptDescriptor::flag('f'))
        ->setOpts([OptDescriptor::value('env', default: 'dev')]);

    $cmd = $b->build();
    expect($cmd->opts())->toHaveCount(1)
        ->and($cmd->opts()[0]->name())->toBe('env')
        ->and($cmd->opts()[0]->default())->toBe('dev');
});

// ---------- help(null) allowed in builder ----------

it('help(null) clears help before build', function () {
    $cmd = CommandBuilder::make('cmd')
        ->help('something')
        ->help(null)
        ->build();

    expect($cmd->help())->toBeNull();
});

// ---------- hidden() toggle ----------

it('hidden(true) sets hidden on descriptor', function () {
    $cmd = CommandBuilder::make('cmd')->hidden()->build();

    expect($cmd->hidden())->toBeTrue();
});

it('hidden(false) keeps as visible', function () {
    $cmd = CommandBuilder::make('cmd')->hidden(false)->build();

    expect($cmd->hidden())->toBeFalse();
});

// ---------- extra() vs mergeExtra() ----------

it('extra() replaces, mergeExtra() merges overriding existing keys', function () {
    $cmd = CommandBuilder::make('cmd')
        ->extra(['a' => 1, 'b' => 2])
        ->mergeExtra(['b' => 9, 'c' => 3])
        ->build();

    expect($cmd->extra())->toBe(['a' => 1, 'b' => 9, 'c' => 3]);
});

// ---------- valid handlers ----------

it('accepts closure handler', function () {
    $cmd = CommandBuilder::make('cmd')
        ->handler(fn () => null)
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('accepts invokable object handler', function () {
    $invokeClass = new class
    {
        public function __invoke() {}
    };

    $cmd = CommandBuilder::make('cmd')
        ->handler($invokeClass)
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('accepts array handler with object and method', function () {
    $obj = new class
    {
        public function run() {}
    };

    $cmd = CommandBuilder::make('cmd')
        ->handler([$obj, 'run'])
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('accepts array handler with class and static method', function () {
    $cls = new class
    {
        public static function run() {}
    };

    $cmd = CommandBuilder::make('cmd')
        ->handler([$cls, 'run'])
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('accepts string function handler', function () {
    function _test_sample_handler() {}

    $cmd = CommandBuilder::make('cmd')
        ->handler('_test_sample_handler')
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('accepts string Class::method handler', function () {
    $cls = new class
    {
        public static function run() {}
    };

    $cmd = CommandBuilder::make('cmd')
        ->handler($cls::class.'::run')
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('accepts invokable class-string handler', function () {
    $cls = new class
    {
        public function __invoke() {}
    };

    $cmd = CommandBuilder::make('cmd')
        ->handler($cls::class)
        ->build();

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

// ---------- errors delegated to the VO (DescriptorException) ----------

it('fails when arg array is not last', function () {
    $arr = ArgDescriptor::arrayArg('items');
    $after = ArgDescriptor::optional('tail');

    CommandBuilder::make('cmd')
        ->args($arr, $after)
        ->build();
})->throws(DescriptorException::class, 'Array argument must be the last argument.');

it('fails when required arg appears after an optional', function () {
    $opt = ArgDescriptor::optional('a');
    $req = ArgDescriptor::withRequired('b');

    CommandBuilder::make('cmd')
        ->args($opt, $req)
        ->build();
})->throws(DescriptorException::class, 'Required arguments cannot appear after optional ones.');

it('fails when duplicate arg names', function () {
    $a1 = ArgDescriptor::optional('dup');
    $a2 = ArgDescriptor::withRequired('dup');

    CommandBuilder::make('cmd')
        ->args($a1, $a2)
        ->build();
})->throws(DescriptorException::class, "Duplicate argument 'dup'.");

it('fails when duplicate option names', function () {
    $o1 = OptDescriptor::flag('force');
    $o2 = OptDescriptor::value('force');

    CommandBuilder::make('cmd')
        ->opts($o1, $o2)
        ->build();
})->throws(DescriptorException::class, "Duplicate option '--force'.");

it('fails when duplicate shortcuts', function () {
    $o1 = OptDescriptor::flag('a1', 'a');
    $o2 = OptDescriptor::value('a2', 'a');

    CommandBuilder::make('cmd')
        ->opts($o1, $o2)
        ->build();
})->throws(DescriptorException::class, "Duplicate shortcut '-a'.");
