<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication;

use Amp\Http\Client\Client;
use Amp\Http\Client\Cookie\ArrayCookieJar;
use Amp\Http\Client\Cookie\CookieHandler;
use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use function Amp\call;

final class Authenticator
{
    private Client $httpClient;

    private Credentials $credentials;

    public function __construct(Client $httpClient, Credentials $credentials)
    {
        $this->httpClient  = $httpClient;
        $this->credentials = $credentials;
    }

    /**
     * @return Promise<ChatParameters>
     */
    public function authenticate(): Promise
    {
        return call(function () {
            $this->httpClient->addNetworkInterceptor(new CookieHandler(new ArrayCookieJar()));

            yield (new LogIn($this->httpClient, $this->credentials))->process();

            return yield (new WebSocket($this->httpClient))->getChatParameters();
        });
    }
}
