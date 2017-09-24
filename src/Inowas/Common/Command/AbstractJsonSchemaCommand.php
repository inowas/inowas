<?php

declare(strict_types=1);

namespace Inowas\Common\Command;

use Inowas\Common\Exception\JsonSchemaValidationFailedException;
use Inowas\Common\SchemaValidator\UrlReplaceLoader;
use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\MessageDataAssertion;

abstract class AbstractJsonSchemaCommand extends Command
{
    /**
     * @var object
     */
    protected $dereferencedSchema;

    /**
     * @var array
     */
    protected $payload;

    /**
     * Returns the json schema for this command or an empty string if command has no payload
     *
     * @return string
     */
    abstract public function schema(): string;

    final public function __construct(array $payload = null)
    {
        $this->setPayload($payload);
        $this->init();
    }

    /**
     * @return array
     */
    final public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     * @throws \League\JsonGuard\Exception\InvalidSchemaException
     * @throws \League\JsonGuard\Exception\MaximumDepthExceededException
     * @throws \Inowas\Common\Exception\JsonSchemaValidationFailedException
     */
    final protected function setPayload(array $payload = null): void
    {
        if ($this->schema() === '') {
            if (null !== $payload) {
                throw new \InvalidArgumentException(__CLASS__ . ' has no json schema defined, but is constructed with payload.');
            }
            $this->payload = [];
            return;
        }

        MessageDataAssertion::assertPayload($payload);

        $this->payload = $payload;

        $payload = json_decode(json_encode($payload), FALSE);
        $validator = new Validator($payload, $this->dereferencedSchema());
        if (! $validator->passes()) {
            throw JsonSchemaValidationFailedException::withErrors($validator->errors());
        }
    }

    protected function dereferencedSchema()
    {
        if (null === $this->dereferencedSchema) {
            $dereferencer = Dereferencer::draft4();
            $dereferencer->getLoaderManager()->registerLoader('https', new UrlReplaceLoader());
            $this->dereferencedSchema = $dereferencer->dereference($this->schema());
        }
        return $this->dereferencedSchema;
    }
}
