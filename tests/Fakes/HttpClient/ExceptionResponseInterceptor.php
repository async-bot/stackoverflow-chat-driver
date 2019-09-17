<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient;

use Amp\CancellationToken;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\Client;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;

final class ExceptionResponseInterceptor implements ApplicationInterceptor
{
    private \Throwable $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     *
     * @return Promise<Response>
     */
    public function request(Request $request, CancellationToken $cancellation, Client $client): Promise
    {
        throw new $this->exception();
    }
}
