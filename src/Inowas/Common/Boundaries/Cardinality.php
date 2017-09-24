<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Exception\InvalidTypeException;

final class Cardinality
{
    /** @var  string */
    private $cardinality;

    private $available = ['1', 'n'];

    public static function fromString(string $type): Cardinality
    {
        return new self($type);
    }

    private function __construct(string $type)
    {
        if (! in_array($type, $this->available, true)){
            throw InvalidTypeException::withMessage(sprintf('Cardinality %s is a not known. Available types are: %s', $type, implode(', ', $this->available)));
        }

        $this->cardinality = $type;
    }

    public function toString(): string
    {
        return $this->cardinality;
    }

    public function sameAs($type): bool
    {
        if ($type instanceof BoundaryType){
            return $this->toString() === $type->toString();
        }

        return false;
    }
}
