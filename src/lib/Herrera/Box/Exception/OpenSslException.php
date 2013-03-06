<?php

namespace Herrera\Box\Exception;

/**
 * Use for errors when using the OpenSSL extension.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class OpenSslException extends Exception
{
    /**
     * Creates an exception for the last OpenSSL error.
     *
     * @return OpenSslException The exception.
     */
    public static function lastError()
    {
        return new static(openssl_error_string());
    }
}