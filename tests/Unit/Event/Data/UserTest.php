<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Event\Data;

use AsyncBot\Driver\StackOverflowChat\Event\Data\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    public function setUp(): void
    {
        $this->user = new User(5764893, 'Jeeves');
    }

    public function testGetId(): void
    {
        $this->assertSame(5764893, $this->user->getId());
    }

    public function testGetName(): void
    {
        $this->assertSame('Jeeves', $this->user->getName());
    }
}
