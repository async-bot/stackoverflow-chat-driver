<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\ValueObject;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\InvalidRoomUrl;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use PHPUnit\Framework\TestCase;

class CredentialsTest extends TestCase
{
    private Credentials $credentials;

    public function setUp(): void
    {
        $this->credentials = new Credentials(
            'test@example.com',
            'mysecret',
            'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
        );
    }

    public function testConstructorThrowsOnInvalidRoomUrl(): void
    {
        $this->expectException(InvalidRoomUrl::class);
        $this->expectExceptionMessage('https://example.com is not recognized as a valid Stack Overflow room URL');

        new Credentials('test@example.com', 'mysecret', 'https://example.com');
    }

    public function testGetEmailAddress(): void
    {
        $this->assertSame('test@example.com', $this->credentials->getEmailAddress());
    }

    public function testGetPassword(): void
    {
        $this->assertSame('mysecret', $this->credentials->getPassword());
    }

    public function testGetRoomId(): void
    {
        $this->assertSame(100286, $this->credentials->getRoomId());
    }

    public function testGetRoomSlug(): void
    {
        $this->assertSame('jeeves-playground', $this->credentials->getRoomSlug());
    }
}
