<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Connection;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Loop;
use Amp\Promise;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Parser;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Message\Formatter;
use AsyncBot\Driver\StackOverflowChat\MessageQueue\Queue;
use function Amp\call;

final class Xhr
{
    private HttpClient $httpClient;

    private Credentials $credentials;

    private ChatParameters $chatParameters;

    private Queue $messageQueue;

    private bool $started = false;

    public function __construct(HttpClient $httpClient, Credentials $credentials, ChatParameters $chatParameters, Queue $messageQueue)
    {
        $this->httpClient     = $httpClient;
        $this->credentials    = $credentials;
        $this->chatParameters = $chatParameters;
        $this->messageQueue   = $messageQueue;
    }

    /**
     * @return Promise<null>
     */
    public function schedule(Message $message): Promise
    {
        return call(function () use ($message) {
            yield $this->messageQueue->append($message->toString());

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

            $message = (new Parser())->parse($message);

            $body = new FormBody();

            $body->addField('text', (new Formatter())->format($message));
            $body->addField('fkey', $this->chatParameters->getFKey());

            $request = new Request(sprintf('https://chat.stackoverflow.com/chats/%d/messages/new', $this->credentials->getRoomId()), 'POST');

            $request->setBody($body);

            /** @var Response $response */
            $response = yield $this->httpClient->request($request);

            if ($response->getStatus() !== 200) {
                $this->messageQueue->prepend($message->toString());

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

    /**
     * @return Promise<null>
     */
    private function pinOrUnpinMessage(int $messageId): Promise
    {
        return call(function () use ($messageId) {
            $body = new FormBody();

            $body->addField('fkey', $this->chatParameters->getFKey());

            $request = new Request(sprintf('https://chat.stackoverflow.com/messages/%d/owner-star', $messageId), 'POST');

            $request->setBody($body);

            yield $this->httpClient->request($request);

        });
    }
}
