<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\Client;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\NetworkError;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\ChatPage;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatLoginParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use function Amp\call;
use function ExceptionalJSON\decode;

final class WebSocket
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return Promise<ChatParameters>
     */
    public function getChatParameters(): Promise
    {
        return call(function () {
            /** @var ChatLoginParameters $chatParameters */
            $chatLoginParameters = yield $this->getChatLoginParameters();

            /** @var string $webSocketUrl */
            $webSocketUrl = yield $this->getWebSocketUrl($chatLoginParameters);

            return new ChatParameters($webSocketUrl, $chatLoginParameters->getFKey());
        });
    }

    /**
     * @return Promise<ChatLoginParameters>
     */
    private function getChatLoginParameters(): Promise
    {
        return call(function () {
            $request = new Request('https://chat.stackoverflow.com/rooms/198198/asyncbot-playground');

            try {
                /** @var Response $response */
                $response = yield $this->httpClient->request($request);
            } catch (HttpException $e) {
                throw new NetworkError($request, 0, $e);
            }

            return (new ChatPage())->parse(yield $response->getBody()->buffer());
        });
    }

    /**
     * @return Promise<string>
     */
    private function getWebSocketUrl(ChatLoginParameters $parameters): Promise
    {
        return call(function () use ($parameters) {
            $body = new FormBody();

            $body->addField('roomid', '198198');
            $body->addField('fkey', $parameters->getFKey());

            $request = new Request('https://chat.stackoverflow.com/ws-auth', 'POST');

            $request->setBody($body);

            /** @var Response $response */
            $response = yield $this->httpClient->request($request);

            if ($response->getStatus() !== 200) {
                throw new NetworkError($request, $response->getStatus());
            }

            $decodedResponse = decode(yield $response->getBody()->buffer(), true);

            return $decodedResponse['url'];
        });
    }
}
