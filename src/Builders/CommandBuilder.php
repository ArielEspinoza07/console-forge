<?php

declare(strict_types=1);

namespace ConsoleForge\Builders;

use ConsoleForge\Contracts\ArgDescriptorInterface;
use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\OptDescriptorInterface;
use ConsoleForge\Descriptors\CommandDescriptor;
use LogicException;

final class CommandBuilder
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

    public static function make(string $name): self
    {
        if ($name === '' || ! preg_match('/^[A-Za-z0-9:_-]+$/', $name)) {
            throw new LogicException(sprintf("Invalid command name '%s'.", $name));
        }

        $s = new self;
        $s->name = $name;

        return $s;
    }

    public function description(string $d): self
    {
        $this->description = $d;

        return $this;
    }

    public function arg(ArgDescriptorInterface $a): self
    {
        $this->args[] = $a;

        return $this;
    }

    public function args(ArgDescriptorInterface ...$a): self
    {
        array_push($this->args, ...$a);

        return $this;
    }

    /** @param list<ArgDescriptorInterface> $a */
    public function setArgs(array $a): self
    {
        $this->args = $a;

        return $this;
    }

    public function opt(OptDescriptorInterface $o): self
    {
        $this->opts[] = $o;

        return $this;
    }

    public function opts(OptDescriptorInterface ...$o): self
    {
        array_push($this->opts, ...$o);

        return $this;
    }

    /** @param list<OptDescriptorInterface> $o */
    public function setOpts(array $o): self
    {
        $this->opts = $o;

        return $this;
    }

    /**
     * @param  callable|array{object|string, string}|string  $h
     * @return $this
     */
    public function handler(callable|array|string $h): self
    {
        $this->handler = $h;

        return $this;
    }

    public function help(?string $h): self
    {
        $this->help = $h;

        return $this;
    }

    public function hidden(bool $h = true): self
    {
        $this->hidden = $h;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $e
     * @return $this
     */
    public function extra(array $e): self
    {
        $this->extra = $e;

        return $this;
    }

    /** @param array<string,mixed> $e */
    public function mergeExtra(array $e): self
    {
        $this->extra = array_merge($this->extra, $e);

        return $this;
    }

    /**
     * Builds and validates. May throw ConsoleForge\Exceptions\DescriptorException.
     *
     * @return CommandDescriptorInterface
     */
    public function build(): CommandDescriptorInterface
    {
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
