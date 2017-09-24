<?php

declare(strict_types=1);

namespace Inowas\Common\Exception;

class KeyHasUseException extends \InvalidArgumentException
{
    public static function withMessage(string $message): KeyHasUseException
    {
        return new self($message);
    }
}
