<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Arg;

use ConsoleForge\Exceptions\DescriptorException;

final class NonArrayDefaultIsArray extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf("Argument '%s' is not array; default value cannot be an array.", $name));
    }
}
