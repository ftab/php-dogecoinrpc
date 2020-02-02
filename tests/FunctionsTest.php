<?php

namespace ftab\Dogecoin\Tests;

use ftab\Dogecoin;
use ftab\Dogecoin\Exceptions\BadConfigurationException;
use ftab\Dogecoin\Exceptions\Handler as ExceptionHandler;

class FunctionsTest extends TestCase
{
    /**
     * Test dogetoshi to dogecoin converter.
     *
     * @param int    $dogetoshi
     * @param string $dogecoin
     *
     * @return void
     *
     * @dataProvider dogetoshiDogeProvider
     */
    public function testToBtc(int $dogetoshi, string $dogecoin) : void
    {
        $this->assertEquals($dogecoin, Dogecoin\to_dogecoin($dogetoshi));
    }

    /**
     * Test dogecoin to dogetoshi converter.
     *
     * @param int    $dogetoshi
     * @param string $dogecoin
     *
     * @return void
     *
     * @dataProvider dogetoshiDogeProvider
     */
    public function testToDogetoshi(int $dogetoshi, string $dogecoin) : void
    {
        $this->assertEquals($dogetoshi, Dogecoin\to_dogetoshi($dogecoin));
    }

    /**
     * Test float to fixed converter.
     *
     * @param float  $float
     * @param int    $precision
     * @param string $expected
     *
     * @return void
     *
     * @dataProvider floatProvider
     */
    public function testToFixed(
        float $float,
        int $precision,
        string $expected
    ) : void {
        $this->assertSame($expected, Dogecoin\to_fixed($float, $precision));
    }

    /**
     * Test url parser.
     *
     * @param string      $url
     * @param string      $scheme
     * @param string      $host
     * @param int|null    $port
     * @param string|null $user
     * @param string|null $password
     *
     * @return void
     *
     * @dataProvider urlProvider
     */
    public function testSplitUrl(
        string $url,
        string $scheme,
        string $host,
        ?int $port,
        ?string $user,
        ?string $pass
    ) : void {
        $parts = Dogecoin\split_url($url);

        $this->assertEquals($parts['scheme'], $scheme);
        $this->assertEquals($parts['host'], $host);
        foreach (['port', 'user', 'pass'] as $part) {
            if (!is_null(${$part})) {
                $this->assertEquals($parts[$part], ${$part});
            }
        }
    }

    /**
     * Test url parser with invalid url.
     *
     * @return array
     */
    public function testSplitUrlWithInvalidUrl() : void
    {
        $this->expectException(BadConfigurationException::class);
        $this->expectExceptionMessage('Invalid url');

        Dogecoin\split_url('cookies!');
    }

    /**
     * Test exception handler helper.
     *
     * @return void
     */
    public function testExceptionHandlerHelper() : void
    {
        $this->assertInstanceOf(ExceptionHandler::class, Dogecoin\exception());
    }

    /**
     * Provides url strings and parts.
     *
     * @return array
     */
    public function urlProvider() : array
    {
        return [
            ['https://localhost', 'https', 'localhost', null, null, null],
            ['https://localhost:8000', 'https', 'localhost', 8000, null, null],
            ['http://localhost', 'http', 'localhost', null, null, null],
            ['http://localhost:8000', 'http', 'localhost', 8000, null, null],
            ['http://testuser@127.0.0.1:8000/', 'http', '127.0.0.1', 8000, 'testuser', null],
            ['http://testuser:testpass@localhost:8000', 'http', 'localhost', 8000, 'testuser', 'testpass'],
        ];
    }

    /**
     * Provides dogetoshi and dogecoin values.
     *
     * @return array
     */
    public function dogetoshiDogeProvider() : array
    {
        return [
            [1000, '0.00001000'],
            [2500, '0.00002500'],
            [-1000, '-0.00001000'],
            [100000000, '1.00000000'],
            [150000000, '1.50000000'],
            [2100000000000000, '21000000.00000000'],
        ];
    }

    /**
     * Provides float values with precision and result.
     *
     * @return array
     */
    public function floatProvider() : array
    {
        return [
            [1.2345678910, 0, '1'],
            [1.2345678910, 2, '1.23'],
            [1.2345678910, 4, '1.2345'],
            [1.2345678910, 8, '1.23456789'],
        ];
    }
}
