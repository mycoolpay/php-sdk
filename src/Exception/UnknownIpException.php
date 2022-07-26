<?php

namespace MyCoolPay\Exception;

use Exception;

class UnknownIpException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "UnknownIpException", $code = 0)
    {
        parent::__construct($message, $code);
    }
}
