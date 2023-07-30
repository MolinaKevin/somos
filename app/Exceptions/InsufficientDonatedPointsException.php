<?php

namespace App\Exceptions;

use Exception;

class InsufficientDonatedPointsException extends Exception
{
    public function __construct($message = "The Commerce does not have enough donated points to create this donation", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}

