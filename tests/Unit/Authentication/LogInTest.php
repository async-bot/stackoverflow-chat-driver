<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication;

use Amp\Http\Client\HttpClientBuilder;
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
    private Credentials $credentials;

    public function setUp(): void
    {
        $this->credentials = new Credentials(
            'test@example.com',
            'mysecret',
            'https://chat.stackoverflow.com/rooms/100286/jeeves-playground',
        );
    }

    public function testProcessThrowsOnHttpExceptionWhenTryingToRetrieveTheLoginPage(): void
    {
        $httpClient = (new HttpClientBuilder())->intercept(
            new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
        )->build();

        $login = new LogIn($httpClient, $this->credentials);

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://stackoverflow.com/users/login over GET');

        wait($login->process());
    }

    public function testProcessThrowsOnNon200ResponseWhenTryingToRetrieveTheLoginPage(): void
    {
        $httpClient = (new HttpClientBuilder())->intercept(
            new ResponseInterceptor('error', 400),
        )->build();

        $login = new LogIn($httpClient, $this->credentials);

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('Network error when requesting https://stackoverflow.com/users/login over GET');

        wait($login->process());
    }

    public function testProcessThrowsOnHttpExceptionWhenTryingToLogIn(): void
    {
        $httpClient = (new HttpClientBuilder())->intercept(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ExceptionResponseInterceptor(new HttpException('Something went wrong')),
            ),
        )->build();

        $login = new LogIn($httpClient, $this->credentials);

        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(0);
        $this->expectExceptionMessage('Network error when requesting https://stackoverflow.com/users/login over POST');

        wait($login->process());
    }

    public function testProcessThrowsOnNon302ResponseWhenTryingToLogIn(): void
    {
        $httpClient = (new HttpClientBuilder())->intercept(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('error', 403),
            ),
        )->build();

        $login = new LogIn($httpClient, $this->credentials);

        $this->expectException(InvalidCredentials::class);

        wait($login->process());
    }

    public function testProcessThrowsOnCaptchaRequiredResponse(): void
    {
        $httpClient = (new HttpClientBuilder())->intercept(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('captcha required', 302),
            ),
        )->build();

        $login = new LogIn($httpClient, $this->credentials);

        $this->expectException(CaptchaRequired::class);

        wait($login->process());
    }

    public function testProcessFinishesWithoutExceptionWhenLogInIsSuccessful(): void
    {
        $httpClient = (new HttpClientBuilder())->intercept(
            new ConsecutiveResponseInterceptor(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/login-page.html')),
                new ResponseInterceptor('captcha required', 302, ['location' => 'https://stackoverflow.com/']),
            ),
        )->followRedirects(0)->build();

        $login = new LogIn($httpClient, $this->credentials);

        Loop::run(function () use ($login) {
            $promise = $login->process();

            $promise->onResolve(function (?\Throwable $e, $value): void {
                $this->assertNull($e);
                $this->assertNull($value);
            });

            yield $promise;
        });
    }
}
