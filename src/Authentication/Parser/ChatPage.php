<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatLoginParameters;
use function Room11\DOMUtils\domdocument_load_html;

final class ChatPage
{
    public function parse(string $html): ChatLoginParameters
    {
        $dom = domdocument_load_html($html);

        $xpath = new \DOMXPath($dom);

        return new ChatLoginParameters($this->getFKeyValue($xpath));
    }

    private function getFKeyValue(\DOMXPath $xpath): string
    {
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->evaluate('//input[@id="fkey"]');

        if ($nodeList->length !== 1) {
            throw new UnexpectedHtmlFormat('fkey input');
        }

        return $nodeList->item(0)->getAttribute('value');
    }
}
