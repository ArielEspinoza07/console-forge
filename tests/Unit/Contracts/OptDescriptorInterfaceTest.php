<?php

declare(strict_types=1);

use ConsoleForge\Contracts\OptDescriptorInterface;

beforeEach(function () {
    $this->mock = Mockery::mock(OptDescriptorInterface::class);
});

it('returns OptDescriptorInterface', function () {
    expect($this->mock)->toBeInstanceOf(OptDescriptorInterface::class);
});

it('returns option name', function () {
    $name = 'yell';

    $this->mock
        ->shouldReceive('name')
        ->once()
        ->andReturn($name);

    $mockName = $this->mock->name();

    expect($mockName)->toBeString()
        ->and($mockName)->toBe($name);
});

it('returns option shortcut', function () {
    $shortcut = 'y';

    $this->mock
        ->shouldReceive('shortcut')
        ->once()
        ->andReturn($shortcut);

    $mockShortcut = $this->mock->shortcut();

    expect($mockShortcut)->toBeString()
        ->and($mockShortcut)->toBe($shortcut);
});

it('returns option description', function () {
    $description = 'Uppercase';

    $this->mock
        ->shouldReceive('description')
        ->once()
        ->andReturn($description);

    $mockDescription = $this->mock->description();

    expect($mockDescription)->toBeString()
        ->and($mockDescription)->toBe($description);
});

it('returns option negatable', function (bool $negatable) {
    $this->mock
        ->shouldReceive('negatable')
        ->once()
        ->andReturn($negatable);

    $mockNegatable = $this->mock->negatable();

    expect($mockNegatable)->toBeBool()
        ->and($mockNegatable)->toBe($negatable);
})->with([true, false]);

it('returns option acceptValue', function (bool $acceptValue) {
    $this->mock
        ->shouldReceive('acceptValue')
        ->once()
        ->andReturn($acceptValue);

    $mockAcceptValue = $this->mock->acceptValue();

    expect($mockAcceptValue)->toBeBool()
        ->and($mockAcceptValue)->toBe($acceptValue);
})->with([true, false]);

it('returns option isArray', function (bool $isArray) {
    $this->mock
        ->shouldReceive('isArray')
        ->once()
        ->andReturn($isArray);

    $mockIsArray = $this->mock->isArray();

    expect($mockIsArray)->toBeBool()
        ->and($mockIsArray)->toBe($isArray);
})->with([true, false]);

it('returns option default', function (mixed $default) {
    $this->mock
        ->shouldReceive('default')
        ->once()
        ->andReturn($default);

    $mockDefault = $this->mock->default();

    expect($mockDefault)->toBe($default);
})->with([null, 'n', 'y']);

it('returns option coercer', function () {
    $this->mock
        ->shouldReceive('coercer')
        ->once()
        ->andReturn(null);

    expect($this->mock->coercer())->toBeNull();
});

afterEach(function () {
    Mockery::close();
});
