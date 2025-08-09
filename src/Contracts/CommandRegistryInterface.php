<?php

declare(strict_types=1);

namespace ConsoleForge;

interface CommandRegistryInterface
{
    public function add(CommandDescriptorInterface $descriptor): self;

    /** @return CommandDescriptorInterface[] */
    public function all(): array;
}
