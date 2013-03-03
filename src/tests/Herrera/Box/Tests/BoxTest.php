<?php

namespace Herrera\Box\Tests;

use Herrera\Box\Box;
use Herrera\Box\Compactor\CompactorInterface;
use Herrera\PHPUnit\TestCase;
use Phar;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;

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

    public function testAddCompactor()
    {
        $compactor = new Compactor();

        $this->box->addCompactor($compactor);

        $this->assertTrue(
            $this->getPropertyValue($this->box, 'compactors')
                 ->contains($compactor)
        );
    }

    /**
     * @depends testAddCompactor
     */
    public function testCompactContents()
    {
        $compactor = new Compactor();

        $this->box->addCompactor($compactor);

        $this->assertEquals(
            'my value',
            $this->box->compactContents('test.php', ' my value ')
        );
    }

    public function testGenerateStub()
    {
        $file = $this->createFile();

        file_put_contents($file, '<?php echo "Hello, world!\n";');

        $this->box->getPhar()->addFile($file, 'test/file.php');

        $this->assertEquals(
            <<<STUB
#!/usr/bin/env php
<?php

/**
 * Generated by Box.
 *
 * @link http://github.com/herrera-io/php-box/
 */
Phar::mapPhar('test.phar');
Phar::interceptFileFuncs();
require 'phar://' . __FILE__ . '/test/file.php';
__HALT_COMPILER();
STUB
            ,
            $stub = $this->box->generateStub('test.phar', 'test/file.php', true)
        );

        $this->box->getPhar()->setStub($stub);

        $this->assertEquals(
            'Hello, world!',
            exec('php test.phar')
        );
    }

    public function testGenerateStubNotExist()
    {
        $this->setExpectedException(
            'Herrera\\Box\\Exception\\InvalidArgumentException',
            'The file "does/not/exist" does not exist inside the Phar.'
        );

        $this->box->generateStub(null, 'does/not/exist');
    }

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

    public function testSetStubUsingFileNotExist()
    {
        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            'The file "/does/not/exist" does not exist or is not a file.'
        );

        $this->box->setStubUsingFile('/does/not/exist');
    }

    public function testSetStubUsingFileReadError()
    {
        vfsStreamWrapper::setRoot($root = vfsStream::newDirectory('test'));

        $root->addChild(vfsStream::newFile('test.php', 0000));

        $this->setExpectedException(
            'Herrera\\Box\\Exception\\FileException',
            'failed to open stream'
        );

        $this->box->setStubUsingFile('vfs://test/test.php');
    }

    public function testSetStubUsingFile()
    {
        $file = $this->createFile();

        file_put_contents($file, <<<STUB
#!/usr/bin/env php
<?php
echo "@replace_me@";
__HALT_COMPILER();
STUB
        );

        $this->box->setValues(array('@replace_me@' => 'replaced'));
        $this->box->setStubUsingFile($file, true);

        $this->assertEquals(
            'replaced',
            exec('php test.phar')
        );
    }

    public function testSetValues()
    {
        $rand = rand();

        $this->box->setValues(array('@rand@' => $rand));

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

class Compactor implements CompactorInterface
{
    public function compact($contents)
    {
        return trim($contents);
    }

    public function supports($file)
    {
        return ('php' === pathinfo($file, PATHINFO_EXTENSION));
    }
}