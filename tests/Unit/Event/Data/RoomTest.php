<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Event\Data;

use AsyncBot\Driver\StackOverflowChat\Event\Data\Room;
use PHPUnit\Framework\TestCase;

class RoomTest extends TestCase
{
    private Room $room;

    public function setUp(): void
    {
        $this->room = new Room(100286, 'Jeeves\' Playground');
    }

    public function testGetId(): void
    {
        $this->assertSame(100286, $this->room->getId());
    }

    public function testGetName(): void
    {
        $this->assertSame('Jeeves\' Playground', $this->room->getName());
    }
}
