<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication;

use Amp\Http\Client\Client;
use AsyncBot\Driver\StackOverflowChat\Authentication\Authenticator;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ConsecutiveResponseInterceptor;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ResponseInterceptor;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;
use function ExceptionalJSON\encode;

class AuthenticatorTest extends TestCase
{
    private Client $httpClient;

    private Authenticator $authenticator;

    public function setUp(): void
    {
        $this->httpClient = new Client();

        $this->authenticator = new Authenticator(
            $this->httpClient,
            new Credentials(
                'test@example.com',
                'mysecret',
                'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
            ),
        );
    }
    public function testAuthenticate(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('captcha required', 302, ['location' => 'https://stackoverflow.com/']),
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
                new ResponseInterceptor(encode([
                    'url' => 'https://chat.sockets.stackexchange.com/events/xxxyyyzzzfff?l=96976114',
                ])),
                new ResponseInterceptor(encode(['time' => 12345])),
            ),
        );

        $chatParameters = wait($this->authenticator->authenticate());

        $this->assertInstanceOf(ChatParameters::class, $chatParameters);
    }
}
