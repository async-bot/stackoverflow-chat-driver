<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Exception;

final class InvalidRoomUrl extends Authentication
{
    public function __construct(string $url)
    {
        parent::__construct(sprintf('%s is not recognized as a valid Stack Overflow room URL', $url));
    }
}
