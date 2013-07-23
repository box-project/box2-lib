<?php

namespace Herrera\Box\Tests;

use Herrera\Box\Signature;
use Herrera\PHPUnit\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use Phar;

class SignatureTest extends TestCase
{
    private $types;

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

    // private methods

    public function testHandle()
    {
        $sig = new Signature(__FILE__);

        $this->setPropertyValue($sig, 'file', '/does/not/exist');

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            'No such file or directory'
        );

        $this->callMethod($sig, 'handle');
    }

    public function testRead()
    {
        $sig = new Signature(__FILE__);

        $this->setPropertyValue($sig, 'handle', true);

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            'boolean given'
        );

        $this->callMethod($sig, 'read', array(123));
    }

    public function testReadShort()
    {
        $file = $this->createFile();
        $sig = new Signature($file);

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            "Only read 0 of 1 bytes from \"$file\"."
        );

        $this->callMethod($sig, 'read', array(1));
    }

    public function testSeek()
    {
        $file = $this->createFile();
        $sig = new Signature($file);

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException'
        );

        $this->callMethod($sig, 'seek', array(-1));
    }

    protected function setUp()
    {
        $this->types = $this->getPropertyValue(
            'Herrera\\Box\\Signature',
            'types'
        );
    }

    protected function tearDown()
    {
        $this->setPropertyValue(
            'Herrera\\Box\\Signature',
            'types',
            $this->types
        );

        parent::tearDown();
    }
}
