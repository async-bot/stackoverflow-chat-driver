<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\Parser\ChatUserPage;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use PHPUnit\Framework\TestCase;

class ChatUserPageTest extends TestCase
{
    public function testParseReturnsChatUser(): void
    {
        $chatUser = (new ChatUserPage())->parse(13, file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html'));

        $this->assertInstanceOf(ChatUser::class, $chatUser);
        $this->assertSame(13, $chatUser->getId());
        $this->assertSame('AsyncBot', $chatUser->getUsername());
    }

    public function testParseThrowsWhenUserStatusElementCanNotBeFound(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('Could not find the "user status" element on the page');

        (new ChatUserPage())->parse(
            13,
            file_get_contents(TEST_DATA_DIR . '/html/chat-user-page-without-user-status.html'),
        );
    }
}
