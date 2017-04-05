# php-scripts

This librairy provides utilities function to ease scripts manipulation

[![Build Status](https://travis-ci.org/hugsbrugs/php-scripts.svg?branch=master)](https://travis-ci.org/hugsbrugs/php-scripts)
[![Coverage Status](https://coveralls.io/repos/github/hugsbrugs/php-scripts/badge.svg?branch=master)](https://coveralls.io/github/hugsbrugs/php-scripts?branch=master)

## Install

Install package with composer
```
composer require hugsbrugs/php-scripts
```

In your PHP code, load library
```php
require_once __DIR__ . '/../vendor/autoload.php';
use Hug\Scripts\Scripts as Scripts;
```

## Usage

Run a script, output is saved to log file
```php
$cmd = 'ls -lsa';
$log_file = __DIR__ . '/test.log';
$res = Scripts::run($cmd, $log_file);
```
Outputs
```
[status] => success
[message] => 
[data] => Array
(
    [pid] => 3358
    [log] => /path/to/test.log
)
```
And file /path/to/test.log contains output of ls -lsa command


Checks if a script is running
```php
$running = Scripts::is_running($res['data']['pid']);
```

Get Memory and processor usage for a script
```php
$cpu_mem = Scripts::get_pid_cpu_mem($res['data']['pid']);
```
outputs
```
[mem] => 0.2
[cpu] => 0.1
```

## Unit Tests

```
composer exec phpunit
```

## Author

Hugo Maugey [visit my website ;)](https://hugo.maugey.fr)

