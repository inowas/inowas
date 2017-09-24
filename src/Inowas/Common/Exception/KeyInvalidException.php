<?php

declare(strict_types=1);

namespace Inowas\Common\Exception;

class KeyInvalidException extends \InvalidArgumentException
{
    public static function withMessage(string $message): KeyInvalidException
    {
        return new self($message);
    }
}
