<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Event\Data;

final class Message
{
    private int $id;

    private Room $room;

    private User $user;

    private string $content;

    private \DateTimeImmutable $timestamp;

    public function __construct(int $id, Room $room, User $user, string $content, \DateTimeImmutable $timestamp)
    {
        $this->id        = $id;
        $this->room      = $room;
        $this->user      = $user;
        $this->content   = $content;
        $this->timestamp = $timestamp;
    }

    /**
     * @param array<string,mixed> $message
     */
    public static function fromWebSocketMessage(array $message): self
    {
        return new self(
            $message['message_id'],
            new Room($message['room_id'], $message['room_name']),
            new User($message['user_id'], $message['user_name']),
            $message['content'],
            new \DateTimeImmutable('@' . $message['time_stamp']),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getRoom(): Room
    {
        return $this->room;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }
}
