<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\Client;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use AsyncBot\Core\Driver;
use AsyncBot\Driver\StackOverflowChat\Authentication\Authenticator;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use function Amp\call;

final class Bot implements Driver
{
    private Client $httpClient;

    private Authenticator $authenticator;

    private bool $started = false;

    private ?ChatParameters $chatParameters;

    public function __construct(Client $httpClient, Authenticator $authenticator)
    {
        $this->httpClient    = $httpClient;
        $this->authenticator = $authenticator;
    }

    /**
     * @return Promise<null>
     */
    public function start(): Promise
    {
        return call(function () {
            $this->chatParameters = yield $this->authenticator->authenticate();

            $this->started = true;
        });
    }

    /**
     * @return Promise<null>
     */
    public function postMessage(string $message): Promise
    {
        return call(function () use ($message) {
            if (!$this->started) {
                return;
            }

            $body = new FormBody();

            $body->addField('text', $message);
            $body->addField('fkey', $this->chatParameters->getFKey());

            $request = new Request('https://chat.stackoverflow.com/chats/198198/messages/new', 'POST');

            $request->setBody($body);

            /** @var Response $response */
            $response = yield $this->httpClient->request($request);

            if ($response->getStatus() !== 200) {
                throw new \Exception('Post error');
            }
        });
    }
}
