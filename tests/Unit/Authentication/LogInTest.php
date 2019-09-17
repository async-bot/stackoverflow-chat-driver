<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication;

use Amp\Http\Client\Client;
use Amp\Http\Client\HttpException;
use Amp\Loop;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\CaptchaRequired;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\InvalidCredentials;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\NetworkError;
use AsyncBot\Driver\StackOverflowChat\Authentication\LogIn;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\Credentials;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ConsecutiveResponseInterceptor;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ExceptionResponseInterceptor;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ResponseInterceptor;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;

class LogInTest extends TestCase
{
    private Client $httpClient;

    private LogIn $login;

    public function setUp(): void
    {
        $this->httpClient = new Client();

        $this->login = new LogIn(
            $this->httpClient,
            new Credentials(
                'test@example.com',
                'mysecret',
                'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
            ),
        );
    }

    public function testProcessThrowsOnHttpExceptionWhenTryingToRetrieveTheLoginPage(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://stackoverflow.com/users/login over GET');

        wait($this->login->process());
    }

    public function testProcessThrowsOnNon200ResponseWhenTryingToRetrieveTheLoginPage(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ResponseInterceptor('error', 400),
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Network error when requesting https://stackoverflow.com/users/login over GET');

        wait($this->login->process());
    }

    public function testProcessThrowsOnHttpExceptionWhenTryingToLogIn(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
            ),
        );

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://stackoverflow.com/users/login over POST');

        wait($this->login->process());
    }

    public function testProcessThrowsOnNon302ResponseWhenTryingToLogIn(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('error', 403),
            ),
        );

        $this->expectException(InvalidCredentials::class);

        wait($this->login->process());
    }

    public function testProcessThrowsOnCaptchaRequiredResponse(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('captcha required', 302),
            ),
        );

        $this->expectException(CaptchaRequired::class);

        wait($this->login->process());
    }

    public function testProcessFinishesWithoutExceptionWhenLogInIsSuccessful(): void
    {
        $this->httpClient->addApplicationInterceptor(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('captcha required', 302, ['location' => 'https://stackoverflow.com/']),
            ),
        );

        Loop::run(function () {
            $promise = $this->login->process();

            $promise->onResolve(function (?\Throwable $e, $value): void {
                $this->assertNull($e);
                $this->assertNull($value);
            });

            yield $promise;
        });
    }
}
