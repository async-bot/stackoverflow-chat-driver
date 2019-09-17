<?php declare(strict_types=1);

namespace AsyncBot\Driver\StackOverflowChatTest\Fakes\HttpClient;

use Amp\ByteStream\InputStream;
use Amp\CancellationToken;
use Amp\Http\Client\ApplicationInterceptor;
use Amp\Http\Client\Client;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Amp\Success;
use PHPUnit\Framework\MockObject\Generator;

final class ResponseInterceptor implements ApplicationInterceptor
{
    private string $body;

    private int $statusCode;

    /** @var array<string,string> */
    private array $headers;

    /**
     * @param array<string,string> $headers
     */
    public function __construct(string $body, int $statusCode = 200, array $headers = [])
    {
        $this->body       = $body;
        $this->statusCode = $statusCode;
        $this->headers    = $headers;
    }

    /**
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     *
     * @return Promise<Response>
     */
    public function request(Request $request, CancellationToken $cancellation, Client $client): Promise
    {
        // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        $body = (new Generator())->getMock(InputStream::class);

        $body
            ->method('read')
            ->willReturnOnConsecutiveCalls(new Success($this->body), new Success(null))
        ;

        $response = new Response('2.0', $this->statusCode, 'OK', [], $body, $request);

        foreach ($this->headers as $key => $value) {
            $response->addHeader($key, $value);
        }

        return new Success($response);
    }
}
