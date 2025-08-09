<?php

declare(strict_types=1);

namespace ConsoleForge;

use Symfony\Component\Console\Style\SymfonyStyle;

interface IOInterface
{
    public function writeln(string $msg): void;

    public function success(string $msg): void;

    public function error(string $msg): void;

    public function info(string $msg): void;

    public function warning(string $msg): void;

    public function note(string $msg): void;

    public function caution(string $msg): void;

    public function section(string $title): void;

    public function title(string $title): void;

    public function ask(string $question, ?string $default = null): string;

    public function confirm(string $question, bool $default = false): bool;

    /** @param list<string> $choices */
    public function choice(string $question, array $choices, string|int|null $default = null): string;

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    public function table(array $headers, array $rows): void;

    public function newLine(int $count = 1): void;

    /** Termwind-first output */
    public function render(string $html): void;

    /** Escape hatch */
    public function style(): SymfonyStyle;
}
