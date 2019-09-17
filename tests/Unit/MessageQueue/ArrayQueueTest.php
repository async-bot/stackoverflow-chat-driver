<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\MessageQueue;

use AsyncBot\Driver\StackOverflowChat\MessageQueue\ArrayQueue;
use PHPUnit\Framework\TestCase;
use function Amp\call;
use function Amp\Promise\wait;

class ArrayQueueTest extends TestCase
{
    private ArrayQueue $queue;

    public function setUp(): void
    {
        $this->queue = new ArrayQueue();
    }

    public function testAppend(): void
    {
        wait(call(function () {
            yield $this->queue->append('Message 1');
            yield $this->queue->append('Message 2');
            yield $this->queue->append('Message 3');

            $this->assertSame('Message 1', yield $this->queue->get());
            $this->assertSame('Message 2', yield $this->queue->get());
            $this->assertSame('Message 3', yield $this->queue->get());
            $this->assertNull(yield $this->queue->get());
        }));
    }

    public function testPrepend(): void
    {
        wait(call(function () {
            yield $this->queue->append('Message 1');
            yield $this->queue->prepend('Message 2');
            yield $this->queue->append('Message 3');

            $this->assertSame('Message 2', yield $this->queue->get());

            yield $this->queue->prepend('Message 4');

            $this->assertSame('Message 4', yield $this->queue->get());
            $this->assertSame('Message 1', yield $this->queue->get());
            $this->assertSame('Message 3', yield $this->queue->get());
            $this->assertNull(yield $this->queue->get());
        }));
    }
}
