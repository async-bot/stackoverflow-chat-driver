<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication;

use Amp\Http\Client\Body\FormBody;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpException;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\CaptchaRequired;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\InvalidCredentials;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\NetworkError;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\LogInPage;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\MainLoginParameters;
use function Amp\call;

final class LogIn
{
    private HttpClient $httpClient;

    private Credentials $credentials;

    public function __construct(HttpClient $httpClient, Credentials $credentials)
    {
        $this->httpClient  = $httpClient;
        $this->credentials = $credentials;
    }

    /**
     * @return Promise<null>
     */
    public function process(): Promise
    {
        return call(function () {
            /** @var MainLoginParameters $parameters */
            $parameters = yield $this->getLogInParameters();

            yield $this->logIn($parameters);
        });
    }

    /**
     * @return Promise<MainLoginParameters>
     */
    private function getLogInParameters(): Promise
    {
        return call(function () {
            $request = new Request('https://stackoverflow.com/users/login');

            try {
                /** @var Response $response */
                $response = yield $this->httpClient->request($request);
            } catch (HttpException $e) {
                throw new NetworkError($request, 0, $e);
            }

            if ($response->getStatus() !== 200) {
                throw new NetworkError($request, $response->getStatus());
            }

            return (new LogInPage())->parse(yield $response->getBody()->buffer());
        });
    }

    /**
     * @return Promise<null>
     */
    private function logIn(MainLoginParameters $parameters): Promise
    {
        return call(function () use ($parameters) {
            $body = new FormBody();

            $body->addField('fkey', $parameters->getFKey());
            $body->addField('ssrc', $parameters->getSsrc());
            $body->addField('email', $this->credentials->getEmailAddress());
            $body->addField('password', $this->credentials->getPassword());
            $body->addField('oauth_version', '');
            $body->addField('oauth_server', '');

            $request = new Request('https://stackoverflow.com/users/login', 'POST');

            $request->setBody($body);

            try {
                /** @var Response $response */
                $response = yield $this->httpClient->request($request);
            } catch (HttpException $e) {
                throw new NetworkError($request, 0, $e);
            }

            if ($response->getStatus() !== 302) {
                throw new InvalidCredentials();
            }

            if ($response->getHeader('location') !== 'https://stackoverflow.com/') {
                throw new CaptchaRequired();
            }
        });
    }
}
