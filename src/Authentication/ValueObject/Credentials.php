<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\ValueObject;

use AsyncBot\Driver\StackOverflowChat\Authentication\Exception\InvalidRoomUrl;

final class Credentials
{
    private string $emailAddress;

    private string $password;

    private int $roomId;

    private string $roomSlug;

    public function __construct(string $emailAddress, string $password, string $roomUrl)
    {
        $this->emailAddress = $emailAddress;
        $this->password     = $password;

        if (!preg_match('~^https://chat\.stackoverflow\.com/rooms/(?P<id>\d+)/(?P<slug>[^/ ]+)$~i', $roomUrl, $matches)) {
            throw new InvalidRoomUrl($roomUrl);
        }

        $this->roomId   = (int) $matches['id'];
        $this->roomSlug = $matches['slug'];
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoomId(): int
    {
        return $this->roomId;
    }

    public function getRoomSlug(): string
    {
        return $this->roomSlug;
    }
}
