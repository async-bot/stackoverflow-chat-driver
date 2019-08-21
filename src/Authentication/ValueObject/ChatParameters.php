<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class ChatParameters
{
    private string $webSocketUrl;

    private string $fkey;

    public function __construct(string $webSocketUrl, string $fkey)
    {
        $this->webSocketUrl = $webSocketUrl;
        $this->fkey         = $fkey;
    }

    public function getWebSocketUrl(): string
    {
        return $this->webSocketUrl;
    }

    public function getFkey(): string
    {
        return $this->fkey;
    }
}
