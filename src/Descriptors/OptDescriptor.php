<?php

declare(strict_types=1);

namespace ConsoleForge\Descriptors;

use Closure;
use ConsoleForge\Contracts\OptDescriptorInterface;
use ConsoleForge\Exceptions\Opt\ArrayOptionDefaultTypeMismatch;
use ConsoleForge\Exceptions\Opt\ArrayOptionMustAcceptValue;
use ConsoleForge\Exceptions\Opt\InvalidOptionName;
use ConsoleForge\Exceptions\Opt\InvalidOptionShortcut;
use ConsoleForge\Exceptions\Opt\NegatableOptionCannotAcceptValue;
use ConsoleForge\Exceptions\Opt\NegatableOptionCannotBeArray;
use ConsoleForge\Exceptions\Opt\NonArrayOptionDefaultIsArray;
use ConsoleForge\Exceptions\Opt\ValueNoneOptionMustHaveNullDefault;

/**
 * Immutable value-object for command options.
 */
final readonly class OptDescriptor implements OptDescriptorInterface
{
    private string $name;

    private ?string $shortcut;

    private string $description;

    private bool $negatable;

    private bool $acceptValue;

    private bool $isArray;

    private mixed $default;

    private ?Closure $coercer; // store as Closure|null (native type required for readonly)

    /**
     * @param  mixed  $default  For array options, use list<mixed> or null.
     * @param  null|callable(mixed):mixed  $coercer
     */
    public function __construct(
        string $name,
        ?string $shortcut = null,
        string $description = '',
        bool $negatable = false,
        bool $acceptValue = false,
        bool $isArray = false,
        mixed $default = null,
        ?callable $coercer = null,
    ) {
        $this->name = $name;
        $this->shortcut = $shortcut === '' ? null : $shortcut;
        $this->description = $description;
        $this->negatable = $negatable;
        $this->acceptValue = $acceptValue;
        $this->isArray = $isArray;
        $this->default = $default;
        $this->coercer = $coercer === null
            ? null
            : ($coercer instanceof Closure ? $coercer : Closure::fromCallable($coercer));

        // ---- Validations (Symfony semantics) ----

        if ($this->name === '' || ! preg_match('/^[a-zA-Z0-9:_-]+$/', $this->name)) {
            throw InvalidOptionName::for($this->name);
        }

        // Shortcut may be null, single char "a", or multi "a|b|c"
        if ($this->shortcut !== null) {
            if (! preg_match('/^[A-Za-z](\|[A-Za-z])*$/', $this->shortcut)) {
                throw InvalidOptionShortcut::for($this->name);
            }
        }

        // Negatable options cannot accept values nor be arrays
        if ($this->negatable && $this->acceptValue) {
            throw NegatableOptionCannotAcceptValue::for($this->name);
        }
        if ($this->negatable && $this->isArray) {
            throw NegatableOptionCannotBeArray::for($this->name);
        }

        // Array options must accept values
        if ($this->isArray && ! $this->acceptValue) {
            throw ArrayOptionMustAcceptValue::for($this->name);
        }

        // VALUE_NONE (no value): default must be null
        if (! $this->acceptValue && $this->default !== null) {
            throw ValueNoneOptionMustHaveNullDefault::for($this->name);
        }

        // Array options: default must be array or null
        if ($this->isArray && $this->default !== null && ! is_array($this->default)) {
            throw ArrayOptionDefaultTypeMismatch::for($this->name);
        }

        // Non-array options: default must not be array
        if (! $this->isArray && is_array($this->default)) {
            throw NonArrayOptionDefaultIsArray::for($this->name);
        }
    }

    // ---- Interface implementation ----

    public function name(): string
    {
        return $this->name;
    }

    public function shortcut(): ?string
    {
        return $this->shortcut;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function negatable(): bool
    {
        return $this->negatable;
    }

    public function acceptValue(): bool
    {
        return $this->acceptValue;
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

    // ---- DX helpers (factories) ----

    /**
     * Flag option (no value). Example: --force
     */
    public static function flag(
        string $name,
        ?string $shortcut = null,
        string $description = ''
    ): self {
        return new self(
            name: $name,
            shortcut: $shortcut,
            description: $description,
            negatable: false,
            acceptValue: false,
            isArray: false,
            default: null,
        );
    }

    /**
     * Negatable flag. Example: --ansi / --no-ansi
     */
    public static function negatableFlag(
        string $name,
        ?string $shortcut = null,
        string $description = ''
    ): self {
        return new self(
            name: $name,
            shortcut: $shortcut,
            description: $description,
            negatable: true,
            acceptValue: false,
            isArray: false,
            default: null,
        );
    }

    /**
     * Single-value option. Example: --env=prod
     */
    public static function value(
        string $name,
        ?string $shortcut = null,
        string $description = '',
        mixed $default = null
    ): self {
        return new self(
            name: $name,
            shortcut: $shortcut,
            description: $description,
            negatable: false,
            acceptValue: true,
            isArray: false,
            default: $default,
        );
    }

    /**
     * Multi-value option (array). Example: --path=one --path=two
     *
     * @param  list<mixed>|null  $default
     */
    public static function values(
        string $name,
        ?string $shortcut = null,
        string $description = '',
        ?array $default = null
    ): self {
        return new self(
            name: $name,
            shortcut: $shortcut,
            description: $description,
            negatable: false,
            acceptValue: true,
            isArray: true,
            default: $default ?? [],
        );
    }

    /**
     * @param  callable(mixed):mixed  $coercer
     */
    public function withCoercer(callable $coercer): self
    {
        return new self(
            name: $this->name,
            shortcut: $this->shortcut,
            description: $this->description,
            negatable: $this->negatable,
            acceptValue: $this->acceptValue,
            isArray: $this->isArray,
            default: $this->default,
            coercer: $coercer
        );
    }
}
