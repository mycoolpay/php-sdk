<?php

namespace MyCoolPay\Exception;

use Exception;

class KeyMismatchException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "KeyMismatchException", $code = 0)
    {
        parent::__construct($message, $code);
    }
}
