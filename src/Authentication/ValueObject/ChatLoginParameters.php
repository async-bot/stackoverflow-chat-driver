<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class ChatLoginParameters
{
    private string $fKey;

    private ChatUser $user;

    public function __construct(string $fKey, ChatUser $user)
    {
        $this->fKey = $fKey;
        $this->user = $user;
    }

    public function getFKey(): string
    {
        return $this->fKey;
    }

    public function getUser(): ChatUser
    {
        return $this->user;
    }
}
