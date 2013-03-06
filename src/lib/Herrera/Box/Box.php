<?php

namespace Herrera\Box;

use Phar;

/**
 * Provides additional, complimentary functionality to the Phar class.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Box
{
    /**
     * The Phar instance.
     *
     * @var Phar
     */
    private $phar;

    /**
     * Sets the Phar instance.
     *
     * @param Phar $phar The instance.
     */
    public function __construct(Phar $phar)
    {
        $this->phar = $phar;
    }

    /**
     * Returns the Phar instance.
     *
     * @return Phar The instance.
     */
    public function getPhar()
    {
        return $this->phar;
    }
}