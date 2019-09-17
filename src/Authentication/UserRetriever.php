<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication;

use Amp\Http\Client\Client;
use Amp\Http\Client\Response;
use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\ChatUserPage;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use function Amp\call;

final class UserRetriever
{
    private Client $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return Promise<ChatUser>
     * @throws UnexpectedHtmlFormat
     */
    public function retrieve(\DOMDocument $dom): Promise
    {
        $activeUserDiv = $dom->getElementById('active-user');

        if ($activeUserDiv === null) {
            throw new UnexpectedHtmlFormat('active user');
        }

        $userId = $this->getUserId($activeUserDiv);

        return $this->getChatUser($userId);
    }

    private function getUserId(\DOMElement $activeUserDiv): int
    {
        $classes = $activeUserDiv->getAttribute('class');

        if ($classes === '') {
            throw new UnexpectedHtmlFormat('active user class');
        }

        if (preg_match('~user-(\d+)~', $classes, $matches) !== 1) {
            throw new UnexpectedHtmlFormat('active user class');
        }

        return (int) $matches[1];
    }

    /**
     * @return Promise<ChatUser>
     */
    private function getChatUser(int $userId): Promise
    {
        return call(function () use ($userId) {
            /** @var Response $response */
            $response = yield $this->httpClient->request(sprintf('https://chat.stackoverflow.com/users/%d', $userId));

            return (new ChatUserPage())->parse($userId, yield $response->getBody()->buffer());
        });
    }
}
