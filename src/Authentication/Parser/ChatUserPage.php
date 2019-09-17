<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Parser;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\ChatUser;
use function Room11\DOMUtils\domdocument_load_html;
use function Room11\DOMUtils\xpath_html_class;

final class ChatUserPage
{
    public function parse(int $userId, string $html): ChatUser
    {
        $dom = domdocument_load_html($html);

        $xpath = new \DOMXPath($dom);

        return new ChatUser($userId, $this->getUsername($xpath));
    }

    private function getUsername(\DOMXPath $xpath): string
    {
        /** @var \DOMNodeList $nodes */
        $nodes = $xpath->evaluate('//*[' . xpath_html_class('user-status') . ']');

        if (!$nodes->length) {
            throw new UnexpectedHtmlFormat('user status');
        }

        return $nodes->item(0)->textContent;
    }
}
