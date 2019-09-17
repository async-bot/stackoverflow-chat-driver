<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Exception;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\CaptchaRequired;
use PHPUnit\Framework\TestCase;

class CaptchaRequiredTest extends TestCase
{
    public function testConstructorPassesTheCorrectMessage(): void
    {
        $this->expectException(CaptchaRequired::class);
        $this->expectExceptionMessage('The StackOverflow authentication requested a captcha');

        throw new CaptchaRequired();
    }
}
