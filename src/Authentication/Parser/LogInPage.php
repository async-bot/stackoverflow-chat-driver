<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\MainLoginParameters;
use function Room11\DOMUtils\domdocument_load_html;

final class LogInPage
{
    public function parse(string $html): MainLoginParameters
    {
        $dom = domdocument_load_html($html);

        $xpath = new \DOMXPath($dom);

        return new MainLoginParameters($this->getFKeyValue($xpath), $this->getSsrcValue($xpath));
    }

    private function getFKeyValue(\DOMXPath $xpath): string
    {
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->evaluate('//form[@id="login-form"]/input[@name="fkey"]');

        if ($nodeList->length !== 1) {
            throw new UnexpectedHtmlFormat('fkey input');
        }

        return $nodeList->item(0)->getAttribute('value');
    }

    private function getSsrcValue(\DOMXPath $xpath): string
    {
        /** @var \DOMNodeList $nodeList */
        $nodeList = $xpath->evaluate('//form[@id="login-form"]/input[@name="ssrc"]');

        if ($nodeList->length !== 1) {
            throw new UnexpectedHtmlFormat('ssrc input');
        }

        return $nodeList->item(0)->getAttribute('value');
    }
}
