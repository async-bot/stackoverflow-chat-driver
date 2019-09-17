<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;

final class ChatPage
{
    public function parse(\DOMDocument $dom): string
    {
        return $this->getFKeyValue($dom);
    }

    private function getFKeyValue(\DOMDocument $dom): string
    {
        $fKeyElement = $dom->getElementById('fkey');

        if ($fKeyElement === null) {
            throw new UnexpectedHtmlFormat('fkey input');
        }

        return $fKeyElement->getAttribute('value');
    }
}
