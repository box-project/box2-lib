<?php

namespace Herrera\Box\Compactor;

/**
 * A PHP source code compactor copied from Composer.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Composer implements CompactorInterface
{
    /**
     * The list of supported file extensions.
     *
     * @var array
     */
    private $extensions = array('php');

    /**
     * {@inheritDoc}
     */
    public function compact($contents)
    {
        $output = '';
        foreach (token_get_all($contents) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    /**
     * Sets the supported file extensions.
     *
     * @param array $extensions The extensions.
     */
    public function setExtensions(array $extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($file)
    {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), $this->extensions);
    }
}