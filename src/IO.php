<?php

declare(strict_types=1);

namespace ConsoleForge;

use ConsoleForge\Contracts\IOInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnexpectedValueException;

use function Termwind\render;

readonly class IO implements IOInterface
{
    public function __construct(private SymfonyStyle $style) {}

    public function writeln(string $msg): void
    {
        $this->style->writeln($msg);
    }

    public function success(string $msg): void
    {
        $this->style->success($msg);
    }

    public function error(string $msg): void
    {
        $this->style->error($msg);
    }

    public function info(string $msg): void
    {
        $this->style->info($msg);
    }

    public function warning(string $msg): void
    {
        $this->style->warning($msg);
    }

    public function note(string $msg): void
    {
        $this->style->note($msg);
    }

    public function caution(string $msg): void
    {
        $this->style->caution($msg);
    }

    public function section(string $title): void
    {
        $this->style->section($title);
    }

    public function title(string $title): void
    {
        $this->style->title($title);
    }

    public function ask(string $question, ?string $default = null): string
    {
        $answer = $this->style->ask($question, $default);

        if ($answer === null) {
            return $default ?? '';
        }

        if (! is_string($answer)) {
            throw new UnexpectedValueException('Expected string from SymfonyStyle::ask().');
        }

        return $answer;
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->style->confirm($question, $default);
    }

    /** @param list<string> $choices */
    public function choice(string $question, array $choices, string|int|null $default = null): string
    {
        $answer = $this->style->choice($question, $choices, $default);

        if (! is_string($answer)) {
            throw new UnexpectedValueException('Expected string from SymfonyStyle::choice().');
        }

        return $answer;
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    public function table(array $headers, array $rows): void
    {
        $this->style->table($headers, $rows);
    }

    public function newLine(int $count = 1): void
    {
        $this->style->newLine($count);
    }

    public function render(string $html): void
    {
        render($html);
    }

    public function style(): SymfonyStyle
    {
        return $this->style;
    }
}
