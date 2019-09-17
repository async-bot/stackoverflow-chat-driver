<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Exception;

final class NotConnected extends Exception
{
    public function __construct()
    {
        parent::__construct('Bot did not connect to Stack Overflow yet');
    }
}
