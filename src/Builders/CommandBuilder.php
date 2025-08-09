<?php

declare(strict_types=1);

namespace ConsoleForge\Builders;

use ConsoleForge\Contracts\ArgDescriptorInterface;
use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\OptDescriptorInterface;
use ConsoleForge\Descriptors\CommandDescriptor;
use LogicException;

/**
 * @phpstan-consistent-constructor
 */
class CommandBuilder
{
    private string $name;

    private string $description = '';

    /** @var list<ArgDescriptorInterface> */
    private array $args = [];

    /** @var list<OptDescriptorInterface> */
    private array $opts = [];

    private mixed $handler = null;

    private ?string $help = null;

    private bool $hidden = false;

    /**
     * @var array<string, mixed>
     */
    private array $extra = [];

    protected function __construct() {}

    public static function make(string $name): static
    {
        $s = new static;
        $s->name = $name;

        return $s;
    }

    public function desc(string $d): static
    {
        $this->description = $d;

        return $this;
    }

    public function arg(ArgDescriptorInterface $a): static
    {
        $this->args[] = $a;

        return $this;
    }

    public function args(ArgDescriptorInterface ...$a): static
    {
        array_push($this->args, ...$a);

        return $this;
    }

    public function opt(OptDescriptorInterface $o): static
    {
        $this->opts[] = $o;

        return $this;
    }

    public function opts(OptDescriptorInterface ...$o): static
    {
        array_push($this->opts, ...$o);

        return $this;
    }

    public function handler(mixed $h): static
    {
        $this->handler = $h;

        return $this;
    }

    public function help(?string $h): static
    {
        $this->help = $h;

        return $this;
    }

    public function hidden(bool $h = true): static
    {
        $this->hidden = $h;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $e
     * @return $this
     */
    public function extra(array $e): static
    {
        $this->extra = $e;

        return $this;
    }

    public function build(): CommandDescriptorInterface
    {
        if ($this->name === '') {
            throw new LogicException('Command name cannot be empty. Use CommandBuilder::make("name").');
        }

        return new CommandDescriptor(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra
        );
    }
}
