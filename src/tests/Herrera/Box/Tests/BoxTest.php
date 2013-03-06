<?php

namespace Herrera\Box\Tests;

use Herrera\Box\Box;
use Herrera\PHPUnit\TestCase;
use Phar;

class BoxTest extends TestCase
{
    /**
     * @var Box
     */
    private $box;

    /**
     * @var string
     */
    private $cwd;

    /**
     * @var Phar
     */
    private $phar;

    public function testGetPhar()
    {
        $this->assertSame($this->phar, $this->box->getPhar());
    }

    protected function tearDown()
    {
        unset($this->box, $this->phar);

        parent::tearDown();
    }

    protected function setUp()
    {
        chdir($this->cwd = $this->createDir());

        $this->phar = new Phar('test.phar');
        $this->box = new Box($this->phar);
    }
}