<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication;

use Amp\Http\Client\Client;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\HttpException;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\NetworkError;
use AsyncBot\Driver\StackOverflowChat\Authentication\UserRetriever;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatParameters;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChat\Authentication\WebSocket;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ConsecutiveResponseInterceptor;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ExceptionResponseInterceptor;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ResponseInterceptor;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;
use function ExceptionalJSON\encode;

class WebSocketTest extends TestCase
{
    private Credentials $credentials;

    public function setUp(): void
    {
        $this->credentials = new Credentials(
            'test@example.com',
            'mysecret',
            'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
        );
    }

    public function testGetChatParametersThrowsOnHttpExceptionWhenTryingToRetrieveRoomPage(): void
    {
        $webSocket = new WebSocket(
            (new HttpClientBuilder())->intercept(
                new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
            )->build(),
            $this->credentials,
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://chat.stackoverflow.com/rooms/100286/jeeves-playground over GET');

        wait($webSocket->getChatParameters());
    }

    public function testGetChatParametersThrowsOnHttpExceptionWhenTryingToRetrieveTheWebSocketUrl(): void
    {
        $webSocket = new WebSocket(
            (new HttpClientBuilder())->intercept(
                new ConsecutiveResponseInterceptor(
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
                    new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
                ),
            )->build(),
            $this->credentials,
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://chat.stackoverflow.com/ws-auth over POST');

        wait($webSocket->getChatParameters());
    }

    public function testGetChatParametersThrowsOnNon200ResponseWhenTryingToRetrieveTheWebSocketUrl(): void
    {
        $webSocket = new WebSocket(
            (new HttpClientBuilder())->intercept(
                new ConsecutiveResponseInterceptor(
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
                    new ResponseInterceptor('error', 400),
                ),
            )->build(),
            $this->credentials,
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Network error when requesting https://chat.stackoverflow.com/ws-auth over POST');

        wait($webSocket->getChatParameters());
    }

    public function testGetChatParametersThrowsOnHttpExceptionWhenTryingToRetrieveTheLastMessageId(): void
    {
        $webSocket = new WebSocket(
            (new HttpClientBuilder())->intercept(
                new ConsecutiveResponseInterceptor(
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
                    new ResponseInterceptor(encode([
                        'url' => 'https://chat.sockets.stackexchange.com/events/xxxyyyzzzfff?l=96976114',
                    ])),
                    new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
                ),
            )->build(),
            $this->credentials,
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://chat.stackoverflow.com/chats/100286/events over POST');

        wait($webSocket->getChatParameters());
    }

    public function testGetChatParametersThrowsOnNon200ResponseWhenTryingToRetrieveTheLastMessageId(): void
    {
        $webSocket = new WebSocket(
            (new HttpClientBuilder())->intercept(
                new ConsecutiveResponseInterceptor(
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
                    new ResponseInterceptor(encode([
                        'url' => 'https://chat.sockets.stackexchange.com/events/xxxyyyzzzfff?l=96976114',
                    ])),
                    new ResponseInterceptor('error', 400),
                ),
            )->build(),
            $this->credentials,
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Network error when requesting https://chat.stackoverflow.com/chats/100286/events over POST');

        wait($webSocket->getChatParameters());
    }

    public function testGetChatParametersReturnsChatParametersWhenSuccessful(): void
    {
        $webSocket = new WebSocket(
            (new HttpClientBuilder())->intercept(
                new ConsecutiveResponseInterceptor(
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
                    new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
                    new ResponseInterceptor(encode([
                        'url' => 'https://chat.sockets.stackexchange.com/events/xxxyyyzzzfff?l=96976114',
                    ])),
                    new ResponseInterceptor(encode(['time' => 12345])),
                ),
            )->build(),
            $this->credentials,
        );

        $chatParameters = wait($webSocket->getChatParameters());

        $this->assertInstanceOf(ChatParameters::class, $chatParameters);
    }
}
