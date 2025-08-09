<?php

declare(strict_types=1);

namespace ConsoleForge\Descriptors;

use Closure;
use ConsoleForge\Contracts\ArgDescriptorInterface;
use LogicException;

/**
 * Immutable value-object for command arguments.
 */
final readonly class ArgDescriptor implements ArgDescriptorInterface
{
    private string $name;

    private string $description;

    private bool $required;

    private bool $isArray;

    private mixed $default;

    private ?Closure $coercer; // store as Closure|null (native type required for readonly)

    /**
     * @param  null|callable(mixed):mixed  $coercer
     */
    public function __construct(
        string $name,
        string $description = '',
        bool $required = false,
        bool $isArray = false,
        mixed $default = null,
        ?callable $coercer = null,
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->required = $required;
        $this->isArray = $isArray;
        $this->default = $default;
        $this->coercer = $coercer === null
            ? null
            : ($coercer instanceof Closure ? $coercer : Closure::fromCallable($coercer));

        if ($this->name === '') {
            throw new LogicException('Argument name cannot be empty.');
        }

        // REQUIRED arg cannot have default
        if ($this->required && $this->default !== null) {
            throw new LogicException(
                sprintf("Argument '%s' is REQUIRED and cannot have a default value.", $this->name)
            );
        }

        // Array arg → default must be array or null
        if ($this->isArray && $this->default !== null && ! is_array($this->default)) {
            throw new LogicException(
                sprintf("Argument '%s' is array; default value must be array or null.", $this->name)
            );
        }

        // Non-array arg → default must not be array
        if (! $this->isArray && is_array($this->default)) {
            throw new LogicException(
                sprintf("Argument '%s' is not array; default value cannot be an array.", $this->name)
            );
        }

        // Simple name guard
        if (! preg_match('/^[a-zA-Z0-9:_-]+$/', $this->name)) {
            throw new LogicException(sprintf("Invalid argument name '%s'.", $this->name));
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function required(): bool
    {
        return $this->required;
    }

    public function isArray(): bool
    {
        return $this->isArray;
    }

    public function default(): mixed
    {
        return $this->default;
    }

    /** @return null|callable(mixed):mixed */
    public function coercer(): ?callable
    {
        return $this->coercer;
    }

    // ---------- DX helpers (factories) ----------

    public static function withRequired(string $name, string $description = ''): self
    {
        return new self(name: $name, description: $description, required: true);
    }

    public static function optional(string $name, string $description = '', mixed $default = null): self
    {
        return new self(name: $name, description: $description, default: $default);
    }

    /**
     * Array argument. If required=false and no default provided, default is [] (nicer DX).
     *
     * @param  list<mixed>|null  $default
     */
    public static function arrayArg(
        string $name,
        string $description = '',
        bool $required = false,
        ?array $default = null
    ): self {
        return new self(
            name: $name,
            description: $description,
            required: $required,
            isArray: true,
            default: $required ? null : ($default ?? []),
        );
    }

    /**
     * @param  callable(mixed):mixed  $coercer
     */
    public function withCoercer(callable $coercer): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            required: $this->required,
            isArray: $this->isArray,
            default: $this->default,
            coercer: $coercer
        );
    }
}
