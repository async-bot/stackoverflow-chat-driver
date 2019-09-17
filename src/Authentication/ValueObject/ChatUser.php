<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class ChatUser
{
    private int $id;

    private string $username;

    public function __construct(int $id, string $username)
    {
        $this->id       = $id;
        $this->username = $username;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
