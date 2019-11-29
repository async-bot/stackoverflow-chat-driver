<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit;

use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Driver;
use AsyncBot\Driver\StackOverflowChat\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testBuildReturnsDriverInstance(): void
    {
        $factory = new Factory(
            new Credentials(
                'test@example.com',
                'mysecret',
                'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
            ),
        );

        $this->assertInstanceOf(Driver::class, $factory->build());
    }
}
