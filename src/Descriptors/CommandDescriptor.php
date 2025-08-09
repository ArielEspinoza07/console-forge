<?php

declare(strict_types=1);

namespace ConsoleForge\Descriptors;

use ConsoleForge\Contracts\ArgDescriptorInterface;
use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\OptDescriptorInterface;

class CommandDescriptor implements CommandDescriptorInterface
{
    /**
     * @param  list<ArgDescriptorInterface>  $args
     * @param  list<OptDescriptorInterface>  $opts
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        protected string $name,
        protected string $description = '',
        protected array $args = [],
        protected array $opts = [],
        protected mixed $handler = null,
        protected ?string $help = null,
        protected bool $hidden = false,
        protected array $extra = [],
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function args(): array
    {
        return $this->args;
    }

    public function opts(): array
    {
        return $this->opts;
    }

    public function handler(): mixed
    {
        return $this->handler;
    }

    public function help(): ?string
    {
        return $this->help;
    }

    public function hidden(): bool
    {
        return $this->hidden;
    }

    public function extra(): array
    {
        return $this->extra;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return $this
     */
    public function withExtra(array $extra): static
    {
        $clone = clone $this;
        $clone->extra = $extra;

        return $clone;
    }
}
