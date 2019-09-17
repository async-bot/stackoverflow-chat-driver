<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\ValueObject;

use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use PHPUnit\Framework\TestCase;

class ChatUserTest extends TestCase
{
    private ChatUser $chatUser;

    public function setUp(): void
    {
        $this->chatUser = new ChatUser(13, 'AsyncBot');
    }

    public function testGetId(): void
    {
        $this->assertSame(13, $this->chatUser->getId());
    }

    public function testGetUsername(): void
    {
        $this->assertSame('AsyncBot', $this->chatUser->getUsername());
    }
}
