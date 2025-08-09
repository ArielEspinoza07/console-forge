<?php

declare(strict_types=1);

namespace ConsoleForge;

interface CommandDescriptorInterface
{
    public function name(): string;

    public function description(): string;

    /** @return ArgDescriptorInterface[] */
    public function args(): array;

    /** @return OptDescriptorInterface[] */
    public function opts(): array;

    public function handler(): mixed;

    public function help(): ?string;

    public function hidden(): bool;

    /**
     * @return array<string, mixed>
     */
    public function extra(): array;
}
