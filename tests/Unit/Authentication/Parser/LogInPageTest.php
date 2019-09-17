<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\LogInPage;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\MainLoginParameters;
use PHPUnit\Framework\TestCase;

class LogInPageTest extends TestCase
{
    public function testParseReturnsMainLoginParameters(): void
    {
        $mainLoginParameters = (new LogInPage())->parse(file_get_contents(TEST_DATA_DIR . '/html/login-page.html'));

        $this->assertInstanceOf(MainLoginParameters::class, $mainLoginParameters);
        $this->assertSame('xxxyyyzzzfff', $mainLoginParameters->getFKey());
        $this->assertSame('head', $mainLoginParameters->getSsrc());
    }

    public function testParseThrowsWhenFKeyCanNotBeFound(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('Could not find the "fkey input" element on the page');

        (new LogInPage())->parse(file_get_contents(TEST_DATA_DIR . '/html/login-page-without-fkey.html'));
    }

    public function testParseThrowsWhenSsrcCanNotBeFound(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('Could not find the "ssrc input" element on the page');

        (new LogInPage())->parse(file_get_contents(TEST_DATA_DIR . '/html/login-page-without-ssrc.html'));
    }
}
