<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Arg;

use ConsoleForge\Exceptions\DescriptorException;

final class RequiredArgHasDefault extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf("Argument '%s' is REQUIRED and cannot have a default value.", $name));
    }
}
