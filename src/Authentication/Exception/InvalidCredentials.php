<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Exception;

class InvalidCredentials extends Authentication
{
    public function __construct()
    {
        parent::__construct('Could not authenticate with StackOverflow');
    }
}
