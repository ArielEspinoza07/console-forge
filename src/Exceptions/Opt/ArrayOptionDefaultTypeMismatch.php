<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class ArrayOptionDefaultTypeMismatch extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf(
            "Option '--%s' is array; default value must be array or null.",
            $name
        ));
    }
}
