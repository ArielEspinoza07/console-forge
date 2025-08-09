<?php

declare(strict_types=1);

namespace ConsoleForge;

use ConsoleForge\Bridge\SymfonyCommandMapper;
use ConsoleForge\Contracts\CommandRegistryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

final class App
{
    /**
     * Create a Symfony Console Application with sensible defaults.
     *
     * @param  CommandRegistryInterface|null  $registry  Any registry exposing `all(): array` of command descriptors.
     * @param  string|null  $name  Application name (defaults to "ConsoleForge").
     * @param  string|null  $version  Application version (optional).
     * @param  ContainerInterface|null  $container  PSR-11 container to resolve handlers (optional).
     */
    public static function make(
        ?CommandRegistryInterface $registry = null,
        ?string $name = null,
        ?string $version = null,
        ?ContainerInterface $container = null
    ): Application {
        $app = new Application($name ?? 'ConsoleForge', $version ?? '0.1.x-dev');

        if ($registry !== null) {
            $mapper = new SymfonyCommandMapper($container);
            $mapper->attach($app, $registry->all());
        }

        return $app;
    }
}
