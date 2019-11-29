<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication;

use Amp\Http\Client\HttpClientBuilder;
use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\UserRetriever;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient\ResponseInterceptor;
use PHPUnit\Framework\TestCase;
use function Amp\Promise\wait;
use function Room11\DOMUtils\domdocument_load_html;

class UserRetrieverTest extends TestCase
{
    public function testRetrieveThrowsWhenTheActiveUserElementCanNotBeFound(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('active user');

        $userRetriever = new UserRetriever(
            (new HttpClientBuilder())->build(),
        );

        wait($userRetriever->retrieve(
            domdocument_load_html(file_get_contents(TEST_DATA_DIR . '/html/chat-page-without-active-user.html')),
        ));
    }

    public function testRetrieveThrowsWhenTheActiveUserElementDoesNotHaveAClassAttribute(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('active user class');

        $userRetriever = new UserRetriever(
            (new HttpClientBuilder())->build(),
        );

        wait($userRetriever->retrieve(
            domdocument_load_html(file_get_contents(TEST_DATA_DIR . '/html/chat-page-without-class-attribute-on-active-user.html')),
        ));
    }

    public function testRetrieveThrowsWhenTheActiveUserElementHasNoValidUserIdClass(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('active user class');

        $userRetriever = new UserRetriever(
            (new HttpClientBuilder())->build(),
        );

        wait($userRetriever->retrieve(
            domdocument_load_html(file_get_contents(TEST_DATA_DIR . '/html/chat-page-with-invalid-active-user-class.html')),
        ));
    }

    public function testRetrieveReturnsChatUser(): void
    {
        $userRetriever = new UserRetriever(
            (new HttpClientBuilder())->intercept(
                new ResponseInterceptor(file_get_contents(TEST_DATA_DIR . '/html/chat-user-page.html')),
            )->build(),
        );

        $chatUser = wait($userRetriever->retrieve(
            domdocument_load_html(file_get_contents(TEST_DATA_DIR . '/html/chat-page.html')),
        ));

        $this->assertInstanceOf(ChatUser::class, $chatUser);
    }
}
