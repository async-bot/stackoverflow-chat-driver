<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Connection;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\Client;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Loop;
use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\MessageQueue\Queue;
use function Amp\call;

final class Xhr
{
    private Client $httpClient;

    private Credentials $credentials;

    private ChatParameters $chatParameters;

    private Queue $messageQueue;

    private bool $started = false;

    public function __construct(Client $httpClient, Credentials $credentials, ChatParameters $chatParameters, Queue $messageQueue)
    {
        $this->httpClient     = $httpClient;
        $this->credentials    = $credentials;
        $this->chatParameters = $chatParameters;
        $this->messageQueue   = $messageQueue;
    }

    /**
     * @return Promise<null>
     */
    public function schedule(string $message): Promise
    {
        return call(function () use ($message) {
            yield $this->messageQueue->append($message);

            if ($this->started) {
                return null;
            }

            $this->started = true;

            return yield $this->postMessage();
        });
    }

    /**
     * @return Promise<null>
     */
    private function postMessage(): Promise
    {
        return call(function () {
            $message = yield $this->messageQueue->get();

            if ($message === null) {
                Loop::delay(50, function () {
                    yield $this->postMessage();
                });

                return null;
            }

            $body = new FormBody();

            $body->addField('text', $message);
            $body->addField('fkey', $this->chatParameters->getFKey());

            $request = new Request(sprintf('https://chat.stackoverflow.com/chats/%d/messages/new', $this->credentials->getRoomId()), 'POST');

            $request->setBody($body);

            /** @var Response $response */
            $response = yield $this->httpClient->request($request);

            if ($response->getStatus() !== 200) {
                $this->messageQueue->prepend($message);

                Loop::delay(1000, function () {
                    yield $this->postMessage();
                });

                return;
            }

            Loop::delay(50, function () {
                yield $this->postMessage();
            });
        });
    }
}
