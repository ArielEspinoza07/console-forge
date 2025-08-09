<?php

declare(strict_types=1);

namespace ConsoleForge\Support\Notice;

use ConsoleForge\Contracts\IOInterface;

final class SymfonyStyleNoticeRenderer implements NoticeRendererInterface
{
    public function render(Notice $notice, IOInterface $io): void
    {
        $msg = $notice->rawString();

        match ($notice->type()) {
            NoticeType::SUCCESS => $io->success($msg),
            NoticeType::ERROR => $io->error($msg),
            NoticeType::WARNING => $io->warning($msg),
            NoticeType::INFO => $io->info($msg),
        };
    }
}
