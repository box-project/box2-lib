<?php

namespace Herrera\Box\Compactor;

/**
 * Compacts JSON files by re-encoding without pretty print.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Json extends Compactor
{
    /**
     * {@inheritDoc}
     */
    public function compact($contents)
    {
        return json_encode(json_decode($contents));
    }
}