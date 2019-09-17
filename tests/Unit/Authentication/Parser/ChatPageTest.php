<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\ChatPage;
use PHPUnit\Framework\TestCase;
use function Room11\DOMUtils\domdocument_load_html;

class ChatPageTest extends TestCase
{
    public function testParseReturnsCorrectFKey(): void
    {
        $this->assertSame(
            'xxxyyyzzzfff',
            (new ChatPage())->parse(domdocument_load_html(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html'))),
        );
    }

    public function testParseThrowsWhenFKeyElementIsNotInTheHtml(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('Could not find the "fkey input" element on the page');

        (new ChatPage())->parse(
            domdocument_load_html(file_get_contents(TEST_DATA_DIR . '/html/chat-page-without-fkey.html')),
        );
    }
}
