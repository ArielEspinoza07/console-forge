<?php

declare(strict_types=1);

namespace ConsoleForge\Support\Notice;

final readonly class Notice
{
    private function __construct(
        private NoticeType $noticeType,
        private string $message,
        private ?string $detail,
        private string $ariaLive = 'polite',
    ) {}

    public static function create(NoticeType $type, string $message, ?string $detail = null): self
    {
        return new self(
            noticeType: $type,
            message: $message,
            detail: $detail,
            ariaLive: $type->ariaLive(),
        );
    }

    public static function success(string $message, ?string $detail = null): self
    {
        return self::create(NoticeType::SUCCESS, $message, $detail);
    }

    public static function error(string $message, ?string $detail = null): self
    {
        return self::create(NoticeType::ERROR, $message, $detail);
    }

    public static function warning(string $message, ?string $detail = null): self
    {
        return self::create(NoticeType::WARNING, $message, $detail);
    }

    public static function info(string $message, ?string $detail = null): self
    {
        return self::create(NoticeType::INFO, $message, $detail);
    }

    public function rawString(): string
    {
        $head = ucfirst($this->noticeType->value).': '.$this->message;

        return $this->detail !== null ? $head.' — '.$this->detail : $head;
    }

    public function html(): string
    {
        $msg = htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8');
        $det = $this->detail !== null ? htmlspecialchars($this->detail, ENT_QUOTES, 'UTF-8') : null;

        // tailwind-like classes compatible with Termwind
        $color = $this->noticeType->color();
        $border = "border border-{$color}-200";
        $bg = "bg-{$color}-50";
        $text = "text-{$color}-800";
        $iconC = "text-{$color}-600";

        $detailHtml = $det !== null
            ? "<div class=\"text-xs opacity-80 mt-1\">{$det}</div>"
            : '';

        return <<<HTML
<div role="alert" aria-live="{$this->ariaLive}" class="flex items-center justify-between p-4 mb-4 rounded-lg {$bg} {$text} {$border}">
  <div class="flex items-center">
    <span class="w-5 h-5 mr-2 {$iconC}">{$this->noticeType->icon()}</span>
    <div class="text-sm font-medium">{$msg}{$detailHtml}</div>
  </div>
</div>
HTML;
    }

    public function type(): NoticeType
    {
        return $this->noticeType;
    }
}
