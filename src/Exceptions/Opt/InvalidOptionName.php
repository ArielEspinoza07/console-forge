<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class InvalidOptionName extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf("Invalid option name '%s'.", $name));
    }
}
