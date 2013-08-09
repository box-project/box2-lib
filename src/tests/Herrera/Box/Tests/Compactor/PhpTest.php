<?php

namespace Herrera\Box\Tests\Compactor;

use Herrera\Annotations\Tokenizer;
use Herrera\Box\Compactor\Php;
use Herrera\PHPUnit\TestCase;

class PhpTest extends TestCase
{
    /**
     * @var Php
     */
    private $php;

    public function testCompact()
    {
        $original = <<<ORIGINAL
<?php

/**
 * A comment.
 */
class AClass
{
    /**
     * A comment.
     */
    public function aMethod()
    {
        \$test = true;# a comment
    }
}
ORIGINAL;

        $expected = <<<EXPECTED
<?php




class AClass
{



public function aMethod()
{
\$test = true;
 }
}
EXPECTED;

        $this->assertEquals($expected, $this->php->compact($original));
    }

    public function testConvertWithAnnotations()
    {
        $tokenizer = new Tokenizer();
        $tokenizer->ignore(array('ignored'));

        $this->php->setTokenizer($tokenizer);

        $original = <<<ORIGINAL
<?php

/**
 * This is an example entity class.
 *
 * @Entity()
 * @Table(name="test")
 */
class Test
{
    /**
     * The unique identifier.
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     */
    private \$id;

    /**
     * A foreign key.
     *
     * @ORM\ManyToMany(targetEntity="SomethingElse")
     * @ORM\JoinTable(
     *     name="aJoinTable",
     *     joinColumns={
     *         @ORM\JoinColumn(name="joined",referencedColumnName="foreign")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="foreign",referencedColumnName="joined")
     *     }
     * )
     */
    private \$foreign;

    /**
     * @ignored
     */
    private \$none;
}
ORIGINAL;


        $expected = <<<EXPECTED
<?php

/**
@Entity()
@Table(name="test")


*/
class Test
{
/**
@ORM\Column(type="integer")
@ORM\GeneratedValue()
@ORM\Id()


*/
private \$id;

/**
@ORM\ManyToMany(targetEntity="SomethingElse")
@ORM\JoinTable(name="aJoinTable",joinColumns={@ORM\JoinColumn(name="joined",referencedColumnName="foreign")},inverseJoinColumns={@ORM\JoinColumn(name="foreign",referencedColumnName="joined")})










*/
private \$foreign;




private \$none;
}
EXPECTED;


        $this->assertEquals($expected, $this->php->compact($original));
    }

    public function testSetTokenizer()
    {
        $tokenizer = new Tokenizer();

        $this->php->setTokenizer($tokenizer);

        $this->assertInstanceOf(
            'Herrera\\Annotations\\Convert\\ToString',
            $this->getPropertyValue($this->php, 'converter')
        );

        $this->assertSame(
            $tokenizer,
            $this->getPropertyValue($this->php, 'tokenizer')
        );
    }

    public function testSupports()
    {
        $this->assertTrue($this->php->supports('test.php'));
    }

    protected function setUp()
    {
        $this->php = new Php();
    }
}
