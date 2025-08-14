<?php

declare(strict_types=1);

use ConsoleForge\Contracts\OptDescriptorInterface;
use ConsoleForge\Descriptors\OptDescriptor;
use ConsoleForge\Exceptions\Opt\ArrayOptionDefaultTypeMismatch;
use ConsoleForge\Exceptions\Opt\ArrayOptionMustAcceptValue;
use ConsoleForge\Exceptions\Opt\InvalidOptionName;
use ConsoleForge\Exceptions\Opt\InvalidOptionShortcut;
use ConsoleForge\Exceptions\Opt\NegatableOptionCannotAcceptValue;
use ConsoleForge\Exceptions\Opt\NegatableOptionCannotBeArray;
use ConsoleForge\Exceptions\Opt\NonArrayOptionDefaultIsArray;
use ConsoleForge\Exceptions\Opt\ValueNoneOptionMustHaveNullDefault;

it('returns OptDescriptor', function () {
    $name = 'name';
    $shortcut = 'n';
    $description = 'description';

    $optDescriptor = new OptDescriptor(
        name: $name,
        shortcut: $shortcut,
        description: $description,
    );

    expect($optDescriptor)->toBeInstanceOf(OptDescriptorInterface::class)
        ->and($optDescriptor->name())->toBeString()
        ->and($optDescriptor->name())->toBe($name)
        ->and($optDescriptor->shortcut())->toBeString()
        ->and($optDescriptor->shortcut())->toBe($shortcut)
        ->and($optDescriptor->description())->toBeString()
        ->and($optDescriptor->description())->toBe($description)
        ->and($optDescriptor->negatable())->toBeBool()
        ->and($optDescriptor->negatable())->toBeFalse()
        ->and($optDescriptor->acceptValue())->toBeBool()
        ->and($optDescriptor->acceptValue())->toBeFalse()
        ->and($optDescriptor->isArray())->toBeBool()
        ->and($optDescriptor->isArray())->toBeFalse()
        ->and($optDescriptor->default())->toBeNull()
        ->and($optDescriptor->coercer())->toBeNull();
});

it('throws exception when name empty', function () {
    new OptDescriptor(
        name: '',
    );
})->throws(InvalidOptionName::class, "Invalid option name ''.");

it('throws exception when name is invalid', function () {
    new OptDescriptor(
        name: '-aZ_/|+-*',
    );
})->throws(InvalidOptionName::class, "Invalid option name '-aZ_/|+-*'.");

it('throws exception when shortcut is invalid', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: '-aZ_/|+-*',
    );
})->throws(InvalidOptionShortcut::class, "Invalid shortcut 'name'. Use letters, optionally separated by '|', e.g. 'a' or 'a|b'.");

it('throws exception when negatable and acceptValue are true', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: 'n',
        description: 'description',
        negatable: true,
        acceptValue: true,
    );
})->throws(NegatableOptionCannotAcceptValue::class, "Option '--name': negatable options cannot accept values.");

it('throws exception when negatable and isArray are true', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: 'n',
        description: 'description',
        negatable: true,
        isArray: true,
    );
})->throws(NegatableOptionCannotBeArray::class, "Option '--name': negatable options cannot be arrays.");

it('throws exception when isArray and acceptValue is false', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: 'n',
        description: 'description',
        acceptValue: false,
        isArray: true,
    );
})->throws(ArrayOptionMustAcceptValue::class, "Option '--name': array options must accept values.");

it('throws exception when acceptValue is false and default is not null', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: 'n',
        description: 'description',
        acceptValue: false,
        default: 'test',
    );
})->throws(ValueNoneOptionMustHaveNullDefault::class, "Option '--name' does not accept a value; default must be null.");

it('throws exception when isArray and default is not an array', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: 'n',
        description: 'description',
        acceptValue: true,
        isArray: true,
        default: 'test',
    );
})->throws(ArrayOptionDefaultTypeMismatch::class, "Option '--name' is array; default value must be array or null.");

it('throws exception when is not Array and default is an array', function () {
    new OptDescriptor(
        name: 'name',
        shortcut: 'n',
        description: 'description',
        acceptValue: true,
        isArray: false,
        default: [],
    );
})->throws(NonArrayOptionDefaultIsArray::class, "Option '--name' is not array; default value cannot be an array.");

// --- Factories / DX ---

