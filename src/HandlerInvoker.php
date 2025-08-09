<?php

declare(strict_types=1);

namespace ConsoleForge;

use BackedEnum;
use Closure;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnitEnum;

final class HandlerInvoker
{
    public function __construct(private ?ContainerInterface $container = null) {}

    public function __invoke(
        mixed $handler,
        InputInterface $input,
        OutputInterface $output
    ): int {
        $callable = $this->resolveCallable($handler);
        $params = $this->mapParameters($callable, $input, $output);

        $result = $callable(...$params);

        return is_int($result) ? $result : 0;
    }

    private function resolveCallable(mixed $handler): Closure
    {
        // Class name -> build instance (from container if present) and use __invoke
        if (is_string($handler) && class_exists($handler)) {
            $instance = $this->container?->get($handler) ?? new $handler;
            if (! is_callable($instance)) {
                throw new InvalidArgumentException("Class $handler is not invokable.");
            }

            return Closure::fromCallable($instance);
        }

        // 'Class::method'
        if (is_string($handler) && str_contains($handler, '::')) {
            [$class, $method] = explode('::', $handler, 2);
            $obj = $this->container?->get($class) ?? new $class;
            $cb = [$obj, $method];
            if (! is_callable($cb)) {
                throw new InvalidArgumentException("Handler [$class::$method] is not callable.");
            }

            return Closure::fromCallable($cb);
        }

        // [$objectOrClass, 'method']
        if (is_array($handler)) {
            [$obj, $method] = $handler + [null, null];

            if (! is_string($method)) {
                throw new InvalidArgumentException('Array callable must be [object|class-string, method-string].');
            }

            if (is_string($obj)) {
                // class-string -> build instance (DI if present)
                $obj = $this->container?->get($obj) ?? new $obj;
            }

            $cb = [$obj, $method];
            if (! is_callable($cb)) {
                $t = is_object($obj) ? $obj::class : get_debug_type($obj);
                throw new InvalidArgumentException("Handler [$t::$method] is not callable.");
            }

            return Closure::fromCallable($cb);
        }

        // Closure / function / invokable object
        if (is_callable($handler)) {
            return Closure::fromCallable($handler);
        }

        throw new InvalidArgumentException('Invalid Handler');
    }

    /**
     * @return list<mixed>
     */
    private function mapParameters(Closure $callable, InputInterface $input, OutputInterface $output): array
    {
        $ref = new ReflectionFunction($callable);
        $style = new SymfonyStyle($input, $output);
        $io = new IO($style);

        $params = [];
        foreach ($ref->getParameters() as $p) {
            // Type-based injections
            $injected = $this->tryFrameworkInjection($p, $input, $output, $style, $io);
            if ($injected['handled']) {
                $params[] = $injected['value'];

                continue;
            }

            // Try by parameter name, snake_case, kebab-case
            $name = $p->getName();
            $candidates = [$name, $this->toSnake($name), $this->toKebab($name)];

            $value = null;
            $found = false;

            foreach ($candidates as $candidate) {
                if ($input->hasArgument($candidate)) {
                    $value = $input->getArgument($candidate);
                    $found = true;
                    break;
                }
                if ($input->hasOption($candidate)) {
                    $value = $input->getOption($candidate);
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $value = $p->isDefaultValueAvailable() ? $p->getDefaultValue() : null;
            }

            // Variadics: ensure array
            if ($p->isVariadic()) {
                $value = is_array($value) ? $value : ($value === null ? [] : [$value]);
            }

            $params[] = $this->coerceParam($p, $value);
        }

        /** @var list<mixed> $params */
        return $params;
    }

    /**
     * @return array{handled: bool, value: mixed}
     */
    private function tryFrameworkInjection(
        \ReflectionParameter $p,
        InputInterface $input,
        OutputInterface $output,
        SymfonyStyle $style,
        IO $io
    ): array {
        $type = $p->getType();
        if (! $type instanceof ReflectionNamedType) {
            return ['handled' => false, 'value' => null];
        }

        return match ($type->getName()) {
            IO::class => ['handled' => true, 'value' => $io],
            SymfonyStyle::class => ['handled' => true, 'value' => $style],
            InputInterface::class => ['handled' => true, 'value' => $input],
            OutputInterface::class => ['handled' => true, 'value' => $output],
            default => ['handled' => false, 'value' => null],
        };
    }

    private function coerceParam(ReflectionParameter $p, mixed $value): mixed
    {
        [$typeName, $nullable] = $this->resolveType($p);

        if ($value === null) {
            return $nullable ? null : $value;
        }

        if ($typeName === null) {
            // No declared type => pass-through
            return $value;
        }

        // Built-in scalars/array
        switch ($typeName) {
            case 'bool':
                $bool = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

                return $bool ?? false;

            case 'int':
                if (is_int($value)) {
                    return $value;
                }
                if (is_numeric($value)) {
                    return (int) $value;
                }
                if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
                    return (int) $value;
                }
                throw new InvalidArgumentException("Cannot cast \${$p->getName()} to int.");
            case 'float':
                if (is_float($value) || is_int($value)) {
                    return (float) $value;
                }
                if (is_numeric($value)) {
                    return (float) $value;
                }
                throw new InvalidArgumentException("Cannot cast \${$p->getName()} to float.");
            case 'string':
                if (is_string($value)) {
                    return $value;
                }
                if (is_scalar($value)) {
                    return (string) $value;
                }
                if (is_object($value) && method_exists($value, '__toString')) {
                    return (string) $value;
                }
                throw new InvalidArgumentException("Cannot cast \${$p->getName()} to string.");
            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    return $value === '' ? [] : explode(',', $value);
                }

                // At this point it's neither array nor string; make a single-item array.
                return [$value];

        }

