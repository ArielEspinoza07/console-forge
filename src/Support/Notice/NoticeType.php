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
            self::SUCCESS => '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 0 1 0 1.414l-7.5 7.5a1 1 0 0 1-1.414 0l-3-3a1 1 0 1 1 1.414-1.414L8.5 12.086l6.793-6.793a1 1 0 0 1 1.414 0z"/></svg>',
            self::ERROR => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5a1 1 0 1 1 2 0 1 1 0 0 1-2 0zm0-7h2v5h-2V6z" clip-rule="evenodd"/></svg>',
            self::WARNING => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.597c.75 1.336-.213 3.004-1.742 3.004H3.48c-1.53 0-2.492-1.668-1.743-3.004L8.257 3.1zM11 14a1 1 0 11-2 0 1 1 0 012 0zm-1-2a1 1 0 01-1-1V8a1 1 0 112 0v3a1 1 0 01-1 1z" clip-rule="evenodd"/></svg>',
            self::INFO => '<svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0zM9 9h2v6H9V9zm0-4h2v2H9V5z" clip-rule="evenodd"/></svg>',
        };
    }
}
