<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Message\Exception;

use AsyncBot\Core\Message\Node\Node;
use AsyncBot\Driver\StackOverflowChat\Exception\Exception;

final class UnsupportedNode extends Exception
{
    public function __construct(Node $node)
    {
        parent::__construct(
            sprintf('Formatting of node %s has not been implemented (yet)', get_class($node)),
        );
    }
}
