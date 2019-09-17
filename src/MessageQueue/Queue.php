<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\MessageQueue;

use Amp\Promise;

interface Queue
{
    /**
     * @return Promise<null>
     */
    public function append(string $message): Promise;

    /**
     * @return Promise<null>
     */
    public function prepend(string $message): Promise;

    /**
     * @return Promise<string|null>
     */
    public function get(): Promise;
}
