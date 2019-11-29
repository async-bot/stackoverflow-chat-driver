<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat;

use Amp\Http\Client\HttpClient;
use Amp\Promise;
use AsyncBot\Core\Driver as DriverInterface;
use AsyncBot\Driver\StackOverflowChat\Authentication\Authenticator;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Connection\WebSocket;
use AsyncBot\Driver\StackOverflowChat\Connection\Xhr;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message as StackOverflowMessage;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\Connected as ConnectedListener;
use AsyncBot\Driver\StackOverflowChat\Event\Listener\MessagePosted as MessagePostedListener;
use AsyncBot\Driver\StackOverflowChat\Exception\NotConnected;
use AsyncBot\Driver\StackOverflowChat\MessageQueue\ArrayQueue;
use function Amp\call;

final class Driver implements DriverInterface
{
    private HttpClient $httpClient;

    private Credentials $credentials;

    private Authenticator $authenticator;

    private ?ChatParameters $chatParameters;

    private ?Xhr $xhrClient = null;

    private array $listeners = [
        ConnectedListener::class => [],
        MessagePostedListener::class => [],
    ];

    public function __construct(HttpClient $httpClient, Credentials $credentials, Authenticator $authenticator)
    {
        $this->httpClient    = $httpClient;
        $this->credentials   = $credentials;
        $this->authenticator = $authenticator;
    }

    public function onConnect(ConnectedListener $listener): void
    {
        $this->listeners[ConnectedListener::class][] = $listener;
    }

    public function onNewMessage(MessagePostedListener $listener): void
    {
        $this->listeners[MessagePostedListener::class][] = $listener;
    }

    /**
     * @return Promise<null>
     */
    public function start(): Promise
    {
        return call(function () {
            $this->chatParameters = yield $this->authenticator->authenticate();

            $this->xhrClient = new Xhr($this->httpClient, $this->credentials, $this->chatParameters, new ArrayQueue());

            (new WebSocket($this->chatParameters, $this->credentials))
                ->onMessage(fn (array $message) => $this->onMessage($message))
                ->start()
            ;

            foreach ($this->listeners[ConnectedListener::class] as $listener) {
                yield $listener();
            }
        });
    }

    /**
     * @param array<string,mixed> $message
     * @return Promise<null>
     */
    private function onMessage(array $message): Promise
    {
        return call(function () use ($message) {
            if ($message['user_id'] === $this->chatParameters->getChatUser()->getId()) {
                return;
            }

            if ($message['event_type'] !== 1) {
                return;
            }

            $postedMessage = StackOverflowMessage::fromWebSocketMessage($message);

            foreach ($this->listeners[MessagePostedListener::class] as $listener) {
                yield $listener($postedMessage);
            }
        });
    }

    /**
     * @return Promise<null>
     * @throws NotConnected
     */
    public function postMessage(string $message): Promise
    {
        if ($this->xhrClient === null) {
            throw new NotConnected();
        }

        return $this->xhrClient->schedule($message);
    }
}
