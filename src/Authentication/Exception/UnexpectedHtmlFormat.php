<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Exception;

use AsyncBot\DriverStackOverflowChat\Authentication\Exception\Authentication;

class UnexpectedHtmlFormat extends Authentication
{
    public function __construct(string $element)
    {
        parent::__construct(sprintf('Could not find the "%s" element on the page', $element));
    }
}
