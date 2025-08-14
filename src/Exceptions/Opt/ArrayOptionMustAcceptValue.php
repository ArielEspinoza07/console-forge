<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class ArrayOptionMustAcceptValue extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf(
            "Option '--%s': array options must accept values.",
            $name
        ));
    }
}
