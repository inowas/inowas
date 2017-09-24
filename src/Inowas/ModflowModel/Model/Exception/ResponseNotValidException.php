<?php

namespace Inowas\ModflowModel\Model\Exception;

final class ResponseNotValidException extends \InvalidArgumentException
{
    public static function withResponse(string $response): ResponseNotValidException
    {
        return new self(sprintf('The response "%s" could not be converted.', $response));
    }
}
