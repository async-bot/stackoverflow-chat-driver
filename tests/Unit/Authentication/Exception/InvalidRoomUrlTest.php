<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Exception;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\InvalidRoomUrl;
use PHPUnit\Framework\TestCase;

class InvalidRoomUrlTest extends TestCase
{
    public function testConstructorPassesTheCorrectMessage(): void
    {
        $this->expectException(InvalidRoomUrl::class);
        $this->expectExceptionMessage('https://example.com is not recognized as a valid Stack Overflow room URL');

        throw new InvalidRoomUrl('https://example.com');
    }
}
