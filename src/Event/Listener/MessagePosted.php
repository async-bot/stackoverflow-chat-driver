<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Event\Listener;

use Amp\Promise;
use AsyncBot\Driver\StackOverflowChat\Event\Data\Message;

interface MessagePosted
{
    /**
     * @return Promise<null>
     */
    public function __invoke(Message $message): Promise;
}
