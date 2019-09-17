<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Exception;

use AsyncBot\Driver\StackOverflowChat\Exception\NotConnected;
use PHPUnit\Framework\TestCase;

class NotConnectedTest extends TestCase
{
    public function testConstructorFormatsMessageCorrectly(): void
    {
        $this->expectException(NotConnected::class);
        $this->expectExceptionMessage('Bot did not connect to Stack Overflow yet');

        throw new NotConnected();
    }
}
