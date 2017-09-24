<?php

namespace Inowas\Common\SchemaValidator;


use Inowas\Common\Exception\JsonSchemaValidationFailedException;
use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;

class SchemaValidatorWrapper
{

    const SCHEMA_MODFLOW = 'file://spec/schema/modflow/modflowModel.json';

    /** @var  Dereferencer */
    private $dereferencer;

    public function __construct(Dereferencer $dereferencer)
    {
        $this->dereferencer = $dereferencer;
    }

    public function validate($json, string $schemaType): void
    {
        $schema = $this->dereferencer->dereference($schemaType);
        $validator = new Validator(json_decode($json), $schema);

        if (! $validator->passes()) {
            throw JsonSchemaValidationFailedException::withErrors($validator->errors());
        }
    }
}
