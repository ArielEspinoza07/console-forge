<?php

declare(strict_types=1);

use ConsoleForge\Contracts\ArgDescriptorInterface;

beforeEach(function () {
    $this->mock = Mockery::mock(ArgDescriptorInterface::class);
});

it('returns ArgDescriptorInterface', function () {
    expect($this->mock)->toBeInstanceOf(ArgDescriptorInterface::class);
});

it('returns argument name', function () {
    $name = 'title';

    $this->mock
        ->shouldReceive('name')
        ->once()
        ->andReturn($name);

    $mockName = $this->mock->name();

    expect($mockName)->toBeString()
        ->and($mockName)->toBe($name);
});

it('returns argument description', function () {
    $description = 'Person name';

    $this->mock
        ->shouldReceive('description')
        ->once()
        ->andReturn($description);

    $mockDescription = $this->mock->description();

    expect($mockDescription)->toBeString()
        ->and($mockDescription)->toBe($description);
});

it('returns argument required', function (bool $required) {
    $this->mock
        ->shouldReceive('required')
        ->once()
        ->andReturn($required);

    $mockRequired = $this->mock->required();

    expect($mockRequired)->toBeBool()
        ->and($mockRequired)->toBe($required);
})->with([true, false]);

it('returns argument is array', function (bool $isArray) {
    $this->mock
        ->shouldReceive('isArray')
        ->once()
        ->andReturn($isArray);

    $mockIsArray = $this->mock->isArray();

    expect($mockIsArray)->toBeBool()
        ->and($mockIsArray)->toBe($isArray);
})->with([true, false]);

it('returns argument default value', function (mixed $defaultValue) {
    $this->mock
        ->shouldReceive('default')
        ->once()
        ->andReturn($defaultValue);

    $mockDefaultValue = $this->mock->default();

    expect($mockDefaultValue)->toBe($defaultValue);
})->with([null, 'test']);

it('returns argument coercer', function () {
    $this->mock
        ->shouldReceive('coercer')
        ->once()
        ->andReturn(null);

    $mockCoercer = $this->mock->coercer();

    expect($mockCoercer)->toBeNull();
})->with([null, fn () => true]);

afterEach(function () {
    Mockery::close();
});
