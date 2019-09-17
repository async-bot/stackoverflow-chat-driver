<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Event\Data;

use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Room;
use AsyncBot\Driver\StackOverflowChat\Event\Data\User;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private Message $message;

    public function setUp(): void
    {
        $this->message = new Message(
            47317296,
            new Room(100286, 'Jeeves\' Playground'),
            new User(5764893, 'Jeeves'),
            '[tag:wotd] **[Yooper](https://www.merriam-webster.com/dictionary/Yooper)**  a native or resident of the Upper Peninsula of Michigan — used as a nickname',
            new \DateTimeImmutable('2019-09-17 16:08:56'),
        );
    }

    public function testGetId(): void
    {
        $this->assertSame(47317296, $this->message->getId());
    }

    public function testGetRoom(): void
    {
        $this->assertInstanceOf(Room::class, $this->message->getRoom());
        $this->assertSame(100286, $this->message->getRoom()->getId());
        $this->assertSame('Jeeves\' Playground', $this->message->getRoom()->getName());
    }

    public function testGetUser(): void
    {
        $this->assertInstanceOf(User::class, $this->message->getUser());
        $this->assertSame(5764893, $this->message->getUser()->getId());
        $this->assertSame('Jeeves', $this->message->getUser()->getName());
    }

    public function testGetContent(): void
    {
        $this->assertSame(
            '[tag:wotd] **[Yooper](https://www.merriam-webster.com/dictionary/Yooper)**  a native or resident of the Upper Peninsula of Michigan — used as a nickname',
            $this->message->getContent(),
        );
    }

    public function testGetTimestamp(): void
    {
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->message->getTimestamp());
        $this->assertSame('2019-09-17 16:08:56', $this->message->getTimestamp()->format('Y-m-d H:i:s'));
    }

    public function testFromWebSocketMessage(): void
    {
        $message = Message::fromWebSocketMessage([
            'message_id' => 47317296,
            'room_id'    => 100286,
            'room_name'  => 'Jeeves\' Playground',
            'user_id'    => 5764893,
            'user_name'  => 'Jeeves',
            'content'    => '[tag:wotd] **[Yooper](https://www.merriam-webster.com/dictionary/Yooper)**  a native or resident of the Upper Peninsula of Michigan — used as a nickname',
            'time_stamp' => '1568729336',
        ]);

        $this->assertSame(47317296, $message->getId());

        $this->assertInstanceOf(Room::class, $message->getRoom());
        $this->assertSame(100286, $message->getRoom()->getId());
        $this->assertSame('Jeeves\' Playground', $message->getRoom()->getName());

        $this->assertInstanceOf(User::class, $message->getUser());
        $this->assertSame(5764893, $message->getUser()->getId());
        $this->assertSame('Jeeves', $message->getUser()->getName());

        $this->assertSame(
            '[tag:wotd] **[Yooper](https://www.merriam-webster.com/dictionary/Yooper)**  a native or resident of the Upper Peninsula of Michigan — used as a nickname',
            $message->getContent(),
        );

        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getTimestamp());
        $this->assertSame('2019-09-17 14:08:56', $message->getTimestamp()->format('Y-m-d H:i:s'));
    }
}
