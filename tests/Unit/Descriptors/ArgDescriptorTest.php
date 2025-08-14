<?php

declare(strict_types=1);

use ConsoleForge\Contracts\ArgDescriptorInterface;
use ConsoleForge\Descriptors\ArgDescriptor;
use ConsoleForge\Exceptions\Arg\ArrayDefaultTypeMismatch;
use ConsoleForge\Exceptions\Arg\InvalidArgumentName;
use ConsoleForge\Exceptions\Arg\NonArrayDefaultIsArray;
use ConsoleForge\Exceptions\Arg\RequiredArgHasDefault;

it('returns ArgDescriptor', function () {
    $name = 'name';
    $description = 'description';

    $argDescriptor = new ArgDescriptor(
        name: $name,
        description: $description,
    );

    expect($argDescriptor)->toBeInstanceOf(ArgDescriptorInterface::class)
        ->and($argDescriptor->name())->toBeString()
        ->and($argDescriptor->name())->toBe($name)
        ->and($argDescriptor->description())->toBeString()
        ->and($argDescriptor->description())->toBe($description)
        ->and($argDescriptor->required())->toBeBool()
        ->and($argDescriptor->required())->toBeFalse()
        ->and($argDescriptor->isArray())->toBeBool()
        ->and($argDescriptor->isArray())->toBeFalse()
        ->and($argDescriptor->default())->toBeNull()
        ->and($argDescriptor->coercer())->toBeNull();
});

it('throws exception when name is empty', function () {
    new ArgDescriptor('');
})->throws(InvalidArgumentName::class, "Invalid argument name ''.");

it('throws exception when is required and default is not null', function () {
    new ArgDescriptor(
        name: 'name',
        required: true,
        default: 'test',
    );
})->throws(RequiredArgHasDefault::class, "Argument 'name' is REQUIRED and cannot have a default value.");

it('throws exception when is Array and default is not array', function () {
    new ArgDescriptor(
        name: 'name',
        isArray: true,
        default: 'test',
    );
})->throws(ArrayDefaultTypeMismatch::class, "Argument 'name' is array; default value must be array or null.");

it('throws exception when is not Array and default is array', function () {
    new ArgDescriptor(
        name: 'name',
        isArray: false,
        default: [],
    );
})->throws(NonArrayDefaultIsArray::class, "Argument 'name' is not array; default value cannot be an array.");

it('throws exception when does not have a simple name', function () {
    new ArgDescriptor(
        name: 'name with space',
    );
})->throws(InvalidArgumentName::class, "Invalid argument name 'name with space'.");

// --- Factories ---

it('withRequired() sets required=true and default=null', function () {
    $a = ArgDescriptor::withRequired(
        name: 'user',
        description: 'User desc',
    );

    expect($a->name())->toBe('user')
        ->and($a->description())->toBe('User desc')
        ->and($a->required())->toBeTrue()
        ->and($a->default())->toBeNull()
        ->and($a->isArray())->toBeFalse();
});

it('optional() keeps default provided (non-array)', function () {
    $a = ArgDescriptor::optional(
        name: 'env',
        description: 'Env desc',
        default: 'prod',
    );

    expect($a->required())->toBeFalse()
        ->and($a->default())->toBe('prod')
        ->and($a->isArray())->toBeFalse();
});

it('arrayArg() sets [] as default when not required and no default provided', function () {
    $a = ArgDescriptor::arrayArg(
        name: 'paths',
        description: 'Paths',
    );

    expect($a->isArray())->toBeTrue()
        ->and($a->required())->toBeFalse()
        ->and($a->default())->toBe([]); // DX nicer default
});

it('arrayArg() uses provided default array when not required', function () {
    $a = ArgDescriptor::arrayArg(
        name: 'tags',
        description: 'Tags',
        default: ['a', 'b'],
    );

    expect($a->default())->toBe(['a', 'b'])
        ->and($a->isArray())->toBeTrue()
        ->and($a->required())->toBeFalse();
});

it('arrayArg() forces default to null when required even if default provided', function () {
    $a = ArgDescriptor::arrayArg(
        name: 'ids',
        description: 'IDs',
        required: true,
        default: ['x'],
    );

    expect($a->required())->toBeTrue()
        ->and($a->isArray())->toBeTrue()
        ->and($a->default())->toBeNull();
});

// --- Coercer ---

it('accepts a Closure coercer and returns it via coercer()', function () {
    $a = new ArgDescriptor(
        name: 'count',
        coercer: fn ($v) => (int) $v,
    );

    $coercer = $a->coercer();

    expect($coercer)->not()->toBeNull()
        ->and($coercer('5'))->toBe(5);
});

it('accepts a non-closure callable and stores it as Closure', function () {
    $callable = 'strval';

    $a = new ArgDescriptor(
        name: 'val',
        coercer: $callable,
    );

    $coercer = $a->coercer();

    expect($coercer)->not()->toBeNull()
        ->and($coercer(123))->toBe('123');
});

it('withCoercer() returns a new instance and leaves the original unchanged', function () {
    $a1 = new ArgDescriptor(
        name: 'name',
        description: 'desc',
    );

    $a2 = $a1->withCoercer(
        coercer: fn ($v) => strtoupper((string) $v),
    );

    // Mismo estado base
    expect($a2->name())->toBe($a1->name())
        ->and($a2->description())->toBe($a1->description())
        ->and($a2->required())->toBe($a1->required())
        ->and($a2->isArray())->toBe($a1->isArray())
        ->and($a2->default())->toBe($a1->default());

    // Coercers distintos
    expect($a1->coercer())->toBeNull();

    $c2 = $a2->coercer();

    expect($c2)->not()->toBeNull()
        ->and($c2('ok'))->toBe('OK');
});

// --- Regex positiva de nombre ---

it('allows simple valid names matching the regex', function () {
    $a = new ArgDescriptor(name: 'foo:bar_baz-123');

    expect($a->name())->toBe('foo:bar_baz-123');
});