it('factory flag() sets VALUE_NONE semantics', function () {
    $o = OptDescriptor::flag(
        name: 'force',
        shortcut: 'f',
        description: 'force desc',
    );

    expect($o->name())->toBe('force')
        ->and($o->shortcut())->toBe('f')
        ->and($o->description())->toBe('force desc')
        ->and($o->negatable())->toBeFalse()
        ->and($o->acceptValue())->toBeFalse()
        ->and($o->isArray())->toBeFalse()
        ->and($o->default())->toBeNull();
});

it('factory negatableFlag() sets negatable VALUE_NONE', function () {
    $o = OptDescriptor::negatableFlag(
        name: 'ansi',
        description: 'ansi desc',
    );

    expect($o->name())->toBe('ansi')
        ->and($o->negatable())->toBeTrue()
        ->and($o->acceptValue())->toBeFalse()
        ->and($o->isArray())->toBeFalse()
        ->and($o->default())->toBeNull();
});

it('factory value() keeps default for single value', function () {
    $o = OptDescriptor::value(
        name: 'env',
        shortcut: 'e',
        description: 'env desc',
        default: 'prod',
    );

    expect($o->name())->toBe('env')
        ->and($o->acceptValue())->toBeTrue()
        ->and($o->isArray())->toBeFalse()
        ->and($o->default())->toBe('prod');
});

it('factory values() sets [] when default is null and respects provided array', function () {
    $o1 = OptDescriptor::values(
        name: 'path',
        shortcut: 'p',
        description: 'paths',
    );

    expect($o1->isArray())->toBeTrue()
        ->and($o1->acceptValue())->toBeTrue()
        ->and($o1->default())->toBe([]);

    $o2 = OptDescriptor::values(
        name: 'tag',
        description: 'tags',
        default: ['a', 'b']);

    expect($o2->default())->toBe(['a', 'b'])
        ->and($o2->isArray())->toBeTrue()
        ->and($o2->acceptValue())->toBeTrue();
});

// --- Shortcut normalization & valid cases ---

it('normalizes empty string shortcut to null', function () {
    $o = new OptDescriptor(
        name: 'alpha',
        shortcut: '',
    );

    expect($o->shortcut())->toBeNull();
});

it('accepts multiple shortcuts "a|b|C"', function () {
    $o = new OptDescriptor(
        name: 'beta',
        shortcut: 'a|b|C',
    );

    expect($o->shortcut())->toBe('a|b|C');
});

// --- Positive regex for name ---

it('accepts valid option names matching the regex', function () {
    $o = new OptDescriptor(
        name: 'foo:bar_baz-123',
    );

    expect($o->name())->toBe('foo:bar_baz-123');
});

// --- Coercer behavior ---

it('stores a Closure coercer and returns it via coercer()', function () {
    $o = new OptDescriptor(
        name: 'count',
        acceptValue: true,
        coercer: fn ($v) => (int) $v,
    );

    $c = $o->coercer();

    expect($c)->not()->toBeNull()
        ->and($c('5'))->toBe(5);
});

it('accepts non-closure callable and converts it to Closure', function () {
    $o = new OptDescriptor(
        name: 'val',
        acceptValue: true,
        coercer: 'strval',
    );

    $c = $o->coercer();

    expect($c)->not()->toBeNull()
        ->and($c(123))->toBe('123');
});

it('withCoercer() returns a new instance and preserves other fields', function () {
    $o1 = OptDescriptor::value(
        name: 'env',
        shortcut: 'e',
        description: 'desc',
        default: 'prod',
    );

    $o2 = $o1->withCoercer(
        coercer: fn ($v) => strtoupper((string) $v),
    );

    // Same base state
    expect($o2->name())->toBe($o1->name())
        ->and($o2->shortcut())->toBe($o1->shortcut())
        ->and($o2->description())->toBe($o1->description())
        ->and($o2->negatable())->toBe($o1->negatable())
        ->and($o2->acceptValue())->toBe($o1->acceptValue())
        ->and($o2->isArray())->toBe($o1->isArray())
        ->and($o2->default())->toBe($o1->default());

    // Different coercers
    expect($o1->coercer())->toBeNull();

    $c2 = $o2->coercer();

    expect($c2)->not()->toBeNull()
        ->and($c2('ok'))->toBe('OK');
});
