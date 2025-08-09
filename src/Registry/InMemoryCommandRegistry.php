<?php

declare(strict_types=1);

namespace ConsoleForge\Registry;

use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\CommandRegistryInterface;
use InvalidArgumentException;

final class InMemoryCommandRegistry implements CommandRegistryInterface
{
    /**
     * @var array<string, CommandDescriptorInterface>
     */
    private array $commands = [];

    public function add(CommandDescriptorInterface $descriptor): CommandRegistryInterface
    {
        $name = $descriptor->name();

        if (isset($this->commands[$name])) {
            throw new InvalidArgumentException(
                sprintf("A command with the name '%s' is already registered.", $name)
            );
        }

        $this->commands[$name] = $descriptor;

        return $this;
    }

    public function get(string $name): CommandDescriptorInterface
    {
        if (! isset($this->commands[$name])) {
            throw new InvalidArgumentException(
                sprintf("No command registered with the name '%s'.", $name)
            );
        }

        return $this->commands[$name];
    }

    /**
     * @return iterable<CommandDescriptorInterface>
     */
    public function all(): iterable
    {
        return array_values($this->commands);
    }

    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    public function remove(string $name): void
    {
        unset($this->commands[$name]);
    }

    public function clear(): void
    {
        $this->commands = [];
    }
}
