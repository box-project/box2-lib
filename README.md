Box
===

[![Build Status](https://travis-ci.org/herrera-io/php-box.png?branch=master)](https://travis-ci.org/herrera-io/php-box)

A class for simplifying the PHAR build process.

Summary
-------

The Box library provides additional features for the Phar building process:

- compact file contents according to type
- search and replace placeholder values

as well as improving others:

- generating stubs
- set stubs using files
- signing using private keys (and private key files)

Installation
------------

Add it to your list of Composer dependencies:

```sh
$ composer require herrera-io/box=1.*
```

Usage
-----

Building the Phar:

```php
<?php

use Herrera\Box\Box;
use Herrera\Box\StubGenerator;

$box = Box::create('test.phar');
$box->buildFromDirectory('/path/to/dir');
$box->getPhar()->setStub(
    StubGenerator::create()
        ->index('path/to/script.php')
        ->generate()
);
```

Running the Phar:

```sh
$ php test.phar
That index script (path/to/script.php).
```