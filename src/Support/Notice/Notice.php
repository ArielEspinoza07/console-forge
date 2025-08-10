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
        $head = $this->message;

        return $this->detail !== null ? $head.' — '.$this->detail : $head;
    }

    public function html(): string
    {
        $msg = htmlspecialchars($this->message, ENT_QUOTES, 'UTF-8');
        $det = $this->detail !== null ? htmlspecialchars($this->detail, ENT_QUOTES, 'UTF-8') : null;

        // tailwind-like classes compatible with Termwind
        $color = $this->noticeType->color();
        $border = "border-{$color}";
        $bg = "bg-{$color}-600";
        $text = "text-{$color}-600";
        $iconC = "text-{$color}-100";

        $detailHtml = $det !== null
            ? '<span class="ml-1">'.$det.'</span>'
            : '';
        $type = ucfirst($this->noticeType->value);

        return <<<HTML
<div aria-live="{$this->ariaLive}">
    <div class="px-2 py-2">
        <span><span class="mr-2">{$this->noticeType->icon()}</span><span class="{$text}"> {$type}:</span> {$msg}</span>
        {$detailHtml}
    </div>
</div>
HTML;
    }

    public function type(): NoticeType
    {
        return $this->noticeType;
    }
}
