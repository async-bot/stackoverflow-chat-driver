<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChat\Authentication\Exception;

use Amp\Http\Client\Request;

class NetworkError extends Authentication
{
    public function __construct(Request $request, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Network error when requesting %s over %s', $request->getUri(), $request->getMethod()),
            $code,
            $previous,
        );
    }
}
