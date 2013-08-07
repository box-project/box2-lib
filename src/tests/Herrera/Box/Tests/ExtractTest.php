<?php

namespace Herrera\Box\Tests;

use Herrera\Box\Extract;
use Herrera\PHPUnit\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use PHPUnit_Framework_Error_Warning;

class ExtractTest extends TestCase
{
    public function getStubLengths()
    {
        return array(
            array(RES_DIR . '/example.phar', 203, null),
            array(RES_DIR . '/mixed.phar', 6683, "__HALT_COMPILER(); ?>"),
        );
    }

    public function testConstruct()
    {
        $extract = new Extract(__FILE__, 123);

        $this->assertEquals(
            __FILE__,
            $this->getPropertyValue($extract, 'file')
        );

        $this->assertSame(
            123,
            $this->getPropertyValue($extract, 'stub')
        );
    }

    public function testConstructNotExist()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The path "/does/not/exist" is not a file or does not exist.'
        );

        new Extract('/does/not/exist', 123);
    }

    /**
     * @dataProvider getStubLengths
     */
    public function testFindStubLength($file, $length, $pattern)
    {
        if ($pattern) {
            $this->assertSame(
                $length,
                Extract::findStubLength($file, $pattern)
            );
        } else {
            $this->assertSame($length, Extract::findStubLength($file));
        }
    }

    public function testFindStubLengthInvalid()
    {
        $path = RES_DIR . '/example.phar';

        $this->setExpectedException(
            'InvalidArgumentException',
            'The pattern could not be found in "' . $path . '".'
        );

        Extract::findStubLength($path, 'bad pattern');
    }

    public function testFindStubLengthOpenError()
    {
        PHPUnit_Framework_Error_Warning::$enabled = false;

        $this->setExpectedException(
            'RuntimeException',
            'The phar "/does/not/exist" could not be opened for reading.'
        );

        $this->expectOutputRegex(
            '/No such file or directory/'
        );

        Extract::findStubLength('/does/not/exist');
    }

    public function testGo()
    {
        $extract = new Extract(RES_DIR . '/mixed.phar', 6683);

        $dir = $extract->go();

        $this->assertFileExists("$dir/test");

        $this->assertEquals(
            "<?php\n\necho \"This is a gzip compressed line.\n\";",
            file_get_contents("$dir/gzip/a.php")
        );

        $this->assertEquals(
            "<?php\n\necho \"This is a bzip2 compressed line.\n\";",
            file_get_contents("$dir/bzip2/b.php")
        );

        $this->assertEquals(
            "<?php\n\necho \"This is not a compressed line.\n\";",
            file_get_contents("$dir/none/c.php")
        );
    }

    public function testGoWithDir()
    {
        $extract = new Extract(RES_DIR . '/mixed.phar', 6683);
        $dir = $this->createDir();

        $extract->go($dir);

        $this->assertFileExists("$dir/test");

        $this->assertEquals(
            "<?php\n\necho \"This is a gzip compressed line.\n\";",
            file_get_contents("$dir/gzip/a.php")
        );

        $this->assertEquals(
            "<?php\n\necho \"This is a bzip2 compressed line.\n\";",
            file_get_contents("$dir/bzip2/b.php")
        );

        $this->assertEquals(
            "<?php\n\necho \"This is not a compressed line.\n\";",
            file_get_contents("$dir/none/c.php")
        );
    }

    public function testGoInvalidLength()
    {
        $path = RES_DIR . '/mixed.phar';

        $extract = new Extract($path, -123);

        $this->setExpectedException(
            'RuntimeException',
            'Could not seek to -123 in the file "' . $path . '".'
        );

        $extract->go();
    }

    /**
     * Issue #7
     *
     * Files with no content would trigger an exception when extracted.
     */
    public function testGoEmptyFile()
    {
        $path = RES_DIR . '/empty.phar';

        $extract = new Extract($path, Extract::findStubLength($path));

        $dir = $extract->go();

        $this->assertFileExists($dir . '/empty.php');

        $this->assertEquals('', file_get_contents($dir . '/empty.php'));
    }

    public function testPurge()
    {
        $dir = $this->createDir();

        mkdir("$dir/a/b/c", 0755, true);
        touch("$dir/a/b/c/d");

        Extract::purge($dir);

        $this->assertFileNotExists($dir);
    }

    public function testPurgeUnlinkError()
    {
        $root = vfsStream::newDirectory('test', 0444);
        $root->addChild(vfsStream::newFile('test', 0000));

        vfsStreamWrapper::setRoot($root);

        $this->setExpectedException(
            'RuntimeException',
            'The file "vfs://test/test" could not be deleted.'
        );

        Extract::purge('vfs://test/test');
    }

    protected function setUp()
    {
        $paths = array(
            sys_get_temp_dir() . '/pharextract/mixed'
        );

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $this->purgePath($path);
            }
        }
    }
}
