<?php

declare(strict_types=1);

namespace Inowas\Common\Exception;

class InvalidTypeException extends \InvalidArgumentException
{
    public static function withMessage(string $message): InvalidTypeException
    {
        return new self($message);
    }
}
