<?php

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class NonArrayOptionDefaultIsArray extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf(
            "Option '--%s' is not array; default value cannot be an array.",
            $name
        ));
    }
}
