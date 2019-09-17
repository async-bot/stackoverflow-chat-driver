<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat;

use Amp\Http\Client\Client;
use AsyncBot\Driver\StackOverflowChat\Authentication\Authenticator;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;

final class Factory
{
    private Client $httpClient;

    private Credentials $credentials;

    public function __construct(Client $httpClient, Credentials $credentials)
    {
        $this->httpClient  = $httpClient;
        $this->credentials = $credentials;
    }

    public function build(): Driver
    {
        return new Driver(
            $this->httpClient,
            $this->credentials,
            new Authenticator($this->httpClient, $this->credentials),
        );
    }
}
