<?php

declare(strict_types=1);

namespace ConsoleForge\Exceptions\Opt;

use ConsoleForge\Exceptions\DescriptorException;

final class InvalidOptionShortcut extends DescriptorException
{
    public static function for(string $shortcut): self
    {
        return new self(sprintf(
            "Invalid shortcut '%s'. Use letters, optionally separated by '|', e.g. 'a' or 'a|b'.",
            $shortcut
        ));
    }
}
