<?php

declare(strict_types=1);

namespace ConsoleForge\Support;

use ConsoleForge\Contracts\CommandDescriptorInterface;
use ConsoleForge\Registry\InMemoryCommandRegistry;
use LogicException;
use Traversable;

final class ConfigLoader
{
    /**
     * @return iterable<CommandDescriptorInterface>
     */
    public static function normalize(mixed $ret): iterable
    {
        // Single descriptor
        if ($ret instanceof CommandDescriptorInterface) {
            yield $ret;

            return;
        }

        // Traversable (we still validate each element)
        if ($ret instanceof Traversable) {
            foreach ($ret as $item) {
                if (! $item instanceof CommandDescriptorInterface) {
                    throw new LogicException('Config iterable must yield CommandDescriptorInterface instances.');
                }
                yield $item;
            }

            return;
        }

        // Array (either direct list or ['commands' => iterable])
        if (is_array($ret)) {
            $iterable = $ret;
            if (array_key_exists('commands', $ret)) {
                if (! is_iterable($ret['commands'])) {
                    throw new LogicException('Config "commands" key must be iterable.');
                }
                $iterable = $ret['commands'];
            }

            foreach ($iterable as $item) {
                if (! $item instanceof CommandDescriptorInterface) {
                    throw new LogicException('Config array must contain only CommandDescriptorInterface instances.');
                }
                yield $item;
            }

            return;
        }

        throw new LogicException('Config must return iterable, ["commands"=>iterable], or a single descriptor.');
    }

    public static function loadFile(string $path, InMemoryCommandRegistry $registry): void
    {
        /** @var mixed $ret */
        $ret = require $path;

        foreach (self::normalize($ret) as $cmd) {
            $registry->add($cmd);
        }
    }

    public static function loadProjectConfigs(InMemoryCommandRegistry $registry, string $cwd): void
    {
        $single = $cwd.'/config/console-forge.php';
        if (is_file($single)) {
            self::loadFile($single, $registry);
        }

        $dir = $cwd.'/config/console-forge';
        if (is_dir($dir)) {
            foreach (glob($dir.'/*.php') ?: [] as $file) {
                self::loadFile($file, $registry);
            }
        }
    }
}
