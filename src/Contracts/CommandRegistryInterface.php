<?php

declare(strict_types=1);

namespace ConsoleForge\Contracts;

interface CommandRegistryInterface
{
    public function add(CommandDescriptorInterface $descriptor): self;

    public function get(string $name): CommandDescriptorInterface;

    /** @return iterable<CommandDescriptorInterface> */
    public function all(): iterable;

    public function has(string $name): bool;

    public function remove(string $name): void;

    public function clear(): void;
}
