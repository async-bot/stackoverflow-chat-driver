<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Exception;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\InvalidCredentials;
use PHPUnit\Framework\TestCase;

class InvalidCredentialsTest extends TestCase
{
    public function testConstructorPassesTheCorrectMessage(): void
    {
        $this->expectException(InvalidCredentials::class);
        $this->expectExceptionMessage('Could not authenticate with StackOverflow');

        throw new InvalidCredentials();
    }
}
