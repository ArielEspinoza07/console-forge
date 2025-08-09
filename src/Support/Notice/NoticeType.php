<?php

declare(strict_types=1);

namespace ConsoleForge\Support\Notice;

enum NoticeType: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';

    public function ariaLive(): string
    {
        return match ($this) {
            self::ERROR, self::WARNING => 'assertive',
            self::SUCCESS, self::INFO => 'polite',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SUCCESS => 'green',
            self::ERROR => 'red',
            self::WARNING => 'yellow',
            self::INFO => 'blue',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::SUCCESS => '✅',
            self::ERROR => '❌',
            self::WARNING => '⚠️',
            self::INFO => 'ℹ️',
        };
    }
}
