<?php

declare(strict_types=1);

namespace ConsoleForge\Support\Notice;

use ConsoleForge\Contracts\IOInterface;

interface NoticeRendererInterface
{
    public function render(Notice $notice, IOInterface $io): void;
}
