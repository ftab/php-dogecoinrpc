<?php

namespace ftab\Dogecoin\Tests;

use ftab\Dogecoin\Client as DogecoinClient;
use ftab\Dogecoin\Config;
use ftab\Dogecoin\Exceptions;
use ftab\Dogecoin\Responses\DogecoindResponse;
use ftab\Dogecoin\Responses\Response;
use GuzzleHttp\Client as GuzzleHttp;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ClientTest extends TestCase
{
    /**
     * Set-up test environment.
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->dogecoind = new DogecoinClient();
    }

    /**
     * Test client getter and setter.
     *
     * @return void
     */
    public function testClientSetterGetter() : void
    {
        $dogecoind = new DogecoinClient('http://old_client.org');
        $this->assertInstanceOf(DogecoinClient::class, $dogecoind);

        $base_uri = $dogecoind->getClient()->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'old_client.org');

        $oldClient = $dogecoind->getClient();
        $this->assertInstanceOf(GuzzleHttp::class, $oldClient);

        $newClient = new GuzzleHttp(['base_uri' => 'http://new_client.org']);
        $dogecoind->setClient($newClient);

        $base_uri = $dogecoind->getClient()->getConfig('base_uri');
        $this->assertEquals($base_uri->getHost(), 'new_client.org');
    }

    /**
     * Test preserve method name case config option.
     *
     * @return void
     */
    public function testPreserveCaseOption() : void
    {
        $dogecoind = new DogecoinClient(['preserve_case' => true]);
        $dogecoind->setClient($this->mockGuzzle([$this->getBlockResponse()]));
        $dogecoind->getBlockHeader();

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getBlockHeader',
            $request['id']
        ), $request);
    }

    /**
     * Test client config getter.
     *
     * @return void
     */
    public function testGetConfig() : void
    {
        $this->assertInstanceOf(Config::class, $this->dogecoind->getConfig());
    }

    /**
     * Test simple request.
     *
     * @return void
     */
    public function testRequest() : void
    {
        $response = $this->dogecoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->request(
                'getblockheader',
                '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
            );

        $request = $this->getHistoryRequestBody();

        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
        ), $request);
        $this->assertEquals(self::$getBlockResponse, $response->get());
    }

    /**
     * Test async request.
     *
     * @return void
     */
    public function testAsyncRequest() : void
    {
        $onFulfilled = $this->mockCallable([
            $this->callback(function (DogecoindResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->requestAsync(
                'getblockheader',
                '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->dogecoind->wait();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testMagic() : void
    {
        $response = $this->dogecoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->getBlockHeader(
                '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
            );

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
        ), $request);
    }

    /**
     * Test magic request.
     *
     * @return void
     */
    public function testAsyncMagic() : void
    {
        $onFulfilled = $this->mockCallable([
            $this->callback(function (DogecoindResponse $response) {
                return $response->get() == self::$getBlockResponse;
            }),
        ]);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->getBlockResponse()]))
            ->getBlockHeaderAsync(
                '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691',
                function ($response) use ($onFulfilled) {
                    $onFulfilled($response);
                }
            );

        $this->dogecoind->wait();

        $request = $this->getHistoryRequestBody();
        $this->assertEquals($this->makeRequestBody(
            'getblockheader',
            $request['id'],
            '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
        ), $request);
    }

    /**
     * Test dogecoind exception.
     *
     * @return void
     */
    public function testDogecoindException() : void
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->rawTransactionError(200)]))
            ->getRawTransaction(
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69'
            );
    }

    /**
     * Test request exception with error code.
     *
     * @return void
     */
    public function testRequestExceptionWithServerErrorCode() : void
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->rawTransactionError(200)]))
            ->getRawTransaction(
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69'
            );
    }

    /**
     * Test request exception with empty response body.
     *
     * @return void
     */
    public function testRequestExceptionWithEmptyResponseBody() : void
    {
        $this->expectException(Exceptions\ConnectionException::class);
        $this->expectExceptionMessage($this->error500());
        $this->expectExceptionCode(500);

        $this->dogecoind
            ->setClient($this->mockGuzzle([new GuzzleResponse(500)]))
            ->getRawTransaction(
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69'
            );
    }

    /**
     * Test async request exception with empty response body.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithEmptyResponseBody() : void
    {
        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == $this->error500() &&
                    $exception->getCode() == 500;
            }),
        ]);

        $this->dogecoind
            ->setClient($this->mockGuzzle([new GuzzleResponse(500)]))
            ->requestAsync(
                'getrawtransaction',
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->dogecoind->wait();
    }

    /**
     * Test request exception with response.
     *
     * @return void
     */
    public function testRequestExceptionWithResponseBody() : void
    {
        $this->expectException(Exceptions\BadRemoteCallException::class);
        $this->expectExceptionMessage(self::$rawTransactionError['message']);
        $this->expectExceptionCode(self::$rawTransactionError['code']);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithResponse()]))
            ->getRawTransaction(
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69'
            );
    }

    /**
     * Test async request exception with response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithResponseBody() : void
    {
        $onRejected = $this->mockCallable([
            $this->callback(function (Exceptions\BadRemoteCallException $exception) {
                return $exception->getMessage() == self::$rawTransactionError['message'] &&
                    $exception->getCode() == self::$rawTransactionError['code'];
            }),
        ]);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithResponse()]))
            ->requestAsync(
                'getrawtransaction',
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69',
                null,
                function ($exception) use ($onRejected) {
                    $onRejected($exception);
                }
            );

        $this->dogecoind->wait();
    }

    /**
     * Test request exception with no response.
     *
     * @return void
     */
    public function testRequestExceptionWithNoResponseBody() : void
    {
        $this->expectException(Exceptions\ClientException::class);
        $this->expectExceptionMessage('test');
        $this->expectExceptionCode(0);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithoutResponse()]))
            ->getRawTransaction(
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69'
            );
    }

    /**
     * Test async request exception with no response.
     *
     * @return void
     */
    public function testAsyncRequestExceptionWithNoResponseBody() : void
    {
        $rejected = $this->mockCallable([
            $this->callback(function (Exceptions\ClientException $exception) {
                return $exception->getMessage() == 'test' &&
                    $exception->getCode() == 0;
            }),
        ]);

        $this->dogecoind
            ->setClient($this->mockGuzzle([$this->requestExceptionWithoutResponse()]))
            ->requestAsync(
                'getrawtransaction',
                '5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69',
                null,
                function ($exception) use ($rejected) {
                    $rejected($exception);
                }
            );

        $this->dogecoind->wait();
    }

    /**
     * Test setting different response handler class.
     *
     * @return void
     */
    public function testSetResponseHandler() : void
    {
        $fake = new FakeClient();

        $guzzle = $this->mockGuzzle([
            $this->getBlockResponse(),
        ], $fake->getClient()->getConfig('handler'));

        $response = $fake
            ->setClient($guzzle)
            ->request(
                'getblockheader',
                '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691'
            );

        $this->assertInstanceOf(FakeResponse::class, $response);
    }
}

class FakeClient extends DogecoinClient
{
    /**
     * Gets response handler class name.
     *
     * @return string
     */
    protected function getResponseHandler() : string
    {
        return 'ftab\\Dogecoin\\Tests\\FakeResponse';
    }
}

class FakeResponse extends Response
{
    //
}
