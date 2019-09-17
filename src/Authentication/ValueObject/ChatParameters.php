<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class ChatParameters
{
    private string $webSocketUrl;

    private string $fKey;

    private ChatUser $user;

    public function __construct(string $webSocketUrl, string $fKey, ChatUser $user)
    {
        $this->webSocketUrl = $webSocketUrl;
        $this->fKey         = $fKey;
        $this->user         = $user;
    }

    public function getWebSocketUrl(): string
    {
        return $this->webSocketUrl;
    }

    public function getFKey(): string
    {
        return $this->fKey;
    }

    public function getChatUser(): ChatUser
    {
        return $this->user;
    }
}
