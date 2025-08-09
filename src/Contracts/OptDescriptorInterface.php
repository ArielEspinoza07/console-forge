<?php

declare(strict_types=1);

namespace ConsoleForge\Contracts;

interface OptDescriptorInterface
{
    public function name(): string;

    public function shortcut(): ?string;

    public function description(): string;

    public function negatable(): bool;

    public function acceptValue(): bool;

    public function isArray(): bool;

    public function default(): mixed;

    public function coercer(): ?callable;
}
