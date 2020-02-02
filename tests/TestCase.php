<?php

namespace ftab\Dogecoin\Tests;

use ftab\Dogecoin\Responses\DogecoindResponse;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use stdClass;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    protected function setUp() : void
    {
        parent::setUp();

        $this->history = [];
    }

    /**
     * Block header response.
     *
     * @var array
     */
    protected static $getBlockResponse = [
        'hash'          => '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691',
        'confirmations' => 3088105,
		'strippedsize'  => 224,
		'size'          => 224,
		'weight'        => 896,
        'height'        => null,
        'version'       => 1,
        'versionHex'    => '00000001',
        'merkleroot'    => '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69',
        'time'          => 1386325540,
        'mediantime'    => 1386325540,
        'nonce'         => 99943,
        'bits'          => '1e0ffff0',
        'difficulty'    => 0.000244140625,
        'chainwork'     => '0000000000000000000000000000000000000000000000000000000000100010',
        'nextblockhash' => '82bc68038f6034c0596b6e313729793a887fded6e92a31fbdf70863f89d9bea2',
        'tx'            => [
            '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69',
            null,
        ],
        'test1'         => [
            'test2' => [
                'test4' => [
                    'amount' => 3,
                ],
            ],
            'test3' => [
                'test5' => [
                    'amount' => 4,
                ],
            ],
        ],
    ];

    /**
     * Transaction error response.
     *
     * @var array
     */
    protected static $rawTransactionError = [
        'code'    => -5,
        'message' => 'No information available about transaction',
    ];

    /**
     * Balance response.
     *
     * @var float
     */
    protected static $balanceResponse = 0.1;

    /**
     * Get error 500 message.
     *
     * @return string
     */
    protected function error500() : string
    {
        return 'Server error: `POST /` '.
            'resulted in a `500 Internal Server Error` response';
    }

    /**
     * Get Closure mock.
     *
     * @param array $with
     *
     * @return callable
     */
    protected function mockCallable(array $with = []) : callable
    {
        $callable = $this->getMockBuilder(stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();

        $callable->expects($this->once())
            ->method('__invoke')
            ->with(...$with);

        return $callable;
    }

    /**
     * Get Guzzle mock client.
     *
     * @param array                    $queue
     * @param \GuzzleHttp\HandlerStack $handler
     *
     * @return \GuzzleHttp\Client
     */
    protected function mockGuzzle(
        array $queue = [],
        HandlerStack $handler = null
    ) : GuzzleClient {
        $handler = $handler ?: $this->dogecoind->getClient()->getConfig('handler');

        if ($handler) {
            $middleware = Middleware::history($this->history);
            $handler->push($middleware);
            $handler->setHandler(new MockHandler($queue));
        }

        return new GuzzleClient([
            'handler' => $handler,
        ]);
    }

    /**
     * Make block header response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getBlockResponse(int $code = 200) : ResponseInterface
    {
        $json = json_encode([
            'result' => self::$getBlockResponse,
            'error'  => null,
            'id'     => 0,
        ]);

        return new Response($code, [], $json);
    }

    /**
     * Get getbalance command response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getBalanceResponse(int $code = 200) : ResponseInterface
    {
        $json = json_encode([
            'result' => self::$balanceResponse,
            'error'  => null,
            'id'     => 0,
        ]);

        return new Response($code, [], $json);
    }

    /**
     * Make raw transaction error response.
     *
     * @param int $code
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function rawTransactionError(int $code = 500) : ResponseInterface
    {
        $json = json_encode([
            'result' => null,
            'error'  => self::$rawTransactionError,
            'id'     => 0,
        ]);

        return new Response($code, [], $json);
    }

    /**
     * Return exception with response.
     *
     * @return callable
     */
    protected function requestExceptionWithResponse() : callable
    {
        $exception = function ($request) {
            return new RequestException(
                'test',
                $request,
                new DogecoindResponse($this->rawTransactionError())
            );
        };

        return $exception;
    }

    /**
     * Return exception without response.
     *
     * @return callable
     */
    protected function requestExceptionWithoutResponse() : callable
    {
        $exception = function ($request) {
            return new RequestException('test', $request);
        };

        return $exception;
    }

    /**
     * Make request body.
     *
     * @param string $method
     * @param int    $id
     * @param mixed  $params,...
     *
     * @return array
     */
    protected function makeRequestBody(
        string $method,
        int $id,
        ...$params
    ) : array {
        return [
            'method' => $method,
            'params' => (array) $params,
            'id'     => $id,
        ];
    }

    /**
     * Get request url from history.
     *
     * @param int $index
     *
     * @return \Psr\Http\Message\UriInterface|null
     */
    protected function getHistoryRequestUri(int $index = 0) : ?UriInterface
    {
        if (isset($this->history[$index])) {
            return $this->history[$index]['request']->getUri();
        }
    }

    /**
     * Get request body from history.
     *
     * @param int $index
     *
     * @return mixed
     */
    protected function getHistoryRequestBody(int $index = 0)
    {
        if (isset($this->history[$index])) {
            return json_decode(
                $this->history[$index]['request']->getBody()->getContents(),
                true
            );
        }
    }
}
