# Simple Dogecoin JSON-RPC client based on https://github.com/denpamusic/php-bitcoinrpc


## Installation
Run ```php composer.phar require ftab/php-dogecoinrpc``` in your project directory or add following lines to composer.json
```javascript
"require": {
    "ftab/php-dogecoinrpc": "^2.1"
}
```
and run ```php composer.phar install```.

## Requirements
PHP 7.1 or higher  

## Usage
Create new object with url as parameter
```php
/**
 * Don't forget to include composer autoloader by uncommenting line below
 * if you're not already done it anywhere else in your project.
 **/
// require 'vendor/autoload.php';

use ftab\Dogecoin\Client as DogecoinClient;

$dogecoind = new DogecoinClient('http://rpcuser:rpcpassword@localhost:22555/');
```
or use array to define your dogecoind settings
```php
/**
 * Don't forget to include composer autoloader by uncommenting line below
 * if you're not already done it anywhere else in your project.
 **/
// require 'vendor/autoload.php';

use ftab\Dogecoin\Client as DogecoinClient;

$dogecoind = new DogecoinClient([
    'scheme'        => 'http',                 // optional, default http
    'host'          => 'localhost',            // optional, default localhost
    'port'          => 22555,                  // optional, default 22555
    'user'          => 'rpcuser',              // required
    'password'      => 'rpcpassword',          // required
    'ca'            => '/etc/ssl/ca-cert.pem',  // optional, for use with https scheme
    'preserve_case' => false,                  // optional, send method names as defined instead of lowercasing them
]);
```
Then call methods defined in [Dogecoin Core](https://github.com/dogecoin/dogecoin/) API with magic:
```php
/**
 * Get block info.
 */
$block = $dogecoind->getBlock('1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691');

$block('hash')->get();     // 1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // array of values
$block->keys();            // array of keys
$block->random(1, 'tx');   // random block txid
$block('tx')->random(2);   // two random block txid's
$block('tx')->first();     // txid of first transaction
$block('tx')->last();      // txid of last transaction

/**
 * Send transaction.
 */
$result = $dogecoind->sendToAddress('DATfurydmRTZ6vJnBtaibHJYMdx9JYjL4n', 100);
$txid = $result->get();

/**
 * Get transaction amount.
 */
$result = $dogecoind->listSinceBlock();
$dogecoin = $result->sum('transactions.*.amount');
$dogetoshi = \ftab\Dogecoin\to_dogetoshi($dogecoin);
```
To send asynchronous request, add Async to method name:
```php
$dogecoind->getBlockAsync(
    '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691',
    function ($response) {
        // success
    },
    function ($exception) {
        // error
    }
);
```

You can also send requests using request method:
```php
/**
 * Get block info.
 */
$block = $dogecoind->request('getBlock', '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691');

$block('hash');            // 1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691
$block['height'];          // 0 (array access)
$block->get('tx.0');       // 5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69
$block->count('tx');       // 1
$block->has('version');    // key must exist and CAN NOT be null
$block->exists('version'); // key must exist and CAN be null
$block->contains(0);       // check if response contains value
$block->values();          // get response values
$block->keys();            // get response keys
$block->first('tx');       // get txid of the first transaction
$block->last('tx');        // get txid of the last transaction
$block->random(1, 'tx');   // get random txid

/**
 * Send transaction.
 */
$result = $dogecoind->request('sendtoaddress', 'DATfurydmRTZ6vJnBtaibHJYMdx9JYjL4n', 60);
$txid = $result->get();

```
or requestAsync method for asynchronous calls:
```php
$dogecoind->requestAsync(
    'getBlock',
    '1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691',
    function ($response) {
        // success
    },
    function ($exception) {
        // error
    }
);
```

## Exceptions
* `ftab\Dogecoin\Exceptions\BadConfigurationException` - thrown on bad client configuration.
* `ftab\Dogecoin\Exceptions\BadRemoteCallException` - thrown on getting error message from daemon.
* `ftab\Dogecoin\Exceptions\ConnectionException` - thrown on daemon connection errors (e. g. timeouts)


## Helpers
Package provides following helpers to assist with value handling.
#### `to_dogecoin()`
Converts value in dogetoshi to dogecoin.
```php
echo ftab\Dogecoin\to_dogecoin(100000); // 0.00100000
```
#### `to_dogetoshi()`
Converts value in dogecoin to dogetoshi.
```php
echo ftab\Dogecoin\to_dogetoshi(0.001); // 100000
```
#### `to_fixed()`
Trims float value to precision without rounding.
```php
echo ftab\Dogecoin\to_fixed(0.1236, 3); // 0.123
```

## License

This product is distributed under MIT license.

## Donations

### Let's give some love to Denpa

If you like this project, please consider donating to the original author of php-bitcoinrpc as they did all the work:<br>
**BTC**: 3L6dqSBNgdpZan78KJtzoXEk9DN3sgEQJu<br>
**Bech32**: bc1qyj8v6l70c4mjgq7hujywlg6le09kx09nq8d350

❤Thanks for your support!❤

### dogecoin fork

I guess I could take a doge or two for making this fork too? Dunno. Feels weird when all I'm doing is diffing and editing.

* **DOGE**: DATfurydmRTZ6vJnBtaibHJYMdx9JYjL4n

Or check out the game I'm putting it in, and throw your doge at some of the in-game goodies!
* [Ruins of Chaos](https://ruinsofchaos.com/)