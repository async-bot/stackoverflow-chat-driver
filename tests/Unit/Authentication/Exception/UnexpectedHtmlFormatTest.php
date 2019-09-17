<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\Exception;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\UnexpectedHtmlFormat;
use PHPUnit\Framework\TestCase;

class UnexpectedHtmlFormatTest extends TestCase
{
    public function testConstructorPassesTheCorrectMessage(): void
    {
        $this->expectException(UnexpectedHtmlFormat::class);
        $this->expectExceptionMessage('Could not find the "TEST" element on the page');

        throw new UnexpectedHtmlFormat('TEST');
    }
}
