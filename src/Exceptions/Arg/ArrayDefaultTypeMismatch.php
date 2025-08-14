<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Arg;

use ConsoleForge\Exceptions\DescriptorException;

final class ArrayDefaultTypeMismatch extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf("Argument '%s' is array; default value must be array or null.", $name));
    }
}
