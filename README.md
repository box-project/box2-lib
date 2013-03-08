Box
===

[![Build Status](https://travis-ci.org/herrera-io/php-box.png?branch=master)](https://travis-ci.org/herrera-io/php-box)

A class for simplifying the PHAR build process.

Summary
-------

The Box class provides additional features for the Phar building process:

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
$box = Box::create('test.phar');
$box->buildFromDirectory('/path/to/dir');
$box->getPhar()->setStub($box->generateStub(null, '/path/to/dir/run.php'));
```

Running the Phar:

```sh
$ php test.phar
That run script (/path/to/dir/run.php).
```