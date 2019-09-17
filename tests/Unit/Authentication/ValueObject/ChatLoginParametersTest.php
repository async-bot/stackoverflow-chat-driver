<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\ValueObject;

use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatLoginParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use PHPUnit\Framework\TestCase;

class ChatLoginParametersTest extends TestCase
{
    private ChatLoginParameters $parameters;

    public function setUp(): void
    {
        $this->parameters = new ChatLoginParameters('xxyyzzff', new ChatUser(13, 'AsyncBot'));
    }

    public function testGetFKey(): void
    {
        $this->assertSame('xxyyzzff', $this->parameters->getFKey());
    }

    public function testGetUser(): void
    {
        $this->assertInstanceOf(ChatUser::class, $this->parameters->getUser());
    }
}
