<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\ValueObject;

use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use PHPUnit\Framework\TestCase;

class ChatParametersTest extends TestCase
{
    private ChatParameters $parameters;

    public function setUp(): void
    {
        $this->parameters = new ChatParameters('wss://example.com', 'xxyyzzff', new ChatUser(13, 'AsyncBot'));
    }

    public function testGetWebSocketUrl(): void
    {
        $this->assertSame('wss://example.com', $this->parameters->getWebSocketUrl());
    }

    public function testGetFKey(): void
    {
        $this->assertSame('xxyyzzff', $this->parameters->getFKey());
    }

    public function testGetChatUser(): void
    {
        $this->assertInstanceOf(ChatUser::class, $this->parameters->getChatUser());
    }
}
