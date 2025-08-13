<?php

declare(strict_types=1);

use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\CommandRegistryInterface;

beforeEach(function () {
    $this->mock = Mockery::mock(CommandRegistryInterface::class);
});

it('returns CommandRegistryInterface', function () {
    expect($this->mock)->toBeInstanceOf(CommandRegistryInterface::class);
});

it('add new command', function () {
    $commandDescriptorMock = Mockery::mock(CommandDescriptorInterface::class);

    $commandDescriptorMock->shouldReceive('name')
        ->once()
        ->andReturn('greet');

    $this->mock->shouldReceive('add')
        ->once()
        ->with($commandDescriptorMock)
        ->andReturnSelf();

    $this->mock->add($commandDescriptorMock);

    $this->mock->shouldReceive('all')
        ->once()
        ->andReturn([$commandDescriptorMock->name() => $commandDescriptorMock]);

    expect($this->mock->all())->toHaveCount(1);
});

it('get a command', function () {
    $commandDescriptorMock = Mockery::mock(CommandDescriptorInterface::class);

    $this->mock->shouldReceive('add')
        ->once()
        ->with($commandDescriptorMock)
        ->andReturnSelf();

    $this->mock->add($commandDescriptorMock);

    $this->mock->shouldReceive('get')
        ->times(2)
        ->with('greet')
        ->andReturn($commandDescriptorMock);

    expect($this->mock->get('greet'))->toBeInstanceOf(CommandDescriptorInterface::class)
        ->and($this->mock->get('greet'))->toBe($commandDescriptorMock);
});

it('get all commands', function () {
    $this->mock->shouldReceive('all')
        ->times(2)
        ->andReturn([]);

    expect($this->mock->all())->toBeArray()
        ->and($this->mock->all())->toHaveCount(0);
});

it('has a command', function () {
    $this->mock->shouldReceive('has')
        ->times(2)
        ->with('greet')
        ->andReturnFalse();

    expect($this->mock->has('greet'))->toBeBool()
        ->and($this->mock->has('greet'))->toBeFalse();
});

it('remove a command', function () {
    $commandDescriptorMock = Mockery::mock(CommandDescriptorInterface::class);

    $this->mock->shouldReceive('add')
        ->once()
        ->with($commandDescriptorMock)
        ->andReturnSelf();

    $this->mock->add($commandDescriptorMock);

    $this->mock->shouldReceive('remove')
        ->once()
        ->with('greet');

    $this->mock->remove('greet');

    $this->mock->shouldReceive('all')
        ->once()
        ->andReturn([]);

    expect($this->mock->all())->toHaveCount(0);
});

it('clear all commands', function () {
    $this->mock->shouldReceive('clear')
        ->once();

    $this->mock->clear();

    $this->mock->shouldReceive('all')
        ->once()
        ->andReturn([]);

    expect($this->mock->all())->toHaveCount(0);
});

afterEach(function () {
    Mockery::close();
});
