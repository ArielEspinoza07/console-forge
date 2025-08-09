<?php

declare(strict_types=1);

namespace ConsoleForge\Contracts;

interface ArgDescriptorInterface
{
    public function name(): string;

    public function description(): string;

    public function required(): bool;

    public function isArray(): bool;

    public function default(): mixed;

    public function coercer(): ?callable;
}
