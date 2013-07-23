<?php

namespace Herrera\Box\Tests;

use Herrera\Box\Signature;
use Herrera\PHPUnit\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use Phar;

class SignatureTest extends TestCase
{
    public function getPhars()
    {
        return array(
            array(RES_DIR . '/md5.phar'),
            array(RES_DIR . '/sha1.phar'),
            array(RES_DIR . '/sha256.phar'),
            array(RES_DIR . '/sha512.phar'),
            array(RES_DIR . '/openssl.phar'),
        );
    }

    public function testConstruct()
    {
        $path = RES_DIR . '/example.phar';

        $sig = new Signature($path);

        $this->assertEquals(
            realpath($path),
            $this->getPropertyValue($sig, 'file')
        );

        $this->assertSame(
            filesize($path),
            $this->getPropertyValue($sig, 'size')
        );
    }

    public function testConstructNotExist()
    {
        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            'The path "/does/not/exist" does not exist or is not a file.'
        );

        new Signature('/does/not/exist');
    }

    public function testCreate()
    {
        $this->assertInstanceOf(
            'Herrera\\Box\\Signature',
            Signature::create(RES_DIR . '/example.phar')
        );
    }

    public function testCreateNoGbmb()
    {
        $path = realpath(RES_DIR . '/missing.phar');
        $sig = new Signature($path);

        $this->setExpectedException(
            'PharException',
            "The phar \"$path\" is not signed."
        );

        $sig->get();
    }

    public function testCreateInvalid()
    {
        $path = realpath(RES_DIR . '/invalid.phar');
        $sig = new Signature($path);

        $this->setExpectedException(
            'PharException',
            "The signature type (ffffffff) is not recognized for the phar \"$path\"."
        );

        $sig->get(true);
    }

    public function testCreateMissingNoRequire()
    {
        $path = realpath(RES_DIR . '/missing.phar');
        $sig = new Signature($path);

        $this->assertNull($sig->get(false));
    }

    /**
     * @dataProvider getPhars
     */
    public function testGet($path)
    {
        $phar = new Phar($path);
        $sig = new Signature($path);

        $this->assertEquals(
            $phar->getSignature(),
            $sig->get()
        );
    }

    /**
     * @dataProvider getPhars
     */
    public function testVerify($path)
    {
        $sig = new Signature($path);

        $this->assertTrue($sig->verify());
    }

    public function testVerifyMissingKey()
    {
        $dir = $this->createDir();

        copy(RES_DIR . '/openssl.phar', "$dir/openssl.phar");

        $sig = new Signature("$dir/openssl.phar");

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            'No such file or directory'
        );

        $sig->verify();
    }

    public function testVerifyErrorHandlingBug()
    {
        $dir = $this->createDir();

        copy(RES_DIR . '/openssl.phar', "$dir/openssl.phar");
        touch("$dir/openssl.phar.pubkey");

        $sig = new Signature("$dir/openssl.phar");

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\OpenSslException',
            'cannot be coerced'
        );

        $sig->verify();
    }
}
