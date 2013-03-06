<?php

namespace Herrera\Box\Tests\Exception;

use Herrera\Box\Exception\OpenSslException;
use Herrera\PHPUnit\TestCase;

class OpenSslExceptionTest extends TestCase
{
    public function testLastError()
    {
        if (false === extension_loaded('openssl')) {
            $this->markTestSkipped(
                'The "openssl" extension is required to test the exception.'
            );
        }

        openssl_pkey_get_private('test', 'test');

        $exception = OpenSslException::lastError();

        $this->assertRegExp('/PEM routines/', $exception->getMessage());
    }
}