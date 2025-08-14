<?php

declare(strict_types=1);

use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Descriptors\ArgDescriptor;
use ConsoleForge\Descriptors\CommandDescriptor;
use ConsoleForge\Descriptors\OptDescriptor;
use ConsoleForge\Exceptions\DescriptorException;

it('creates a descriptor with defaults', function () {
    $cmd = new CommandDescriptor('test');

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->name())->toBe('test')
        ->and($cmd->description())->toBe('')
        ->and($cmd->args())->toBe([])
        ->and($cmd->opts())->toBe([])
        ->and($cmd->handler())->toBeNull()
        ->and($cmd->help())->toBeNull()
        ->and($cmd->hidden())->toBeFalse()
        ->and($cmd->extra())->toBe([]);
});

it('rejects invalid command name', function () {
    new CommandDescriptor('');
})->throws(DescriptorException::class, "Invalid command name ''.");

it('rejects arg not implementing interface', function () {
    new CommandDescriptor(
        name: 'test',
        args: [new stdClass],
    );
})->throws(DescriptorException::class, 'Arg #0 must implement ArgDescriptorInterface.');

it('rejects duplicate arg names', function () {
    $a = new ArgDescriptor(name: 'a');

    new CommandDescriptor(
        name: 'test',
        args: [$a, $a],
    );
})->throws(DescriptorException::class, "Duplicate argument 'a'.");

it('rejects array arg not last', function () {
    $arr = new ArgDescriptor(
        name: 'arr',
        required: false,
        isArray: true,
    );
    $other = new ArgDescriptor(name: 'b');

    new CommandDescriptor(
        name: 'test',
        args: [$arr, $other],
    );
})->throws(DescriptorException::class, 'Array argument must be the last argument.');

it('rejects required arg after optional', function () {
    $opt = new ArgDescriptor(
        name: 'a',
        required: false,
    );
    $req = new ArgDescriptor(
        name: 'b',
        required: true,
    );

    new CommandDescriptor(
        name: 'test',
        args: [$opt, $req],
    );
})->throws(DescriptorException::class, 'Required arguments cannot appear after optional ones.');

it('rejects opt not implementing interface', function () {
    new CommandDescriptor(
        name: 'test',
        opts: [new stdClass],
    );
})->throws(DescriptorException::class, 'Opt #0 must implement OptDescriptorInterface.');

it('rejects duplicate opts by name', function () {
    $o = new OptDescriptor(name: 'o');

    new CommandDescriptor(
        name: 'test',
        opts: [$o, $o],
    );
})->throws(DescriptorException::class, "Duplicate option '--o'.");

it('rejects duplicate shortcuts', function () {
    $o1 = new OptDescriptor(
        name: 'o1',
        shortcut: 'a',
    );
    $o2 = new OptDescriptor(
        name: 'o2',
        shortcut: 'a',
    );

    new CommandDescriptor(
        name: 'test',
        opts: [$o1, $o2],
    );
})->throws(DescriptorException::class, "Duplicate shortcut '-a'.");

it('rejects extra with non-string keys', function () {
    new CommandDescriptor(
        name: 'test',
        extra: [1 => 'val'],
    );
})->throws(DescriptorException::class, 'Extra keys must be strings.');

it('accepts closure handler', function () {
    $c = fn () => null;

    $cmd = new CommandDescriptor(
        name: 't',
        handler: $c,
    );
    expect($cmd->handler())->toBe($c);
});

it('rejects non-invokable object handler', function () {
    new CommandDescriptor(
        name: 't',
        handler: new stdClass,
    );
})->throws(DescriptorException::class, 'Invalid handler type: stdClass. Must be callable, array callable, string callable, invokable object, or invokable class-string.');

it('accepts invokable object handler', function () {
    $invokable = new class
    {
        public function __invoke() {}
    };

    $cmd = new CommandDescriptor(
        name: 't',
        handler: $invokable,
    );

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->handler())->toBe($invokable);
});

it('rejects array handler with wrong size', function () {
    new CommandDescriptor(
        name: 't',
        handler: [new stdClass],
    );
})->throws(DescriptorException::class, 'Array handler must have exactly 2 elements: [object|class, method].');

it('rejects array handler with bad method', function () {
    $obj = new class {};

    new CommandDescriptor(
        name: 't',
        handler: [$obj, 'missing'],
    );
})->throws(DescriptorException::class);

it('accepts array handler with object and method', function () {
    $obj = new class
    {
        public function run() {}
    };

    $cmd = new CommandDescriptor(
        name: 't',
        handler: [$obj, 'run'],
    );

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->handler())->toBe([$obj, 'run']);
});

it('accepts array handler with class and method', function () {
    $class = new class
    {
        public static function run() {}
    };
    $name = $class::class;

    $cmd = new CommandDescriptor(
        name: 't',
        handler: [$name, 'run'],
    );

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('rejects string handler invalid', function () {
    new CommandDescriptor(
        name: 't',
        handler: 'not_a_function',
    );
})->throws(DescriptorException::class, "String handler 'not_a_function' is not a valid function, method, or invokable class-string.");

it('accepts string handler as function name', function () {
    $func = __NAMESPACE__.'\\sample_fn';
    function sample_fn() {}

    $cmd = new CommandDescriptor(
        name: 't',
        handler: $func,
    );

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->handler())->toBe($func);
});

it('accepts string handler as Class::method', function () {
    $class = new class
    {
        public static function run() {}
    };
    $name = $class::class.'::run';

    $cmd = new CommandDescriptor(
        name: 't',
        handler: $name,
    );

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->handler())->toBe($name);
});

it('accepts string handler as invokable class-string', function () {
    $class = new class
    {
        public function __invoke() {}
    };
    $name = $class::class;

    $cmd = new CommandDescriptor(
        name: 't',
        handler: $name,
    );

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->handler())->toBe($name);
});

it('fromArray builds descriptor', function () {
    $a = new ArgDescriptor(name: 'a');
    $o = new OptDescriptor(name: 'o');
    $data = [
        'name' => 'cmd',
        'description' => 'desc',
        'args' => [$a],
        'opts' => [$o],
        'handler' => fn () => null,
        'help' => 'help',
        'hidden' => true,
        'extra' => ['foo' => 'bar'],
    ];

    $cmd = CommandDescriptor::fromArray(data: $data);

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->name())->toBe($data['name'])
        ->and($cmd->description())->toBe($data['description'])
        ->and($cmd->args())->toBe($data['args'])
        ->and($cmd->opts())->toBe($data['opts'])
        ->and($cmd->handler())->toBe($data['handler'])
        ->and($cmd->help())->toBe($data['help'])
        ->and($cmd->hidden())->toBe($data['hidden'])
        ->and($cmd->extra())->toBe($data['extra']);
});

it('assertValid passes for valid descriptor', function () {
    $cmd = new CommandDescriptor('t');

    expect($cmd)->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->assertValid())->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($cmd->assertValid())->toBe($cmd);
});
