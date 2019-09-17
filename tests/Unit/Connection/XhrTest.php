<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Connection;

use Amp\Http\Client\Client;
use Amp\Loop;
use Amp\Success;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Connection\Xhr;
use AsyncBot\Driver\StackOverflowChat\MessageQueue\Queue;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ConsecutiveResponseInterceptor;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ResponseInterceptor;
use PHPUnit\Framework\TestCase;

class XhrTest extends TestCase
{
    private Client $httpClient;

    private Queue $messageQueue;

    private Xhr $xhrClient;

    public function setUp(): void
    {
        $this->httpClient = new Client();

        $this->messageQueue = $this->createMock(Queue::class);

        $this->xhrClient = new Xhr(
            $this->httpClient,
            new Credentials(
                'test@example.com',
                'mysecret',
                'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
            ),
            new ChatParameters('ws://127.0.0.1:8009', 'xxxyyyzzzfff', new ChatUser(13, 'AsyncBot')),
            $this->messageQueue,
        );
    }

    public function testScheduleAddsMessageToQueue(): void
    {
        Loop::run(function () {
            $this->messageQueue
                ->expects($this->once())
                ->method('append')
                ->willReturn(new Success())
            ;

            yield $this->xhrClient->schedule('My message');

            Loop::stop();
        });
    }

    public function testScheduleStartsQueueProcessing(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ResponseInterceptor('all ok'),
        );

        Loop::run(function () {
            $this->messageQueue
                ->expects($this->once())
                ->method('append')
                ->willReturn(new Success())
            ;

            $this->messageQueue
                ->expects($this->exactly(3))
                ->method('get')
                ->willReturnOnConsecutiveCalls(new Success('My message'), new Success(null), new Success(null))
            ;

            yield $this->xhrClient->schedule('My message');

            Loop::delay(125, static function (): void {
                Loop::stop();
            });
        });
    }

    public function testScheduleReschedulesWithDelayAfterPostError(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor('error', 400),
                new ResponseInterceptor('all ok'),
            ),
        );

        Loop::run(function () {
            $this->messageQueue
                ->expects($this->once())
                ->method('append')
                ->willReturn(new Success())
            ;

            $this->messageQueue
                ->expects($this->once())
                ->method('prepend')
                ->willReturn(new Success())
            ;

            $this->messageQueue
                ->expects($this->exactly(2))
                ->method('get')
                ->willReturn(new Success('My message'))
            ;

            yield $this->xhrClient->schedule('My message');

            Loop::delay(1025, static function (): void {
                Loop::stop();
            });
        });
    }

    public function testScheduleDoesOnlyStartProcessingOfTheQueueOnceOnConsecutiveCalls(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ResponseInterceptor('error', 400),
        );

        Loop::run(function () {
            $this->messageQueue
                ->expects($this->exactly(2))
                ->method('append')
                ->willReturn(new Success())
            ;

            $this->messageQueue
                ->expects($this->once())
                ->method('prepend')
                ->willReturn(new Success())
            ;

            $this->messageQueue
                ->expects($this->once())
                ->method('get')
                ->willReturn(new Success('My message'))
            ;

            yield $this->xhrClient->schedule('My message');
            yield $this->xhrClient->schedule('My message');

            Loop::delay(75, static function (): void {
                Loop::stop();
            });
        });
    }
}
