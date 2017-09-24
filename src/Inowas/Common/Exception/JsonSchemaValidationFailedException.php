<?php

declare(strict_types=1);

namespace Inowas\Common\Exception;

class JsonSchemaValidationFailedException extends \InvalidArgumentException
{
    /** @var  array */
    private $errors = [];

    public static function withErrors(array $errors): JsonSchemaValidationFailedException
    {
        $self = new self();
        $self->errors = $errors;
        return $self;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
