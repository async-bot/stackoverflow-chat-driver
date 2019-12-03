<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat;

use Amp\Http\Client\Cookie\CookieInterceptor;
use Amp\Http\Client\Cookie\InMemoryCookieJar;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use AsyncBot\Driver\StackOverflowChat\Authentication\Authenticator;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;

final class Factory
{
    private Credentials $credentials;

    public function __construct(Credentials $credentials)
    {
        $this->credentials = $credentials;
    }

    public function build(): Driver
    {
        $httpClient = $this->buildHttpClient();

        return new Driver(
            $httpClient,
            $this->credentials,
            new Authenticator($httpClient, $this->credentials),
        );
    }

    private function buildHttpClient(): HttpClient
    {
        $cookieInterceptor = new CookieInterceptor(new InMemoryCookieJar());

        return (new HttpClientBuilder())
            ->followRedirects(0)
            ->interceptNetwork($cookieInterceptor)
            ->build()
        ;
    }
}
