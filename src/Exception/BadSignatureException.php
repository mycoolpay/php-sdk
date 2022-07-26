<?php

namespace MyCoolPay\Exception;

use Exception;

class BadSignatureException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "BadSignatureException", $code = 0)
    {
        parent::__construct($message, $code);
    }
}
