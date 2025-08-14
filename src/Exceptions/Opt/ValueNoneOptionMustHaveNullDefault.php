<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class ValueNoneOptionMustHaveNullDefault extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf(
            "Option '--%s' does not accept a value; default must be null.",
            $name
        ));
    }
}
