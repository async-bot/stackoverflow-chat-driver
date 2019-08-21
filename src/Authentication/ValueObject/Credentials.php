<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

final class Credentials
{
    private string $emailAddress;

    private string $password;

    public function __construct(string $emailAddress, string $password)
    {
        $this->emailAddress = $emailAddress;
        $this->password     = $password;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
