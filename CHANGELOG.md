# Changelog

## 3.0.2 - 2020/03/01

Add unconfirmed_balance and immature_balance to the keys that are stringified (walletinfo)

## 3.0.1 - 2020/02/16

Negative numbers need some love too

## 3.0.0 - 2020/02/16

**BREAKING CHANGE:** Using strings instead of floats for amount, fee, and balance to avoid accuracy issues
* Put quotes around JSON values for these keys to force `json_decode()` to process it as a string
* `Collection::sum()` now uses bcadd and returns string
* Add ext-bcmath as dependency

## 2.1.3 - 2020/02/02

Initial fork for Dogecoin (based on v2.1.2 of https://github.com/denpamusic/php-bitcoinrpc)
