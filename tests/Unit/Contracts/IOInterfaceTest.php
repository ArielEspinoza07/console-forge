<?php

declare(strict_types=1);

use ConsoleForge\Contracts\IOInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

beforeEach(function () {
    $this->mock = Mockery::mock(IOInterface::class);
});

it('returns IOInterface', function () {
    expect($this->mock)->toBeInstanceOf(IOInterface::class);
});

it('write line', function () {
    $arg = 'test';

    $this->mock
        ->shouldReceive('writeln')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $arg);

    $this->mock->writeln($arg);
});

it('write success', function () {
    $successText = 'test success';

    $this->mock
        ->shouldReceive('success')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $successText);

    $this->mock->success($successText);
});

it('write error', function () {
    $errorText = 'test error';

    $this->mock
        ->shouldReceive('error')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $errorText);

    $this->mock->error($errorText);
});

it('write info', function () {
    $infoText = 'test info';

    $this->mock
        ->shouldReceive('info')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $infoText);

    $this->mock->info($infoText);
});

it('write warning', function () {
    $warningText = 'test warning';

    $this->mock
        ->shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $warningText);

    $this->mock->warning($warningText);
});

it('write note', function () {
    $noteText = 'test note';

    $this->mock
        ->shouldReceive('note')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $noteText);

    $this->mock->note($noteText);
});

it('write caution', function () {
    $cautionText = 'test caution';

    $this->mock
        ->shouldReceive('caution')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $cautionText);

    $this->mock->caution($cautionText);
});

it('write section', function () {
    $sectionText = 'test section';

    $this->mock
        ->shouldReceive('section')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $sectionText);

    $this->mock->section($sectionText);
});

it('write title', function () {
    $title = 'test title';

    $this->mock
        ->shouldReceive('title')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $title);

    $this->mock->title($title);
});

it('write table', function () {
    $table = [
        'headers' => ['name', 'age'],
        'rows' => [['John', 25]],
    ];

    $this->mock
        ->shouldReceive('table')
        ->once()
        ->withArgs(function (array $headers, array $rows) use ($table) {
            return count($headers) === count($table['headers'])
                && array_values($headers) === array_values($table['headers'])
                && count($rows) === count($table['rows']);
        });

    $this->mock->table($table['headers'], $table['rows']);
});

it('write new line', function () {
    $newLine = 2;

    $this->mock
        ->shouldReceive('newLine')
        ->once()
        ->withArgs(fn (int $argument) => $argument === $newLine);

    $this->mock->newLine(2);
});

it('render html', function () {
    $html = '<div> <h1>Test Render</h1> </div>';

    $this->mock
        ->shouldReceive('render')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $html);

    $this->mock->render($html);
});

it('ask question', function () {
    $question = 'test question';
    $response = 'y';

    $this->mock
        ->shouldReceive('ask')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $question)
        ->andReturn($response);

    $mockResponse = $this->mock->ask($question);

    expect($mockResponse)->toBeString()
        ->and($mockResponse)->toBe($response);
});

it('confirm question', function (bool $response) {
    $question = 'is a test';

    $this->mock
        ->shouldReceive('confirm')
        ->once()
        ->withArgs(fn (string $argument) => $argument === $question)
        ->andReturn($response);

    $mockResponse = $this->mock->confirm($question);

    expect($mockResponse)->toBeBool()
        ->and($mockResponse)->toBe($response);
})->with([true, false]);

it('choice question', function () {
    $choiceQuestion = 'select one';
    $options = ['apple', 'grape', 'peach'];
    $response = 'peach';

    $this->mock
        ->shouldReceive('choice')
        ->once()
        ->withArgs(function (string $question, array $choices) use ($choiceQuestion, $options) {
            return $question === $choiceQuestion
                && count($choices) === count($options)
                && array_values($choices) === array_values($options);
        })
        ->andReturn($response);

    $mockResponse = $this->mock->choice($choiceQuestion, $options);

    expect($mockResponse)->toBeString()
        ->and($mockResponse)->toBe($response);
});

it('return symfony style', function () {
    $SymfonyStyleMock = Mockery::mock(SymfonyStyle::class);

    $this->mock
        ->shouldReceive('style')
        ->once()
        ->andReturn($SymfonyStyleMock);

    $mockStyleResponse = $this->mock->style();

    expect($mockStyleResponse)->toBeInstanceOf(OutputStyle::class)
        ->and($mockStyleResponse)->toBeInstanceOf(SymfonyStyle::class);
});

afterEach(function () {
    Mockery::close();
});
