<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Message;

use AsyncBot\Core\Message\Node\BlockQuote;
use AsyncBot\Core\Message\Node\Bold;
use AsyncBot\Core\Message\Node\Code;
use AsyncBot\Core\Message\Node\Italic;
use AsyncBot\Core\Message\Node\ListItem;
use AsyncBot\Core\Message\Node\Mention;
use AsyncBot\Core\Message\Node\Message;
use AsyncBot\Core\Message\Node\Node;
use AsyncBot\Core\Message\Node\OrderedList;
use AsyncBot\Core\Message\Node\Separator;
use AsyncBot\Core\Message\Node\Strikethrough;
use AsyncBot\Core\Message\Node\Tag;
use AsyncBot\Core\Message\Node\Text;
use AsyncBot\Core\Message\Node\UnorderedList;
use AsyncBot\Core\Message\Node\Url;
use AsyncBot\Driver\StackOverflowChat\Message\Exception\UnsupportedNode;

final class Formatter
{
    private const ZERO_WIDTH_JOINER = "\xE2\x80\x8D";

    public function format(Message $message): string
    {
        $formattedMessage = '';

        if ($message->hasAttribute('replyTo') && $message->getAttribute('type')->getValue() === 'stackoverflow') {
            $formattedMessage = sprintf(':%d ', $message->getAttribute('replyTo')->getValue());
        }

        foreach ($message->getChildren() as $index => $child) {
            if ($index === 0) {
                $formattedMessage .= ltrim($this->formatNode($child));

                continue;
            }

            $formattedMessage .= $this->formatNode($child);
        }

        return rtrim($formattedMessage);
    }

    private function formatNode(Node $node): string
    {
        switch (get_class($node)) {
            case Text::class:
                /** @var Text $node */
                return $this->formatText($node);

            case Bold::class:
                /** @var Bold $node */
                return $this->formatBold($node);

            case BlockQuote::class:
                /** @var BlockQuote $node */
                return $this->formatBlockquote($node);

            case Code::class:
                /** @var Code $node */
                return $this->formatCode($node);

            case Italic::class:
                /** @var Italic $node */
                return $this->formatItalic($node);

            case Mention::class:
                /** @var Mention $node */
                return $this->formatMention($node);

            case OrderedList::class:
                /** @var OrderedList $node */
                return $this->formatOrderedList($node);

            case Strikethrough::class:
                /** @var Strikethrough $node */
                return $this->formatStrikethrough($node);

            case UnorderedList::class:
                /** @var UnorderedList $node */
                return $this->formatUnorderedList($node);

            case Url::class:
                /** @var Url $node */
                return $this->formatUrl($node);

            case Tag::class:
                /** @var Tag $node */
                return $this->formatTag($node);

            case Separator::class:
                return $this->formatSeparator();

            default:
                throw new UnsupportedNode($node);
        }
    }

    private function formatChildren(Node $parentNode): string
    {
        $content = array_map(fn (Node $childNode) => $this->formatNode($childNode), $parentNode->getChildren());

        return implode('', $content);
    }

    private function formatText(Text $textNode): string
    {
        return preg_replace('~(@)([[:alnum:]]+)~u', '\1' . self::ZERO_WIDTH_JOINER . '\2', $textNode->toString());
    }

    private function formatBold(Bold $boldNode): string
    {
        return sprintf('**%s**', trim($this->formatChildren($boldNode)));
    }

    private function formatBlockquote(BlockQuote $blockQuoteNode): string
    {
        return sprintf('> %s', trim($this->formatChildren($blockQuoteNode)));
    }

    private function formatCode(Code $codeNode): string
    {
        return sprintf('`%s`', trim($this->formatChildren($codeNode)));
    }

    private function formatItalic(Italic $italicNode): string
    {
        return sprintf('_%s_', trim($this->formatChildren($italicNode)));
    }

    private function formatMention(Mention $mentionNode): string
    {
        return sprintf('@%s', $mentionNode->getAttribute('id')->getValue());
    }

    private function formatOrderedList(OrderedList $orderedListNode): string
    {
        $items = [];

        foreach ($orderedListNode->getChildren() as $index => $listItem) {
            $items[] = sprintf("%d. %s\r\n", ($index + 1), $this->formatChildren($listItem));
        }

        return implode('', $items);
    }

    private function formatStrikethrough(Strikethrough $strikethroughNode): string
    {
        return sprintf('---%s---', trim($this->formatChildren($strikethroughNode)));
    }

    private function formatUnorderedList(UnorderedList $unorderedListNode): string
    {
        $items = array_map(
            fn (ListItem $listItem) => sprintf("- %s\r\n", $this->formatChildren($listItem)),
            $unorderedListNode->getChildren(),
        );

        return implode('', $items);
    }

    private function formatUrl(Url $urlNode): string
    {
        return sprintf('[%s](%s)', $this->formatChildren($urlNode), $urlNode->getAttribute('href')->getValue());
    }

    private function formatTag(Tag $tagNode): string
    {
        if (!$tagNode->hasAttribute('type')) {
            return sprintf('[tag:%s]', trim($this->formatChildren($tagNode)));
        }

        return sprintf('[tag-%s:%s]', $tagNode->getAttribute('type'), trim($this->formatChildren($tagNode)));
    }

    private function formatSeparator(): string
    {
        return ' ・ ';
    }
}
