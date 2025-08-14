<?php

declare(strict_types=1);

namespace ConsoleForge\Descriptors;

use Closure;
use ConsoleForge\Contracts\ArgDescriptorInterface;
use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Contracts\OptDescriptorInterface;
use ConsoleForge\Exceptions\DescriptorException;

final readonly class CommandDescriptor implements CommandDescriptorInterface
{
    /**
     * @param  list<ArgDescriptorInterface>  $args
     * @param  list<OptDescriptorInterface>  $opts
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        private string $name,
        private string $description = '',
        private array $args = [],
        private array $opts = [],
        private mixed $handler = null,
        private ?string $help = null,
        private bool $hidden = false,
        private array $extra = [],
    ) {
        $this->validate();
    }

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

    // ---- DX: “immutable” ----
    public static function make(string $name): self
    {
        return new self($name);
    }

    public function withName(string $name): self
    {
        return new self(
            name: $name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function withDescription(string $description): self
    {
        return new self(
            name: $this->name,
            description: $description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function withHelp(string $help): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function withoutHelp(): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: null,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function asHidden(bool $hidden = true): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $hidden,
            extra: $this->extra,
        );
    }

    /** @param list<ArgDescriptorInterface> $args */
    public function withArgs(array $args): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function addArg(ArgDescriptorInterface $arg): self
    {
        $args = $this->args;
        $args[] = $arg;

        return new self(
            name: $this->name,
            description: $this->description,
            args: $args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function replaceArg(ArgDescriptorInterface $arg): self
    {
        /** @var array<int, ArgDescriptorInterface> $args */
        $args = $this->args;
        $found = false;

        foreach ($args as $i => $a) {
            if ($a->name() === $arg->name()) {
                $args[$i] = $arg;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $args[] = $arg;
        }

        /** @var list<ArgDescriptorInterface> $args */
        $args = array_values($args);

        return new self(
            name: $this->name,
            description: $this->description,
            args: $args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function withoutArg(string $name): self
    {
        /** @var list<ArgDescriptorInterface> $args */
        $args = $this->args;
        foreach ($args as $i => $a) {
            if ($a->name() === $name) {
                unset($args[$i]);
                break;
            }
        }

        return new self(
            name: $this->name,
            description: $this->description,
            args: array_values($args),
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function findArg(string $name): ?ArgDescriptorInterface
    {
        foreach ($this->args as $a) {
            if ($a->name() === $name) {
                return $a;
            }
        }

        return null;
    }

    /** @param list<OptDescriptorInterface> $opts */
    public function withOpts(array $opts): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function addOpt(OptDescriptorInterface $opt): self
    {
        $opts = $this->opts;
        $opts[] = $opt;

        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function replaceOpt(OptDescriptorInterface $opt): self
    {
        /** @var array<int, OptDescriptorInterface> $opts */
        $opts = $this->opts;
        $found = false;

        foreach ($opts as $i => $o) {
            if ($o->name() === $opt->name()) {
                $opts[$i] = $opt;
                $found = true;
                break;
            }
        }

        if (! $found) {
            $opts[] = $opt;
        }

        /** @var list<OptDescriptorInterface> $opts */
        $opts = array_values($opts);

        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function withoutOpt(string $name): self
    {
        /** @var list<OptDescriptorInterface> $opts */
        $opts = $this->opts;
        foreach ($opts as $i => $o) {
            if ($o->name() === $name) {
                unset($opts[$i]);
                break;
            }
        }

        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: array_values($opts),
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    public function findOpt(string $name): ?OptDescriptorInterface
    {
        foreach ($this->opts as $o) {
            if ($o->name() === $name) {
                return $o;
            }
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $extra
     */
    public function withExtra(array $extra): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $extra,
        );
    }

    /** @param array<string,mixed> $extra */
    public function mergeExtra(array $extra): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $this->handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: array_merge($this->extra, $extra),
        );
    }

    /**
     * @param  callable|array{object|string, string}|string  $handler
     */
    public function withHandler(callable|array|string $handler): self
    {
        return new self(
            name: $this->name,
            description: $this->description,
            args: $this->args,
            opts: $this->opts,
            handler: $handler,
            help: $this->help,
            hidden: $this->hidden,
            extra: $this->extra,
        );
    }

    /**
     * @param array{
     *     name?: string,
     *     description?: string,
     *     args?: list<ArgDescriptorInterface>,
     *     opts?: list<OptDescriptorInterface>,
     *     handler?: mixed,
     *     help?: ?string,
     *     hidden?: bool,
     *     extra?: array<string,mixed>,
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: isset($data['name']) ? (string) $data['name'] : '',
            description: isset($data['description']) ? (string) $data['description'] : '',
            args: is_array($data['args'] ?? null) ? $data['args'] : [],
            opts: is_array($data['opts'] ?? null) ? $data['opts'] : [],
            handler: $data['handler'] ?? null,
            help: $data['help'] ?? null,
            hidden: filter_var($data['hidden'] ?? false, FILTER_VALIDATE_BOOL),
            extra: is_array($data['extra'] ?? null) ? $data['extra'] : [],
        );
    }

    /**
     * Explicitly revalidate if you built in steps.
     */
    public function assertValid(): self
    {
        $this->validate();

        return $this;
    }

    private function validate(): void
    {
        if ($this->name === '' || ! preg_match('/^[A-Za-z0-9:_-]+$/', $this->name)) {
            throw new DescriptorException(sprintf("Invalid command name '%s'.", $this->name));
        }

        // args: type + uniqueness + order
        $seen = [];
        $seenOptional = false;
        $seenArray = false;
        foreach ($this->args as $i => $a) {
            /** @phpstan-ignore-next-line */
            if (! $a instanceof ArgDescriptorInterface) {
                throw new DescriptorException(sprintf('Arg #%d must implement ArgDescriptorInterface.', $i));
            }
            $n = $a->name();
            if (isset($seen[$n])) {
                throw new DescriptorException(sprintf("Duplicate argument '%s'.", $n));
            }
            $seen[$n] = true;

            if ($a->isArray()) {
                if ($seenArray) {
                    throw new DescriptorException('Only one array argument is allowed and it must be last.');
                }
                if ($i !== array_key_last($this->args)) {
                    throw new DescriptorException('Array argument must be the last argument.');
                }
                $seenArray = true;
            }

            if (! $a->required()) {
                $seenOptional = true;
            } elseif ($seenOptional) {
                throw new DescriptorException('Required arguments cannot appear after optional ones.');
            }
        }

        // opts: type + uniqueness (by name) + optionally unique shortcuts
        $optNames = [];
        $shorts = [];
        foreach ($this->opts as $i => $o) {
            /** @phpstan-ignore-next-line */
            if (! $o instanceof OptDescriptorInterface) {
                throw new DescriptorException(sprintf('Opt #%d must implement OptDescriptorInterface.', $i));
            }
            $n = $o->name();
            if (isset($optNames[$n])) {
                throw new DescriptorException(sprintf("Duplicate option '--%s'.", $n));
            }
            $optNames[$n] = true;

            $sc = $o->shortcut();
            if ($sc) {
                foreach (explode('|', $sc) as $s) {
                    if ($s === '') {
                        continue;
                    }
                    $s = strtolower($s);
                    if (isset($shorts[$s])) {
                        throw new DescriptorException(sprintf("Duplicate shortcut '-%s'.", $s));
                    }
                    $shorts[$s] = true;
                }
            }
        }

        // handler: callable or class-string (invokable) or null
        if ($this->handler !== null) {
            $this->validateHandler();
        }

        // extra: string keys
        foreach ($this->extra as $k => $_) {
            /** @phpstan-ignore-next-line */
            if (! is_string($k)) {
                throw new DescriptorException('Extra keys must be strings.');
            }
        }
    }

    private function validateHandler(): void
    {
        $h = $this->handler;

        // Fail-fast by type
        if (! is_callable($h) && ! is_array($h) && ! is_string($h)) {
            throw new DescriptorException(
                sprintf(
                    'Invalid handler type: %s. Must be callable, array callable, string callable, invokable object, or invokable class-string.',
                    get_debug_type($h)
                )
            );
        }

        // Detailed validation by type

        // Closure
        if ($h instanceof Closure) {
            return;
        }

        // Invokable object
        if (is_object($h)) {
            if (! method_exists($h, '__invoke')) {
                throw new DescriptorException(
                    sprintf('Object of class %s is not invokable (missing __invoke).', $h::class)
                );
            }

            return;
        }

        // Array callable: [$object, 'method'] o ['Class', 'method']
        if (is_array($h)) {
            if (count($h) !== 2) {
                throw new DescriptorException('Array handler must have exactly 2 elements: [object|class, method].');
            }

            [$target, $method] = $h;

            if (! is_string($method) || $method === '') {
                throw new DescriptorException('Method name in array handler must be a non-empty string.');
            }

            if (is_object($target)) {
                if (! method_exists($target, $method)) {
                    throw new DescriptorException(
                        sprintf('Method %s::%s does not exist.', $target::class, $method)
                    );
                }
            } elseif (is_string($target)) {
                if (! class_exists($target)) {
                    throw new DescriptorException(sprintf('Class %s does not exist.', $target));
                }
                if (! method_exists($target, $method)) {
                    throw new DescriptorException(sprintf('Method %s::%s does not exist.', $target, $method));
                }
            } else {
                throw new DescriptorException(
                    'First element of array handler must be an object or a class-string.'
                );
            }

            if (! is_callable($h)) {
                throw new DescriptorException('Array handler is not callable.');
            }

            return;
        }

        // String callable
        if (is_string($h)) {
            // global function
            if (function_exists($h)) {
                return;
            }

            // Static method: "Class::method"
            if (str_contains($h, '::')) {
                [$class, $method] = explode('::', $h, 2);
                if (! class_exists($class)) {
                    throw new DescriptorException(sprintf('Class %s does not exist.', $class));
                }
                if (! method_exists($class, $method)) {
                    throw new DescriptorException(sprintf('Method %s::%s does not exist.', $class, $method));
                }

                return;
            }

            // Class-string invocable
            if (class_exists($h) && method_exists($h, '__invoke')) {
                return;
            }

            throw new DescriptorException(
                sprintf("String handler '%s' is not a valid function, method, or invokable class-string.", $h)
            );
        }
    }
}
