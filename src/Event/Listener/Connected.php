<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Event\Listener;

use Amp\Promise;

interface Connected
{
    /**
     * @return Promise<null>
     */
    public function __invoke(): Promise;
}
