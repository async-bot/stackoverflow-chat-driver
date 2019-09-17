<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Unit\Authentication\ValueObject;

use AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject\MainLoginParameters;
use PHPUnit\Framework\TestCase;

class MainLoginParametersTest extends TestCase
{
    private MainLoginParameters $mainLoginParameters;

    public function setUp(): void
    {
        $this->mainLoginParameters = new MainLoginParameters('xxyyzzff', 'head');
    }

    public function testGetFKey(): void
    {
        $this->assertSame('xxyyzzff', $this->mainLoginParameters->getFKey());
    }

    public function testGetSsrc(): void
    {
        $this->assertSame('head', $this->mainLoginParameters->getSsrc());
    }
}
