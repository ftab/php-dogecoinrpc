<?php

declare(strict_types=1);

namespace ftab\Dogecoin;

use ftab\Dogecoin\Exceptions\BadConfigurationException;
use ftab\Dogecoin\Exceptions\Handler as ExceptionHandler;

if (!function_exists('to_dogecoin')) {
    /**
     * Converts from dogetoshi to dogecoin.
     *
     * @param int $dogetoshi
     *
     * @return string
     */
    function to_dogecoin(int $dogetoshi) : string
    {
        return bcdiv((string) $dogetoshi, (string) 1e8, 8);
    }
}

if (!function_exists('to_dogetoshi')) {
    /**
     * Converts from dogecoin to dogetoshi.
     *
     * @param string|float $dogecoin
     *
     * @return string
     */
    function to_dogetoshi($dogecoin) : string
    {
        return bcmul(to_fixed($dogecoin, 8), (string) 1e8);
    }
}

if (!function_exists('to_fixed')) {
    /**
     * Brings number to fixed precision without rounding.
     *
     * @param string $number
     * @param int    $precision
     *
     * @return string
     */
    function to_fixed(string $number, int $precision = 8) : string
    {
        $number = bcmul($number, (string) pow(10, $precision));

        return bcdiv($number, (string) pow(10, $precision), $precision);
    }
}

if (!function_exists('split_url')) {
    /**
     * Splits url into parts.
     *
     * @param string $url
     *
     * @return array
     */
    function split_url(string $url) : array
    {
        $allowed = ['scheme', 'host', 'port', 'user', 'pass'];

        $parts = (array) parse_url($url);
        $parts = array_intersect_key($parts, array_flip($allowed));

        if (!$parts || empty($parts)) {
            throw new BadConfigurationException(
                ['url' => $url],
                'Invalid url'
            );
        }

        return $parts;
    }
}

if (!function_exists('exception')) {
    /**
     * Gets exception handler instance.
     *
     * @return \ftab\Dogecoin\Exceptions\Handler
     */
    function exception() : ExceptionHandler
    {
        return ExceptionHandler::getInstance();
    }
}

set_exception_handler([ExceptionHandler::getInstance(), 'handle']);
