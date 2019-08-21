<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class ChatLoginParameters
{
    private string $fKey;

    public function __construct(string $fKey)
    {
        $this->fKey = $fKey;
    }

    public function getFKey(): string
    {
        return $this->fKey;
    }
}
