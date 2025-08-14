<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Arg;

use ConsoleForge\Exceptions\DescriptorException;

final class InvalidArgumentName extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf("Invalid argument name '%s'.", $name));
    }
}
