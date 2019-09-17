<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\MessageQueue;

use Amp\Promise;
use Amp\Success;

final class ArrayQueue implements Queue
{
    private array $messages = [];

    /**
     * @return Promise<null>
     */
    public function append(string $message): Promise
    {
        $this->messages[] = $message;

        return new Success();
    }

    /**
     * @return Promise<null>
     */
    public function prepend(string $message): Promise
    {
        array_unshift($this->messages, $message);

        return new Success();
    }

    /**
     * @return Promise<string|null>
     */
    public function get(): Promise
    {
        if (!$this->messages) {
            return new Success(null);
        }

        return new Success(array_shift($this->messages));
    }
}
