<?php

declare(strict_types=1);

namespace ConsoleForge\Support\Notice;

use ConsoleForge\Contracts\IOInterface;

final class TermwindNoticeRenderer implements NoticeRendererInterface
{
    public function render(Notice $notice, IOInterface $io): void
    {
        $io->render($notice->html());
    }
}
