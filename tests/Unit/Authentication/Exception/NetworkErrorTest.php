<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Exception;

use Amp\Http\Client\Request;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\CaptchaRequired;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\NetworkError;
use PHPUnit\Framework\TestCase;

class NetworkErrorTest extends TestCase
{
    public function testConstructorPassesTheCorrectMessage(): void
    {
        $this->expectException(NetworkError::class);
        $this->expectExceptionMessage('Network error when requesting https://example.com over POST');

        throw new NetworkError(new Request('https://example.com', 'POST'));
    }

    public function testConstructorPassesErrorCode(): void
    {
        $this->expectException(NetworkError::class);
        $this->expectExceptionCode(13);

        throw new NetworkError(new Request('https://example.com', 'POST'), 13);
    }

    public function testConstructorPassesPreviousThrowable(): void
    {
        try {
            throw new NetworkError(new Request('https://example.com', 'POST'), 13, new CaptchaRequired());
        } catch (NetworkError $e) {
            $this->assertInstanceOf(CaptchaRequired::class, $e->getPrevious());
        }
    }
}
