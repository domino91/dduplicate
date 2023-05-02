<?php

namespace App\Exception;

use JetBrains\PhpStorm\Pure;
use RuntimeException;

class ScanFileException extends RuntimeException
{
    #[Pure] public function __construct(string $path, string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        $message .= ', path to file: ' . $path;
        parent::__construct($message, $code, $previous);
    }

}