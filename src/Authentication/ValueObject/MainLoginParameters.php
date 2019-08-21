<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class MainLoginParameters
{
    private string $fKey;

    private string $ssrc;

    public function __construct(string $fKey, string $ssrc)
    {
        $this->fKey = $fKey;
        $this->ssrc = $ssrc;
    }

    public function getFKey(): string
    {
        return $this->fKey;
    }

    public function getSsrc(): string
    {
        return $this->ssrc;
    }
}
