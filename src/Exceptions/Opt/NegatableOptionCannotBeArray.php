<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class NegatableOptionCannotBeArray extends DescriptorException
{
    public static function for(string $name): self
    {
        return new self(sprintf(
            "Option '--%s': negatable options cannot be arrays.",
            $name
        ));
    }
}
