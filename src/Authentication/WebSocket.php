<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\NetworkError;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\ChatPage;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatLoginParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use function Amp\call;
use function ExceptionalJSON\decode;
use function Room11\DOMUtils\domdocument_load_html;

final class WebSocket
{
    private HttpClient $httpClient;

    private Credentials $credentials;

    public function __construct(HttpClient $httpClient, Credentials $credentials)
    {
        $this->httpClient  = $httpClient;
        $this->credentials = $credentials;
    }

    /**
     * @return Promise<ChatParameters>
     */
    public function getChatParameters(): Promise
    {
        return call(function () {
            /** @var ChatLoginParameters $chatLoginParameters */
            $chatLoginParameters = yield $this->getChatLoginParameters();

            /** @var string $webSocketUrl */
            $webSocketUrl = yield $this->getWebSocketUrl($chatLoginParameters);

            return new ChatParameters($webSocketUrl, $chatLoginParameters->getFKey(), $chatLoginParameters->getUser());
        });
    }

    /**
     * @return Promise<ChatLoginParameters>
     */
    private function getChatLoginParameters(): Promise
    {
        return call(function () {
            $request = new Request(
                sprintf(
                    'https://chat.stackoverflow.com/rooms/%d/%s',
                    $this->credentials->getRoomId(),
                    $this->credentials->getRoomSlug(),
                ),
            );

            try {
                /** @var Response $response */
                $response = yield $this->httpClient->request($request);
            } catch (HttpException $e) {
                throw new NetworkError($request, 0, $e);
            }

            $dom = domdocument_load_html(yield $response->getBody()->buffer());

            return new ChatLoginParameters(
                (new ChatPage())->parse($dom),
                yield (new UserRetriever($this->httpClient))->retrieve($dom),
            );
        });
    }

    /**
     * @return Promise<string>
     */
    private function getWebSocketUrl(ChatLoginParameters $parameters): Promise
    {
        return call(function () use ($parameters) {
            $body = new FormBody();

            $body->addField('roomid', (string) $this->credentials->getRoomId());
            $body->addField('fkey', $parameters->getFKey());

            $request = new Request('https://chat.stackoverflow.com/ws-auth', 'POST');

            $request->setBody($body);

            try {
                /** @var Response $response */
                $response = yield $this->httpClient->request($request);
            } catch (HttpException $e) {
                throw new NetworkError($request, 0, $e);
            }

            if ($response->getStatus() !== 200) {
                throw new NetworkError($request, $response->getStatus());
            }

            $decodedResponse = decode(yield $response->getBody()->buffer(), true);

            return sprintf('%s?l=%d', $decodedResponse['url'], yield $this->getLastMessageId($parameters));
        });
    }

    /**
     * @return Promise<int>
     */
    private function getLastMessageId(ChatLoginParameters $parameters): Promise
    {
        return call(function () use ($parameters) {
            $body = new FormBody();

            $body->addField('since', '0');
            $body->addField('mode', 'Messages');
            $body->addField('msgCount', '100');
            $body->addField('fkey', $parameters->getFKey());

            $request = new Request(sprintf('https://chat.stackoverflow.com/chats/%d/events', $this->credentials->getRoomId()), 'POST');

            $request->setBody($body);

            try {
                /** @var Response $response */
                $response = yield $this->httpClient->request($request);
            } catch (HttpException $e) {
                throw new NetworkError($request, 0, $e);
            }

            if ($response->getStatus() !== 200) {
                throw new NetworkError($request, $response->getStatus());
            }

            $decodedResponse = decode(yield $response->getBody()->buffer(), true);

            return $decodedResponse['time'];
        });
    }
}