        // Enums
        if (enum_exists($typeName)) {
            /** @var class-string<UnitEnum> $typeName */
            if (is_subclass_of($typeName, BackedEnum::class)) {
                /** @var class-string<BackedEnum> $typeName */
                $candidate = $this->normalizeBackedEnumCandidate($value);
                if ($candidate === null) {
                    if ($nullable) {
                        return null;
                    }
                    throw new InvalidArgumentException("Invalid value for enum {$typeName} on \${$p->getName()}.");
                }
                $backed = $typeName::tryFrom($candidate);
                if ($backed !== null) {
                    return $backed;
                }
                if ($nullable) {
                    return null;
                }
                throw new InvalidArgumentException("Invalid value for enum {$typeName} on \${$p->getName()}.");
            }

            // UnitEnum: accept case name as string
            foreach ($typeName::cases() as $case) {
                if (is_string($value) && $value === $case->name) {
                    return $case;
                }
            }
            if ($nullable) {
                return null;
            }
            throw new InvalidArgumentException("Invalid enum case for {$typeName} on \${$p->getName()}.");
        }

        // Classes/interfaces: pass-through
        return $value;
    }

    /**
     * Normalize a candidate value for BackedEnum::tryFrom() into int|string or null.
     */
    private function normalizeBackedEnumCandidate(mixed $value): int|string|null
    {
        // Already acceptable types
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value)) {
            // If it's a numeric string with only digits (optional leading minus), cast to int
            if (preg_match('/^-?\d+$/', $value) === 1) {
                return (int) $value;
            }

            // Otherwise keep as string
            return $value;
        }

        // Convert numeric scalars (floats) to string or int as appropriate
        if (is_float($value)) {
            // Convert numeric value to string or int as appropriate
            $str = (string) $value;

            return str_contains($str, '.') ? $str : (int) $value;
        }

        // Anything else cannot be normalized for a BackedEnum
        return null;
    }

    /**
     * @return array{0: ?string, 1: bool} [typeName, nullable]
     */
    private function resolveType(ReflectionParameter $p): array
    {
        $t = $p->getType();
        if ($t === null) {
            return [null, true];
        }

        if ($t instanceof ReflectionNamedType) {
            return [$t->getName(), $t->allowsNull()];
        }

        if ($t instanceof ReflectionUnionType) {
            $names = [];
            $nullable = false;
            foreach ($t->getTypes() as $n) {
                if ($n instanceof ReflectionNamedType) {
                    $nName = $n->getName();
                    $names[] = $nName;
                    $nullable = $nullable || $n->allowsNull() || $nName === 'null';
                }
            }
            $nonNulls = array_values(array_filter($names, static fn ($n) => $n !== 'null'));
            $chosen = $nonNulls[0] ?? null;

            return [$chosen, $nullable];
        }

        return [null, true];
    }

    private function toSnake(string $s): string
    {
        $out = preg_replace('/[A-Z]/', '_$0', $s);

        return strtolower($out ?? $s);
    }

    private function toKebab(string $s): string
    {
        return str_replace('_', '-', $this->toSnake($s));
    }
}
