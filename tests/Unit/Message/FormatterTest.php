<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Message;

use AsyncBot\Core\Message\Node\Attribute;
use AsyncBot\Core\Message\Node\BlockQuote;
use AsyncBot\Core\Message\Node\Bold;
use AsyncBot\Core\Message\Node\Code;
use AsyncBot\Core\Message\Node\Italic;
use AsyncBot\Core\Message\Node\ListItem;
use AsyncBot\Core\Message\Node\Mention;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Node\OrderedList;
use AsyncBot\Core\Message\Node\Strikethrough;
use AsyncBot\Core\Message\Node\Text;
use AsyncBot\Core\Message\Node\UnorderedList;
use AsyncBot\Core\Message\Node\Url;
use AsyncBot\Driver\StackOverflowChat\Message\Formatter;
use PHPUnit\Framework\TestCase;

final class FormatterTest extends TestCase
{
    private Message $message;

    private Formatter $formatter;

    public function setUp(): void
    {
        $this->message   = new Message();
        $this->formatter = new Formatter();
    }

    public function testFormatAddsReplyTo(): void
    {
        $this->message->addAttribute(new Attribute('type', 'stackoverflow'));
        $this->message->addAttribute(new Attribute('replyTo', '123456'));

        $this->assertSame(':123456', $this->formatter->format($this->message));
    }

    public function testFormatTrims(): void
    {
        $this->message->appendNode(new Text(' test '));

        $this->assertSame('test', $this->formatter->format($this->message));
    }

    public function testFormatFormatsText(): void
    {
        $this->message->appendNode(new Text('test'));

        $this->assertSame('test', $this->formatter->format($this->message));
    }

    public function testFormatFormatsBold(): void
    {
        $boldNode = new Bold();

        $boldNode->appendNode(new Text('test'));

        $this->message->appendNode($boldNode);

        $this->assertSame('**test**', $this->formatter->format($this->message));
    }

    public function testFormatFormatsBlockQuote(): void
    {
        $blockQuoteNode = new BlockQuote();

        $blockQuoteNode->appendNode(new Text('test'));

        $this->message->appendNode($blockQuoteNode);

        $this->assertSame('> test', $this->formatter->format($this->message));
    }

    public function testFormatFormatsCode(): void
    {
        $codeNode = new Code();

        $codeNode->appendNode(new Text('test'));

        $this->message->appendNode($codeNode);

        $this->assertSame('`test`', $this->formatter->format($this->message));
    }

    public function testFormatFormatsItalic(): void
    {
        $italicNode = new Italic();

        $italicNode->appendNode(new Text('test'));

        $this->message->appendNode($italicNode);

        $this->assertSame('_test_', $this->formatter->format($this->message));
    }

    public function testFormatFormatsMention(): void
    {
        $mentionNode = new Mention('stackoverflow', 'peehaa');

        $this->message->appendNode($mentionNode);

        $this->assertSame('@peehaa', $this->formatter->format($this->message));
    }

    public function testFormatFormatsOrderedList(): void
    {
        $orderedListNode = new OrderedList();

        $lisItemNode1 = new ListItem();
        $lisItemNode2 = new ListItem();

        $lisItemNode1->appendNode(new Text('item 1'));
        $lisItemNode2->appendNode(new Text('item 2'));

        $orderedListNode->appendNode($lisItemNode1);
        $orderedListNode->appendNode($lisItemNode2);

        $this->message->appendNode($orderedListNode);

        $this->assertSame("1. item 1\r\n2. item 2", $this->formatter->format($this->message));
    }

    public function testFormatFormatsStrikethrough(): void
    {
        $strikethroughNode = new Strikethrough();

        $strikethroughNode->appendNode(new Text('test'));

        $this->message->appendNode($strikethroughNode);

        $this->assertSame('---test---', $this->formatter->format($this->message));
    }

    public function testFormatFormatsUnorderedList(): void
    {
        $unorderedListNode = new UnorderedList();

        $lisItemNode1 = new ListItem();
        $lisItemNode2 = new ListItem();

        $lisItemNode1->appendNode(new Text('item 1'));
        $lisItemNode2->appendNode(new Text('item 2'));

        $unorderedListNode->appendNode($lisItemNode1);
        $unorderedListNode->appendNode($lisItemNode2);

        $this->message->appendNode($unorderedListNode);

        $this->assertSame("- item 1\r\n- item 2", $this->formatter->format($this->message));
    }

    public function testFormatFormatsUrl(): void
    {
        $urlNode = new Url('https://example.com');

        $urlNode->appendNode(new Text('test'));

        $this->message->appendNode($urlNode);

        $this->assertSame('[test](https://example.com)', $this->formatter->format($this->message));
    }
}
