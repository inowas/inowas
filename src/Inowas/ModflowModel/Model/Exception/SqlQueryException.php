<?php

namespace Inowas\ModflowModel\Model\Exception;

final class SqlQueryException extends \InvalidArgumentException
{
    public static function withClassName(string $className, string $functionName)
    {
        return new self(sprintf('A query went wrong in %s:%s.', $className, $functionName));
    }
}
