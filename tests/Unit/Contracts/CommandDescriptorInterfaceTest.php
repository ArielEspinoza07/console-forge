<?php

declare(strict_types=1);

use ConsoleForge\Contracts\CommandDescriptorInterface;

beforeEach(function () {
    $this->mock = Mockery::mock(CommandDescriptorInterface::class);
});

it('returns CommandDescriptorInterface', function () {
    expect($this->mock)->toBeInstanceOf(CommandDescriptorInterface::class);
});

it('returns command name', function () {
    $name = 'greet';

    $this->mock
        ->shouldReceive('name')
        ->once()
        ->andReturn($name);

    $mockName = $this->mock->name();

    expect($mockName)->toBeString()
        ->and($mockName)->toBe($name);
});

it('returns command description', function () {
    $description = 'Greet someone';

    $this->mock
        ->shouldReceive('description')
        ->once()
        ->andReturn($description);

    $mockDescription = $this->mock->description();

    expect($mockDescription)->toBeString()
        ->and($mockDescription)->toBe($description);
});

it('returns command arguments', function () {
    $args = [];

    $this->mock
        ->shouldReceive('args')
        ->once()
        ->andReturn($args);

    $mockArgs = $this->mock->args();

    expect($mockArgs)->toBeArray()
        ->and($mockArgs)->toHaveCount(count($args));
});

it('returns command options', function () {
    $opts = [];

    $this->mock
        ->shouldReceive('opts')
        ->once()
        ->andReturn($opts);

    $mockOpts = $this->mock->opts();

    expect($mockOpts)->toBeArray()
        ->and($mockOpts)->toHaveCount(count($opts));
});

it('returns command handler', function () {
    $this->mock
        ->shouldReceive('handler')
        ->once()
        ->andReturn(null);

    expect($this->mock->handler())->toBeNull();
});

it('returns command help', function () {
    $this->mock
        ->shouldReceive('help')
        ->once()
        ->andReturn(null);

    expect($this->mock->help())->toBeNull();
});

it('returns command hidden', function (bool $hidden) {
    $this->mock
        ->shouldReceive('hidden')
        ->once()
        ->andReturn($hidden);

    $mockHidden = $this->mock->hidden();

    expect($mockHidden)->toBeBool()
        ->and($mockHidden)->toBe($hidden);
})->with([true, false]);

it('returns command extra', function () {
    $extra = [];

    $this->mock
        ->shouldReceive('extra')
        ->once()
        ->andReturn($extra);

    $mockExtra = $this->mock->extra();

    expect($mockExtra)->toBeArray()
        ->and($mockExtra)->toHaveCount(count($extra));
});

afterEach(function () {
    Mockery::close();
});
