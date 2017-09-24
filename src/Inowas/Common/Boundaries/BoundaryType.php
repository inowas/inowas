<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Exception\InvalidTypeException;

final class BoundaryType
{
    const CONSTANT_HEAD = 'chd';
    const GENERAL_HEAD = 'ghb';
    const RECHARGE = 'rch';
    const RIVER = 'riv';
    const WELL = 'wel';

    private $available = [
        self::CONSTANT_HEAD,
        self::GENERAL_HEAD,
        self::RECHARGE,
        self::RIVER,
        self::WELL
    ];

    /** @var  string */
    private $type;

    public static function fromString(string $type): BoundaryType
    {
        return new self($type);
    }

    private function __construct(string $type)
    {
        if (! in_array($type, $this->available, true)){
            throw InvalidTypeException::withMessage(sprintf('BoundaryType %s is a not known. Available types are: %s', $type, implode(', ', $this->available)));
        }

        $this->type = $type;
    }

    public function toString(): string
    {
        return $this->type;
    }

    public function sameAs($type): bool
    {
        if ($type instanceof BoundaryType){
            return $this->toString() === $type->toString();
        }

        return false;
    }
}
