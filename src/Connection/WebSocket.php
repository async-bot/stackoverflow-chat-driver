<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Connection;

use Amp\Loop;
use Amp\Promise;
use Amp\Websocket\Client\Connection;
use Amp\Websocket\Client\ConnectionException;
use Amp\Websocket\Client\Handshake;
use Amp\Websocket\Message;
use Amp\Websocket\Options;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\Websocket\Client\connect;
use function ExceptionalJSON\decode;

final class WebSocket
{
    private ChatParameters $chatParameters;

    private Credentials $credentials;

    /** @var array<callable> */
    private array $listeners = [];

    public function __construct(ChatParameters $chatParameters, Credentials $credentials)
    {
        $this->chatParameters = $chatParameters;
        $this->credentials    = $credentials;
    }

    public function onMessage(callable $callback): self
    {
        $this->listeners[] = $callback;

        return $this;
    }

    public function start(): void
    {
        asyncCall(function () {
            try {
                /** @var Connection $connection */
                $connection = yield connect($this->getHandshake());

                /** @var Message $message */
                while ($message = yield $connection->receive()) {
                    yield $this->handleMessage($message);
                }
            } catch (ConnectionException $e) {
                // log closed connection?
            } finally {
                Loop::delay(5000, function (): void {
                    $this->start();
                });
            }
        });
    }

    private function getHandshake(): Handshake
    {
        return (new Handshake(
            $this->chatParameters->getWebSocketUrl(),
            // Stack Overflow's WS server does not support pings
            Options::createClientDefault()->withoutHeartbeat(),
        ))->withHeader('Origin', 'https://chat.stackoverflow.com');
    }

    /**
     * @return Promise<null>
     */
    private function handleMessage(Message $message): Promise
    {
        return call(function () use ($message) {
            $message = decode(yield $message->buffer(), true);

            if (!isset($message['r' . $this->credentials->getRoomId()]['e'][0])) {
                return;
            }

            foreach ($this->listeners as $listener) {
                yield $listener($message['r' . $this->credentials->getRoomId()]['e'][0]);
            }
        });
    }
}
