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

    public function testReplaceValues()
    {
        $this->setPropertyValue($this->box, 'values', array(
            '@1@' => 'a',
            '@2@' => 'b'
        ));

        $this->assertEquals('ab@3@', $this->box->replaceValues('@1@@2@@3@'));
    }

    public function testSetValues()
    {
        $rand = rand();

        $this->box->setValues(array('rand' => $rand));

        $this->assertEquals(
            array('@rand@' => $rand),
            $this->getPropertyValue($this->box, 'values')
        );
    }

    public function testSetValuesNonScalar()
    {
        $this->setExpectedException(
            'Herrera\\Box\\Exception\\InvalidArgumentException',
            'Non-scalar values (such as resource) are not supported.'
        );

        $this->box->setValues(array('stream' => STDOUT));
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